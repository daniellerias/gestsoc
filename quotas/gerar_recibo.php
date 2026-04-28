<?php
require_once '../config.php';
require_once '../vendor/fpdf/fpdf.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die('Pagamento não especificado.');
}
// Buscar dados do pagamento, associado, quota
$stmt = $pdo->prepare('SELECT p.*, a.numero_socio, a.nome_completo, q.nome AS nome_quota, q.valor, a.associacao_id FROM pagamentos p
    JOIN socios a ON p.associado_id = a.id
    JOIN tipos_quotas q ON p.quota_id = q.id
    WHERE p.id = ?');
$stmt->execute([$id]);
$pag = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pag) {
    die('Pagamento não encontrado.');
}
// Buscar nome e logotipo da associação principal (id=1)
$nome_associacao = 'Associação';
$logotipo = '';
$stmtAssoc = $pdo->prepare('SELECT nome, logotipo FROM associacoes WHERE id = 1');
$stmtAssoc->execute();
$assoc = $stmtAssoc->fetch(PDO::FETCH_ASSOC);
if ($assoc) {
    $nome_associacao = $assoc['nome'];
    $logotipo = $assoc['logotipo'];
}

$pdf = new FPDF();
$pdf->AddPage();
// Logotipo
if (!empty($logotipo) && file_exists($logotipo)) {
    $pdf->Image($logotipo,10,10,30);
    $pdf->SetXY(45,10);
}
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,iconv('UTF-8','ISO-8859-1//TRANSLIT', $nome_associacao),0,1);
$pdf->Ln(10);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,iconv('UTF-8','ISO-8859-1//TRANSLIT','Recibo de Pagamento de Quota'),0,1,'C');
$pdf->Ln(5);
$pdf->SetFont('Arial','',11);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Nº Sócio:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$pag['numero_socio']),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Nome:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$pag['nome_completo']),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Tipo de Quota:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$pag['nome_quota']),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Valor:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT', number_format($pag['valor'],2,',','.') . ' €'),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Data de Pagamento:'),0,0); $pdf->Cell(0,8,date('d/m/Y',strtotime($pag['data_pagamento'])),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Método:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT',$pag['metodo_pagamento']),0,1);
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Referente ao ano:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT', $pag['referente_ano']),0,1);
$mesesNomes = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
$mesesPagos = [];
if (!empty($pag['meses'])) {
    foreach (explode(',', $pag['meses']) as $m) {
        $m = (int)trim($m);
        if (isset($mesesNomes[$m])) $mesesPagos[] = $mesesNomes[$m];
    }
}
$pdf->Cell(50,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Meses pagos:'),0,0); $pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT', implode(', ', $mesesPagos)),0,1);
$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,8,iconv('UTF-8','ISO-8859-1//TRANSLIT','Gerado em '.date('d/m/Y H:i')),0,1,'R');
$pdf->Output('I','recibo_pagamento.pdf');
