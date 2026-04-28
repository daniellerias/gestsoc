<?php
// Iniciar output buffering para evitar erros de headers
if (ob_get_level() === 0) ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config.php';
require_once '../vendor/fpdf/fpdf.php';
require_once '../includes/pdf_helpers.php';

// Buscar dados da associação principal (id = 1)
$associacao = null;
try {
    $stmtA = $pdo->prepare('SELECT id, nome, morada, contacto, logotipo FROM associacoes WHERE id = 1 LIMIT 1');
    $stmtA->execute();
    $associacao = $stmtA->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $associacao = null;
}

// Adicionar o caminho base do servidor ao logotipo
$basePath = __DIR__ . '/../';
if (!empty($associacao['logotipo'])) {
    $associacao['logotipo'] = $basePath . $associacao['logotipo'];
}

// Definir Ano para as quotas (recebido do formulário, ou ano actual por omissão)
$anoActual = date('Y');
if (isset($_POST['ano']) && ctype_digit((string)$_POST['ano'])) {
    $anoActual = intval($_POST['ano']);
}

// Processamento principal — aceita POST com ids[]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);

    $tempFiles = [];
    $tempNames = [];

    foreach ($ids as $id) {
        // Buscar dados do sócio (incluir valor da quota via tipos_quotas)
    $stmt = $pdo->prepare("SELECT a.id, a.numero_socio, a.nome_completo, a.data_nascimento, a.bi_cc, a.nif, a.morada, a.telefone, a.telemovel, a.email, a.quota_id, q.valor AS valor_quota FROM socios a LEFT JOIN tipos_quotas q ON a.quota_id = q.id WHERE a.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $assoc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$assoc) continue;

    // Criar PDF
    $pdf = new FPDF('P', 'mm', 'A5'); // A5 format
    $pdf->SetAutoPageBreak(true, 10); // menor margem inferior para A5
    $pdf->AddPage();
    // Margem superior 35mm
    $pdf->SetY(35);
        // header da associação (nome)
        if ($associacao) {
            $logoWidth = 22; // menor para A5
            $logoY = $pdf->GetY();
            $pageWidth = $pdf->GetPageWidth();
            // Centralizar header
            $headerBlockW = max($logoWidth, 70); // largura mínima para header
            $headerX = ($pageWidth - $headerBlockW) / 2;
            $pdf->SetXY($headerX, $logoY);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell($headerBlockW,6,pdf_text($associacao['nome']),0,1,'C');
            // Linha de separação horizontal (toda a largura da página)
            $pdf->SetDrawColor(180, 180, 180);
            $pdf->SetLineWidth(0.3);
            $yLine1 = $pdf->GetY() + 2;
            $pdf->Line(0, $yLine1, $pageWidth, $yLine1);
            $pdf->Ln(4);
        }

        // Primeira linha: Nome e número de sócio
        $pdf->SetFont('Arial', '', 9);
        $pageWidth = $pdf->GetPageWidth();
        $blockW = 90; // largura bloco
        $blockX = ($pageWidth - $blockW) / 2;
        $pdf->SetX($blockX);
        $wName = $blockW * 0.6;
        $wNum = $blockW - $wName;
        
        $pdf->Cell($wName, 5, pdf_text('Nome: ' . ($assoc['nome_completo'] ?? '')), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($wNum, 5, pdf_text('Sócio Nº.: ' . ($assoc['numero_socio'] ?? '')), 0, 1, 'R');

        // Segunda linha: Morada
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetX($blockX);
        $pdf->Cell($blockW, 5, pdf_text('Morada: ' . ($assoc['morada'] ?? '')), 0, 1);

        // Terceira linha: Telefone e Telemóvel
    $pdf->SetX($blockX);
    $wcol = $blockW / 2;
    $pdf->Cell($wcol, 5, pdf_text('Telefone: ' . ($assoc['telefone'] ?? '')), 0, 0);
    $pdf->Cell($wcol, 5, pdf_text('Telemóvel: ' . ($assoc['telemovel'] ?? '')), 0, 1, 'R');
    $pdf->Ln(4);

    // Segunda linha de separação horizontal (toda a largura da página)
    $pdf->SetDrawColor(180, 180, 180);
    $pdf->SetLineWidth(0.3);
    $yLine2 = $pdf->GetY() + 2;
    $pdf->Line(0, $yLine2, $pageWidth, $yLine2);
    $pdf->Ln(14); // Espaço antes da tabela

        // Tabela 12 células (3 cols x 4 rows) adaptada para A5
    $cols = 3;
    $rows = 4;
    $gap = 0;
    $fixedMargin = 6;
    $maxGridW = 100;
    $startY = $pdf->GetY();

    // Inverter a ordem dos meses
    $mesesInvertidos = array_reverse($meses);

    // Calcular largura máxima possível para células quadradas
    $usableWidth = min($pdf->GetPageWidth() - 2 * $fixedMargin, $maxGridW);
    $cellW = $usableWidth / $cols;
    $cellH = $cellW * 0.83; // células mais baixas (83% da largura)
    $usableHeight = $cellH * $rows;

    // Centralizar grelha verticalmente (mas respeitando topo)
    $startX = ($pdf->GetPageWidth() - $usableWidth) / 2;
    // Se não couber, força topo
    $startY = min($startY, $pdf->GetPageHeight() - $usableHeight - $fixedMargin);

        // Extrair apenas o primeiro e último nome
        function primeiroUltimoNome($nomeCompleto) {
            $partes = preg_split('/\s+/', trim($nomeCompleto));
            if (count($partes) === 0) return '';
            if (count($partes) === 1) return $partes[0];
            return $partes[0] . ' ' . $partes[count($partes)-1];
        }

        $monthIndex = 0;
        $pdf->SetXY($startX, $startY);
        for ($r = 0; $r < $rows; $r++) {
            $y = $startY + $r * ($cellH + $gap);
            for ($c = 0; $c < $cols; $c++) {
                $x = $startX + $c * ($cellW + $gap);
                $pdf->SetXY($x, $y);
                dottedRect($pdf, $x, $y, $cellW, $cellH, 2, 1.5);

                $pad = 2;
                $lineH = 4.2;
                $logoOffset = 0;
                // Aumentar ligeiramente o tamanho do logotipo
                $logoCellSize = min($cellW * 0.18, $cellH * 0.18, 9);
                // Logotipo pequeno da associação (quadrado, alinhado à esquerda na célula)
                if (!empty($associacao['logotipo']) && file_exists($associacao['logotipo'])) {
                    $logoX = $x + $pad;
                    $logoY = $y + $pad;
                    $pdf->Image($associacao['logotipo'], $logoX, $logoY, $logoCellSize, $logoCellSize);
                    $logoOffset = $logoCellSize + 1.2; // espaço extra após logotipo
                }

                // Número do mês (em negrito, tamanho 9) alinhado à direita na mesma linha do logotipo
                $pdf->SetFont('Arial', 'B', 9);
                $numeroMes = 12 - $monthIndex; // porque meses estão invertidos
                $numX = $x + $cellW - $pad - 2; // margem direita
                $numY = $y + $pad;
                $pdf->SetXY($numX - 7, $numY); // 7mm de largura para o número
                $pdf->Cell(7, $logoCellSize > 0 ? $logoCellSize : 5, $numeroMes, 0, 0, 'R');

                // 1ª linha: Nome do mês + ano (ex: Janeiro 2024)
                $pdf->SetFont('Arial', 'B', 8.5);
                $pdf->SetXY($x, $y + $pad + $logoOffset);
                $pdf->Cell($cellW, $lineH, pdf_text($mesesInvertidos[$monthIndex] . ' ' . $anoActual), 0, 0, 'C');

                // 2ª linha: Primeiro e último nome
                $pdf->SetFont('Arial', '', 8.5);
                $nomeReduzido = isset($assoc['nome_completo']) ? primeiroUltimoNome($assoc['nome_completo']) : '';
                $pdf->SetXY($x, $y + $pad + $logoOffset + 1 * $lineH);
                $pdf->Cell($cellW, $lineH, pdf_text($nomeReduzido), 0, 0, 'C');

                // 3ª linha: Nº do sócio
                $pdf->SetFont('Arial', '', 8.5);
                $pdf->SetXY($x, $y + $pad + $logoOffset + 2 * $lineH);
                $pdf->Cell($cellW, $lineH, pdf_text('Sócio n.º ' . ($assoc['numero_socio'] ?? '')), 0, 0, 'C');

                // 4ª linha: Valor da quota mensal
                $pdf->SetFont('Arial', 'B', 8.5);
                $valorQuota = isset($assoc['valor_quota']) ? number_format((float)$assoc['valor_quota'], 2, ',', '.') : '0,00';
                $pdf->SetXY($x, $y + $pad + $logoOffset + 3 * $lineH);
                $pdf->Cell($cellW, $lineH, pdf_text($valorQuota . ' €'), 0, 0, 'C');

                $monthIndex++;
                if ($monthIndex >= 12) break 2;
            }
        }

        // Guardar ficheiro temporário
    $safeName = ($assoc['numero_socio'] ?? 's' . $assoc['id']);
    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "quotas_{$safeName}_{$assoc['id']}.pdf";
    $pdf->Output('F', $tmpPath);
    $tempFiles[] = $tmpPath;
    // gerar nome para download: <numero>_<nome>.pdf (permite acentos e especiais)
        // Extrair apenas o primeiro e último nome para o nome do ficheiro
        $nomeCompleto = $assoc['nome_completo'] ?? 'assoc';
        $partesNome = preg_split('/\s+/', trim($nomeCompleto));
        if (count($partesNome) === 0) {
            $nomeReduzido = '';
        } elseif (count($partesNome) === 1) {
            $nomeReduzido = $partesNome[0];
        } else {
            $nomeReduzido = $partesNome[0] . ' ' . $partesNome[count($partesNome)-1];
        }
        $downloadSafeName = trim((string)($assoc['numero_socio'] ?? 's' . $assoc['id']) . '_' . $nomeReduzido);
        $tempNames[] = $downloadSafeName . '.pdf';
    }

    if (empty($tempFiles)) {
        $_SESSION['flash'] = 'Nenhum PDF gerado.';
        header('Location: gerar_quotas.php');
        exit;
    }

    if (count($tempFiles) === 1) {
        // Enviar único PDF
        $file = $tempFiles[0];
        $downloadName = $tempNames[0] ?? 'quotas.pdf';
        // limpar todos os buffers de saída para prevenir qualquer output extra
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        // enviar ficheiro e garantir flush
        readfile($file);
        flush();
        unlink($file);
        exit;
    }

    // Empacotar vários PDFs num ZIP
    $zipName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'quotas_' . time() . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipName, ZipArchive::CREATE) !== true) {
        $_SESSION['flash'] = 'Erro ao criar zip.';
        header('Location: gerar_quotas.php');
        exit;
    }
    foreach ($tempFiles as $i => $f) {
        $nameInZip = $tempNames[$i] ?? basename($f);
        $zip->addFile($f, $nameInZip);
    }
    $zip->close();

    // Enviar o zip
    // limpar buffers e enviar zip
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="quotas.zip"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($zipName));
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    readfile($zipName);
    flush();

    // Limpar temporários
    foreach ($tempFiles as $f) { if (file_exists($f)) unlink($f); }
    if (file_exists($zipName)) unlink($zipName);
    exit;
} else {
    // acesso inválido - redirecionar de volta
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['flash'] = 'Pedido inválido para gerar PDFs.';
    header('Location: gerar_quotas.php');
    exit;
}
