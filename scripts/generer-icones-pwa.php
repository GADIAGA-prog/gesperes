<?php
/**
 * Génère les icônes PWA de l'espace agent à partir de public/images/logo.png.
 * Fond institution (#1e40af), logo centré avec marge maskable (~72 %).
 * Usage : php scripts/generer-icones-pwa.php
 */

$src = __DIR__ . '/../public/images/logo.png';
$dest = __DIR__ . '/../public/images/icons';

if (! is_file($src)) {
    fwrite(STDERR, "logo.png introuvable\n");
    exit(1);
}

[$bg_r, $bg_g, $bg_b] = [0x1e, 0x40, 0xaf]; // institution-700
$logo = imagecreatefrompng($src);
$logoW = imagesx($logo);
$logoH = imagesy($logo);

/** @param bool $maskable padding (zone de sécurité) si true */
$rendre = function (int $taille, bool $maskable, string $fichier) use ($logo, $logoW, $logoH, $bg_r, $bg_g, $bg_b, $dest) {
    $canvas = imagecreatetruecolor($taille, $taille);
    imagealphablending($canvas, true);
    $bg = imagecolorallocate($canvas, $bg_r, $bg_g, $bg_b);
    imagefilledrectangle($canvas, 0, 0, $taille, $taille, $bg);

    // Marge : maskable => logo à ~62 %, sinon ~80 %.
    $ratio = $maskable ? 0.62 : 0.80;
    $cible = (int) round($taille * $ratio);
    $echelle = min($cible / $logoW, $cible / $logoH);
    $dw = (int) round($logoW * $echelle);
    $dh = (int) round($logoH * $echelle);
    $dx = (int) round(($taille - $dw) / 2);
    $dy = (int) round(($taille - $dh) / 2);

    imagecopyresampled($canvas, $logo, $dx, $dy, 0, 0, $dw, $dh, $logoW, $logoH);

    imagepng($canvas, $dest . '/' . $fichier, 9);
    imagedestroy($canvas);
    echo "✓ {$fichier}\n";
};

$rendre(512, true,  'icon-512.png');
$rendre(192, true,  'icon-192.png');
$rendre(180, false, 'apple-touch-icon.png');
$rendre(32,  false, 'favicon-32.png');

imagedestroy($logo);
echo "Icônes générées dans public/images/icons\n";
