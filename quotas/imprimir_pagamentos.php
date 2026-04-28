<?php
require_once '../config.php';
require_once '../vendor/fpdf/fpdf.php';
require_once '../includes/pdf_helpers.php';

// Filtros e ordenação
$ordem = $_GET['ordem'] ?? 'data_desc';
$ordem_sql = 'p.data_pagamento DESC';
if ($ordem === 'data_asc') {
    $ordem_sql = 'p.data_pagamento ASC';
} elseif ($ordem === 'ano_desc') {
    $ordem_sql = 'p.referente_ano DESC, p.data_pagamento DESC';
}
$anoSelecionado = $_GET['ano'] ?? date('Y');
$filtro_socio = trim($_GET['filtro_socio'] ?? '');
$filtro_metodo = trim($_GET['filtro_metodo'] ?? '');
$where = [];
$params = [];
if ($filtro_metodo !== '') {
    $where[] = 'p.metodo_pagamento = ?';
    $params[] = $filtro_metodo;
}
if ($anoSelecionado !== 'todos') {
    $where[] = 'p.referente_ano = ?';
    $params[] = $anoSelecionado;
}
if ($filtro_socio !== '') {
    $where[] = '(a.numero_socio LIKE ? OR a.nome_completo LIKE ?)';
    $params[] = "%$filtro_socio%";
    $params[] = "%$filtro_socio%";
}
$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$sql = "SELECT p.*, a.nome_completo, a.numero_socio, q.nome AS nome_quota FROM pagamentos p
    JOIN socios a ON p.associado_id = a.id
    JOIN tipos_quotas q ON p.quota_id = q.id
    $whereSQL
    ORDER BY $ordem_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar dados da associação
$assoc_stmt = $pdo->query("SELECT nome, logotipo FROM associacoes LIMIT 1");
$associacao = $assoc_stmt->fetch(PDO::FETCH_ASSOC);
$logo_path = !empty($associacao['logotipo']) ? $_SERVER['DOCUMENT_ROOT'] . '/gestao-socios/' . $associacao['logotipo'] : '';

class PagamentosPDF extends FPDF {
    public $assocName = '';
    function Footer() {
        $this->SetY(-16);
        $this->SetFont('Arial','',8);
        $page = $this->PageNo();
        $total = '{nb}';
        $dataAtual = date('d/m/Y');
        $leftText = pdf_text($this->assocName . ' - Listagem de Pagamentos (' . $page . '/' . $total . ')');
        $rightText = pdf_text($dataAtual);
        $this->Cell(0, 6, $leftText, 0, 0, 'L');
        $this->SetXY(-50, $this->GetY());
        $this->Cell(40, 6, $rightText, 0, 0, 'R');
        $this->SetY(-10);
        $this->SetFont('Arial','',6);
        $appVersion = VERSION;
        $appName = APP_NAME;
        $this->Cell(0, 6, pdf_text('Documento gerado automaticamente por sistema informático ' . $appName . '-'.$appVersion), 0, 0, 'C');
    }
}
$pdf = new PagamentosPDF('L', 'mm', 'A4');
$pdf->assocName = $associacao['nome'] ?? 'Associação';
$pdf->AliasNbPages();
$pdf->AddPage();
if (!empty($logo_path) && file_exists($logo_path)) {
    $pdf->Image($logo_path, 10, 8, 28, 0, '', '');
    $pdf->SetXY(40, 10);
} else {
    $pdf->SetXY(10, 10);
}
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 10, pdf_text($associacao['nome'] ?? 'Associação'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(0, 10, pdf_text('Listagem de Pagamentos'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Ln(8);

// Cabeçalho
$header = ['Data', 'Valor', 'Método', 'Meses', 'Ref. Ano', 'Sócio Nº', 'Nome'];
$totalWidth = 277;
// Proporções: Data (15%), Valor (15%), Método (10%), Meses (10%), Ano (10%), Nº Sócio (10%), Nome (20%)
$widths = [round($totalWidth*0.15), round($totalWidth*0.15), round($totalWidth*0.10), round($totalWidth*0.10), round($totalWidth*0.10), round($totalWidth*0.10), round($totalWidth*0.20)];
$pdf->SetFillColor(0,0,0);
$pdf->SetTextColor(255,255,255);
foreach ($header as $i => $col) {
    $pdf->Cell($widths[$i], 8, pdf_text($col), 1, 0, 'C', true);
}
$pdf->SetTextColor(0,0,0);
$pdf->Ln();

// Dados
foreach ($pagamentos as $p) {
    $pdf->Cell($widths[0], 8, !empty($p['data_pagamento']) ? pdf_text(date('d/m/Y', strtotime($p['data_pagamento']))) : pdf_text('-'), 1, 0, 'C');
    $pdf->Cell($widths[1], 8, isset($p['valor']) ? pdf_text(number_format((float)$p['valor'], 2, ',', '.') . ' €') : pdf_text('-'), 1, 0, 'C');
    $pdf->Cell($widths[2], 8, pdf_text($p['metodo_pagamento'] ?? '-'), 1, 0, 'C');
    $pdf->Cell($widths[3], 8, pdf_text($p['meses_pagos'] ?? '-'), 1, 0, 'C');
    $pdf->Cell($widths[4], 8, pdf_text($p['referente_ano'] ?? '-'), 1, 0, 'C');
    $pdf->Cell($widths[5], 8, pdf_text($p['numero_socio'] ?? '-'), 1, 0, 'C');
    $nomeReduzido = isset($p['nome_completo']) ? primeiroUltimoNome($p['nome_completo']) : '-';
    $pdf->Cell($widths[6], 8, pdf_text($nomeReduzido), 1, 0, 'C');
    $pdf->Ln();
}
$date_str = date('Ymd');
$pdf->Output('I', "listagem_pagamentos_$date_str.pdf");
exit;

function primeiroUltimoNome($nomeCompleto) {
    $partes = preg_split('/\s+/', trim($nomeCompleto));
    if (count($partes) === 0) return '';
    if (count($partes) === 1) return $partes[0];
    return $partes[0] . ' ' . $partes[count($partes)-1];
}
