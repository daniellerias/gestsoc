<?php
require_once '../config.php';
require_once '../vendor/fpdf/fpdf.php';
require_once '../includes/pdf_helpers.php';

// Buscar todos os sócios (sem paginação, com filtro)
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'numero_socio';
$ordem_sql = 'a.data_registo DESC';
if ($ordem === 'numero_socio') {
    $ordem_sql = 'CAST(a.numero_socio AS UNSIGNED) ASC';
} elseif ($ordem === 'numero_socio_desc') {
    $ordem_sql = 'CAST(a.numero_socio AS UNSIGNED) DESC';
} elseif ($ordem === 'nome') {
    $ordem_sql = 'a.nome_completo ASC';
}

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
if ((empty($usar_diabetico) || !$usar_diabetico) && in_array($filtro, ['diabeticos','nao_diabeticos'])) {
    $filtro = 'todos';
}
$where_clauses = [];
if ($filtro === 'diabeticos') {
    $where_clauses[] = 'a.diabetico = 1';
} elseif ($filtro === 'nao_diabeticos') {
    $where_clauses[] = 'a.diabetico = 0';
} elseif ($filtro === 'inscricao_ativa') {
    $where_clauses[] = "a.estado = 'Ativa'";
} elseif ($filtro === 'inscricao_suspensa') {
    $where_clauses[] = "a.estado = 'Suspensa'";
}
$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}
$sql = "SELECT a.*, q.valor AS valor_quota FROM socios a LEFT JOIN tipos_quotas q ON a.quota_id = q.id $where_sql ORDER BY $ordem_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar dados da associação
$assoc_stmt = $pdo->query("SELECT nome, logotipo FROM associacoes LIMIT 1");
$associacao = $assoc_stmt->fetch(PDO::FETCH_ASSOC);
$logo_path = !empty($associacao['logotipo']) ? WEB_ADDRESS . IMG_DIR . $associacao['logotipo'] : '';

class SociosPDF extends FPDF {
    public $assocName = '';
    function Footer() {
        $this->SetY(-16);
        $this->SetFont('Arial','',8);
        $page = $this->PageNo();
        $total = '{nb}';
        $dataAtual = date('d/m/Y');
        $leftText = pdf_text($this->assocName . ' - Listagem de Sócios (' . $page . '/' . $total . ')');
        $rightText = pdf_text($dataAtual);
        $this->Cell(0, 6, $leftText, 0, 0, 'L');
        $this->SetXY(-50, $this->GetY());
        $this->Cell(40, 6, $rightText, 0, 0, 'R');
        $this->SetY(-10);
        $this->SetFont('Arial','',6);
        $appVersion = VERSION;
        $appName = APP_NAME;
        $this->Cell(0, 6, pdf_text('Documento gerado automaticamente por sistema informático ' . $appName . '-'.$appVersion.''), 0, 0, 'C');
    }
}
$pdf = new SociosPDF('L', 'mm', 'A4');
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
$pdf->Cell(0, 10, pdf_text('Listagem de Sócios'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Ln(8); // Espaço extra entre título e tabela

// Cabeçalho
$header = ['Nome', 'Número Sócio'];
if (!empty($usar_diabetico)) $header[] = 'Diabético';
$header = array_merge($header, ['Quota', 'Data de Admissão', 'Inscrição']);
// Ajustar larguras para ocupar toda a página (A4 landscape: 297mm, menos margens)
$totalWidth = 277; // margem de 10mm de cada lado
$numCols = count($header);
// Proporções: Nome (30%), Número (12%), Diabético (10%), Quota (18%), Data (15%), Inscrição (15%)
$widths = [];
if (!empty($usar_diabetico)) {
    $widths = [round($totalWidth*0.30), round($totalWidth*0.12), round($totalWidth*0.10), round($totalWidth*0.18), round($totalWidth*0.15), round($totalWidth*0.15)];
} else {
    $widths = [round($totalWidth*0.32), round($totalWidth*0.14), round($totalWidth*0.20), round($totalWidth*0.17), round($totalWidth*0.17)];
}
$pdf->SetFillColor(0,0,0); // Fundo preto
$pdf->SetTextColor(255,255,255); // Texto branco
foreach ($header as $i => $col) {
    $pdf->Cell($widths[$i], 8, pdf_text($col), 1, 0, 'C', true);
}
$pdf->SetTextColor(0,0,0); // Voltar texto para preto
$pdf->Ln();

// Dados
foreach ($socios as $assoc) {
    $pdf->Cell($widths[0], 8, pdf_text($assoc['nome_completo'] ?? '-'), 1);
    $pdf->Cell($widths[1], 8, pdf_text($assoc['numero_socio'] ?? '-'), 1);
    $col_idx = 2;
    if (!empty($usar_diabetico)) {
        $pdf->Cell($widths[$col_idx], 8, intval($assoc['diabetico']) ? pdf_text('Sim') : pdf_text('Não'), 1, 0, 'C');
        $col_idx++;
    }
    $pdf->Cell($widths[$col_idx], 8, pdf_text($assoc['valor_quota'] ?? '-') . chr(128), 1, 0, 'C');
    $pdf->Cell($widths[$col_idx+1], 8, !empty($assoc['data_registo']) ? pdf_text(date('d/m/Y', strtotime($assoc['data_registo']))) : pdf_text('-'), 1, 0, 'C');
    $estado = $assoc['estado'] ?? '-';
    $pdf->Cell($widths[$col_idx+2], 8, pdf_text($estado), 1, 0, 'C');
    $pdf->Ln();
}
$date_str = date('Ymd');
$pdf->Output('I', "listagem_socios_$date_str.pdf");
exit;
