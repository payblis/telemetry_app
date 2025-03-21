<?php
// Configuration
$gentelella_version = "master";
$vendors_dir = "vendors";
$css_dir = "css";
$js_dir = "js";
$temp_dir = "temp";

// Création des répertoires nécessaires
$dirs = [$vendors_dir, $css_dir, $js_dir, $temp_dir];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Téléchargement de Gentelella
$zip_url = "https://github.com/ColorlibHQ/gentelella/archive/refs/heads/master.zip";
$zip_file = $temp_dir . "/gentelella.zip";

echo "Téléchargement de Gentelella...\n";
file_put_contents($zip_file, file_get_contents($zip_url));

// Extraction du ZIP
$zip = new ZipArchive;
if ($zip->open($zip_file) === TRUE) {
    $zip->extractTo($temp_dir);
    $zip->close();
    echo "Extraction réussie.\n";
} else {
    echo "Échec de l'extraction.\n";
    exit(1);
}

// Copie des fichiers nécessaires
$source_dir = $temp_dir . "/gentelella-" . $gentelella_version;

// Copie des vendors
$vendor_dirs = [
    'bootstrap',
    'font-awesome',
    'nprogress',
    'fastclick',
    'Chart.js',
    'jquery-sparkline',
    'Flot',
    'DateJS'
];

foreach ($vendor_dirs as $dir) {
    if (file_exists($source_dir . "/vendors/" . $dir)) {
        recurse_copy($source_dir . "/vendors/" . $dir, $vendors_dir . "/" . $dir);
    }
}

// Copie des fichiers CSS et JS
copy($source_dir . "/build/css/custom.min.css", $css_dir . "/custom.min.css");
copy($source_dir . "/build/js/custom.min.js", $js_dir . "/custom.min.js");

// Nettoyage
array_map('unlink', glob($temp_dir . "/*.*"));
rmdir($temp_dir);

echo "Installation terminée.\n";

// Fonction pour copier récursivement les dossiers
function recurse_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
} 