<?php
require_once '../config.php';
require_once '../vendor/fpdf/fpdf.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('ID de sócio inválido.');
}

// Buscar dados do sócio
$stmt = $pdo->prepare('SELECT a.*, q.nome AS nome_quota, q.valor AS valor_quota, a.associacao_id FROM socios a LEFT JOIN tipos_quotas q ON a.quota_id = q.id WHERE a.id = :id');
$stmt->execute(['id' => $id]);
$socio = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$socio) {
    // Garantir que não haja saída de texto antes do PDF
    ob_clean();
    die('Sócio não encontrado.');
}

// Buscar dados da associação principal (id=1)
$stmtAssoc = $pdo->prepare('SELECT nome, morada, contacto, logotipo FROM associacoes WHERE id = 1');
$stmtAssoc->execute();
$associacao = $stmtAssoc->fetch(PDO::FETCH_ASSOC);

$pdf = new FPDF();
$pdf->AddPage();
// Logotipo
$logoWidth = 30; // largura do logo
$logoHeight = 20; // altura do logo (ajuste conforme necessário)
$logoY = 10;
// Garantir que o caminho do logotipo seja absoluto (evitar passar null para ltrim)
$logo_rel = isset($associacao['logotipo']) && $associacao['logotipo'] ? ltrim((string)$associacao['logotipo'], '/') : '';
$logotipoPath = $logo_rel ? (__DIR__ . '/../' . $logo_rel) : '';
if ($logo_rel && file_exists($logotipoPath)) {
    $pdf->Image($logotipoPath, 10, $logoY, $logoWidth);
}
// Avançar X para depois do logo
$pdf->SetXY(10 + $logoWidth + 5, $logoY); // 5px de espaço após o logo
$pdf->SetFont('Helvetica','B',16);
$pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$associacao['nome']),0,1,'L');
$pdf->SetFont('Helvetica','',14);
$pdf->SetX(10 + $logoWidth + 5);
$pdf->Cell(0,6,iconv('UTF-8','ISO-8859-1//TRANSLIT',$associacao['morada'].' | Tel.: '.$associacao['contacto']),0,1,'L');
$pdf->Ln(25);
$pdf->SetFont('Helvetica','B',16);
$pdf->Cell(0,10,iconv('UTF-8','ISO-8859-1//TRANSLIT','Detalhes do Sócio'),0,1,'L');
$pdf->SetFont('Helvetica','',12);
$pdf->Ln(5);

// Reordenar dados do sócio conforme layout solicitado
$pdf->SetFont('Arial', '', 11);

// Linha 1: Nome | Número de Sócio | Data de Nascimento

$left = $pdf->GetX();
$fullW = $pdf->GetPageWidth() - 2 * $left;
$wName = $fullW * 0.45;
$wNum = $fullW * 0.2;
$wDob = $fullW - $wName - $wNum;
$pdf->Cell($wName, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nome: ' . ($socio['nome_completo'] ?? '')), 0, 0);
$pdf->Cell($wNum, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Sócio Nº.: ' . ($socio['numero_socio'] ?? '')), 0, 0);
$pdf->Cell($wDob, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Data Nascimento: ' . (!empty($socio['data_nascimento']) ? date('d/m/Y', strtotime($socio['data_nascimento'])) : '')), 0, 1);

// Linha 2: Morada (full width)
$pdf->MultiCell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Morada: ' . ($socio['morada'] ?? '')));

// Linha 3: Telefone | Telemóvel | Email
$wcol = $fullW / 3;
$pdf->Cell($wcol, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Telefone: ' . ($socio['telefone'] ?? '')), 0, 0);
$pdf->Cell($wcol, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Telemóvel: ' . ($socio['telemovel'] ?? '')), 0, 0);
$pdf->Cell($wcol, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Email: ' . ($socio['email'] ?? '')), 0, 1);

$pdf->Ln(6);

// Diabético (campo personalizado) — só se estiver activo no sistema
if (isset($usar_diabetico) && $usar_diabetico) {
    $diab_val = (!empty($socio['diabetico']) && intval($socio['diabetico'])) ? 'Sim' : 'Não';
    $wDiab = $fullW * 0.25;
    $wReg = $fullW - $wDiab;
    $pdf->Cell($wDiab,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Diabético: ' . $diab_val),0,0);
    $pdf->Cell($wReg,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Data de Admissão: ' . (!empty($socio['data_registo']) ? date('d/m/Y', strtotime($socio['data_registo'])) : '')),0,1);
    $pdf->Ln(2);
}

// Quota e Valor Quota
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Quota:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$socio['nome_quota']),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Valor Quota:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',number_format((float)$socio['valor_quota'], 2, ',', '.')) . ' ' . iconv('UTF-8','ISO-8859-1//TRANSLIT','€'),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Anotações:'),0,0); $pdf->MultiCell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$socio['anotacoes']));

// Pagamentos
$stmtPag = $pdo->prepare('SELECT p.*, q.nome AS nome_quota, p.referente_ano FROM pagamentos p JOIN tipos_quotas q ON p.quota_id = q.id WHERE p.associado_id = ? ORDER BY p.data_pagamento DESC');
$stmtPag->execute([$id]);
$pagamentos = $stmtPag->fetchAll(PDO::FETCH_ASSOC);

$pdf->Ln(10);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Pagamentos'),0,1);
$pdf->SetFont('Helvetica','',11);
if ($pagamentos) {
    foreach ($pagamentos as $pag) {
        $linha = sprintf(
            'Data: %s | Valor: %s € | Método: %s | Ano: %s',
            $pag['data_pagamento'],
            number_format($pag['montante'], 2, ',', '.'),
            $pag['metodo_pagamento'],
            $pag['referente_ano']
        );
        $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$linha),0,1);
    }
} else {
    $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Sem pagamentos registados.'),0,1);
}
$pdf->Ln(0);
// Garantir que não haja output já enviado (evita erro do FPDF)
if (ob_get_length() !== false && ob_get_length() > 0) {
    @ob_end_clean();
}
$pdf->Output('I', 'associado_' . $socio['numero_socio'] . '.pdf');
?>
