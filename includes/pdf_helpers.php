<?php
// Helpers para geração de PDFs (FPDF)

// Helper para texto compatível com FPDF (converte UTF-8 -> ISO-8859-1)
if (!function_exists('pdf_text')) {
    function pdf_text($s) {
        if ($s === null) return '';
        if (is_callable('iconv')) {
            $out = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
            if ($out !== false) return $out;
        }
        if (is_callable('mb_convert_encoding')) {
            $out = @mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
            if ($out !== false) return $out;
        }
        return preg_replace('/[\x{80}-\x{FFFF}]/u', '?', $s);
    }
}

// Helper: encurta um texto para caber numa largura (em mm) no PDF, preferindo "Primeira … Última"
if (!function_exists('pdf_shorten')) {
    function pdf_shorten($pdf, $text, $maxWidth, $font = 'Arial', $style = '', $size = 11) {
        $orig = (string)($text ?? '');
        $encoded = pdf_text($orig);
        $pdf->SetFont($font, $style, $size);
        if ($pdf->GetStringWidth($encoded) <= $maxWidth) return $encoded;
        $words = preg_split('/\s+/', trim($orig));
        if (is_array($words) && count($words) >= 2) {
            $first = $words[0];
            $last = $words[count($words) - 1];
            $candidate = pdf_text($first . ' … ' . $last);
            if ($pdf->GetStringWidth($candidate) <= $maxWidth) return $candidate;
        }
        $first = isset($words[0]) ? $words[0] : '';
        $encFirst = pdf_text($first);
        if ($pdf->GetStringWidth($encFirst) <= $maxWidth) return $encFirst;
        $len = function_exists('mb_strlen') ? mb_strlen($first, 'UTF-8') : strlen($first);
        for ($i = $len; $i > 0; $i--) {
            $part = function_exists('mb_substr') ? mb_substr($first, 0, $i, 'UTF-8') : substr($first, 0, $i);
            $enc = pdf_text($part);
            if ($pdf->GetStringWidth($enc) <= $maxWidth) return $enc;
        }
        return '';
    }
}

// função para desenhar rectângulo pontilhado (inactiva)
/*if (!function_exists('dottedRect')) {
    function dottedRect($pdf, $x, $y, $w, $h, $dot=3, $gap=2) {
        for ($px = $x; $px < $x + $w; $px += ($dot + $gap)) {
            $x2 = min($px + $dot, $x + $w);
            $pdf->Line($px, $y, $x2, $y);
            $pdf->Line($px, $y + $h, $x2, $y + $h);
        }
        for ($py = $y; $py < $y + $h; $py += ($dot + $gap)) {
            $y2 = min($py + $dot, $y + $h);
            $pdf->Line($x, $py, $x, $y2);
            $pdf->Line($x + $w, $py, $x + $w, $y2);
        }
    }
} */

// função para desenhar rectângulo - sólido e preto
if (!function_exists('dottedRect')) {
    function dottedRect($pdf, $x, $y, $w, $h, $dot=3, $gap=2) {
        // definir cor e espessura de linha para sólido preto
        $pdf->SetDrawColor(0, 0, 0);
        // 0.2mm é a largura de linha padrão para FPDF; ajustar se necessário
        $pdf->SetLineWidth(0.2);
        // desenhar retângulo completo
        $pdf->Rect($x, $y, $w, $h);
    }
}

// Lista de meses (pt)
$meses = [
    'Janeiro','Fevereiro','Março','Abril','Maio','Junho',
    'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'
];
