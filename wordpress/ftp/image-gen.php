<?php
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit;
}
header('X-Robots-Tag: noindex, nofollow', true);
header('X-Content-Type-Options: nosniff', true);
$limits = ['titulo' => 120, 'subtitulo' => 180, 'tag_top' => 40, 'tag_1' => 40, 'tag_2' => 40];
foreach ($limits as $field => $max) {
    $value = isset($_GET[$field]) ? trim((string) $_GET[$field]) : '';
    if (mb_strlen($value, 'UTF-8') > $max || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', $value)) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'invalid input';
        exit;
    }
}

// image-gen.php — Generador de imagen destacada cuadrada para doble3d.cl
// Contrato (sin cambios): GET tag_top, titulo, subtitulo, tag_1, tag_2 -> WebP 1080x1080
header('Content-Type: image/webp');

// 1. Variables dinámicas (con textos de respaldo)
$tag_top   = isset($_GET['tag_top']) ? mb_strtoupper($_GET['tag_top'], 'UTF-8') : 'CAPACITACIÓN INMERSIVA INDUSTRIAL';
$titulo    = $_GET['titulo']    ?? 'XR empresarial: Pico, AR e IA toman junio';
$subtitulo = $_GET['subtitulo'] ?? 'Tres señales de la semana en realidad virtual industrial';
$tag_1     = isset($_GET['tag_1']) ? mb_strtoupper($_GET['tag_1'], 'UTF-8') : 'PICO 4 ULTRA';
$tag_2     = isset($_GET['tag_2']) ? mb_strtoupper($_GET['tag_2'], 'UTF-8') : 'AWE 2026';

// 2. Lienzo 1080x1080 (estándar Instagram)
$W = 1080; $H = 1080;
$img = imagecreatetruecolor($W, $H);
imagealphablending($img, true);
imagesavealpha($img, true);

// 3. Paleta oficial
$negro   = imagecolorallocate($img, 10, 5, 29);    // #0a051d
$azul    = imagecolorallocate($img, 0, 102, 255);  // #0066ff
$violeta = imagecolorallocate($img, 107, 60, 226); // #6b3ce2
$blanco  = imagecolorallocate($img, 255, 255, 255);
$gris    = imagecolorallocate($img, 160, 170, 184);// #a0aab8

// 4. Fondo + resplandor radial (esquina inferior derecha)
imagefill($img, 0, 0, $negro);
for ($r = 720; $r > 0; $r -= 2) {
    $a = (int)(127 * ($r / 720)); // más transparente hacia afuera
    $c = imagecolorallocatealpha($img, 0, 102, 255, $a);
    imagefilledellipse($img, 980, 980, $r * 2, $r * 2, $c);
}

// 5. Rejilla de puntos
$pt = imagecolorallocatealpha($img, 255, 255, 255, 110);
$step = 38;
for ($x = 54; $x < $W; $x += $step) {
    for ($y = 54; $y < $H; $y += $step) {
        imagefilledellipse($img, $x, $y, 5, 5, $pt);
    }
}

// 6. Fuente TTF (subir Inter-Bold.ttf junto a este archivo)
$font = __DIR__ . '/Inter-Bold.ttf';
$hasFont = is_file($font);

$marginX = 76;
$contentW = $W - 2 * $marginX; // ~928 px útiles

// Helpers de medición (solo válidos con fuente TTF)
$measureW = function ($size, $str) use ($font) {
    $b = imagettfbbox($size, 0, $font, $str);
    return abs($b[2] - $b[0]);
};
$wrapPx = function ($str, $size, $maxw) use ($font, $measureW) {
    $words = preg_split('/\s+/', trim($str));
    $lines = []; $cur = '';
    foreach ($words as $w) {
        $try = ($cur === '') ? $w : $cur . ' ' . $w;
        if ($measureW($size, $try) > $maxw && $cur !== '') { $lines[] = $cur; $cur = $w; }
        else { $cur = $try; }
    }
    if ($cur !== '') $lines[] = $cur;
    return $lines;
};

if ($hasFont) {
    // 7. Píldora superior (violeta) — ancho dinámico
    $tpSize = 18; $padX = 26;
    $pillX1 = $marginX; $pillY1 = 72; $pillH = 56; $pillY2 = $pillY1 + $pillH;
    $pillX2 = $pillX1 + $measureW($tpSize, $tag_top) + 2 * $padX;
    imagefilledrectangle($img, $pillX1, $pillY1, $pillX2, $pillY2, $violeta);
    imagettftext($img, $tpSize, 0, $pillX1 + $padX, $pillY1 + 38, $blanco, $font, $tag_top);

    // 8. Título principal (multilínea, se reduce si excede 4 líneas)
    $titleSize = 58;
    $titleLines = $wrapPx($titulo, $titleSize, $contentW);
    while (count($titleLines) > 4 && $titleSize > 40) {
        $titleSize -= 4;
        $titleLines = $wrapPx($titulo, $titleSize, $contentW);
    }
    $lineH = (int)($titleSize * 1.30);
    $y = $pillY2 + 90;
    foreach ($titleLines as $ln) {
        $y += $lineH;
        imagettftext($img, $titleSize, 0, $marginX, $y, $blanco, $font, $ln);
    }

    // 9. Subtítulo (gris) posicionado bajo el título real
    $subSize = 26;
    $subLines = $wrapPx($subtitulo, $subSize, $contentW);
    $subLineH = (int)($subSize * 1.38);
    $sy = $y + 60;
    foreach ($subLines as $ln) {
        $sy += $subLineH;
        imagettftext($img, $subSize, 0, $marginX, $sy, $gris, $font, $ln);
    }

    // 10. Píldoras inferiores — ancho dinámico
    $btSize = 18; $btY1 = $H - 150; $btH = 58; $btY2 = $btY1 + $btH; $bpadX = 24;
    // Tag 1: contorno azul
    $x1a = $marginX; $x1b = $x1a + $measureW($btSize, $tag_1) + 2 * $bpadX;
    imagefilledrectangle($img, $x1a, $btY1, $x1b, $btY2, $negro);
    imagerectangle($img, $x1a, $btY1, $x1b, $btY2, $azul);
    imagettftext($img, $btSize, 0, $x1a + $bpadX, $btY1 + 38, $blanco, $font, $tag_1);
    // Tag 2: relleno violeta (acento)
    $x2a = $x1b + 24; $x2b = $x2a + $measureW($btSize, $tag_2) + 2 * $bpadX;
    imagefilledrectangle($img, $x2a, $btY1, $x2b, $btY2, $violeta);
    imagettftext($img, $btSize, 0, $x2a + $bpadX, $btY1 + 38, $blanco, $font, $tag_2);

    // 11. Marca
    imagettftext($img, 20, 0, $marginX, $H - 58, $gris, $font, 'doble3d.cl');
} else {
    // Fallback sin TTF: fuente bitmap incorporada (nunca entrega imagen muda)
    imagefilledrectangle($img, $marginX, 72, $marginX + 540, 128, $violeta);
    imagestring($img, 5, $marginX + 14, 92, $tag_top, $blanco);
    $yy = 320;
    foreach (explode("\n", wordwrap($titulo, 40, "\n")) as $ln) { imagestring($img, 5, $marginX, $yy, $ln, $blanco); $yy += 42; }
    $yy += 24;
    foreach (explode("\n", wordwrap($subtitulo, 60, "\n")) as $ln) { imagestring($img, 4, $marginX, $yy, $ln, $gris); $yy += 28; }
    imagestring($img, 5, $marginX, $H - 150, $tag_1 . '    ' . $tag_2, $blanco);
    imagestring($img, 4, $marginX, $H - 60, 'doble3d.cl', $gris);
}

// 12. Exportar WebP calidad 85
imagewebp($img, null, 85);
imagedestroy($img);
?>
