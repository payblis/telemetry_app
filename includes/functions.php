<?php
/**
 * Fonctions utilitaires pour le formatage des données
 */

/**
 * Formate un temps en secondes en format MM:SS.mmm
 * @param float $seconds Le temps en secondes
 * @return string Le temps formaté
 */
function formatTime($seconds) {
    if (!$seconds) return 'N/A';
    
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    $milliseconds = round(($seconds - floor($seconds)) * 1000);
    $seconds = floor($seconds);
    
    return sprintf('%02d:%02d.%03d', $minutes, $seconds, $milliseconds);
}

/**
 * Formate une vitesse en km/h
 * @param float $speed La vitesse en km/h
 * @return string La vitesse formatée
 */
function formatSpeed($speed) {
    if (!$speed) return 'N/A';
    return round($speed) . ' km/h';
}

/**
 * Formate un angle en degrés
 * @param float $angle L'angle en degrés
 * @return string L'angle formaté
 */
function formatAngle($angle) {
    if (!$angle) return 'N/A';
    return round($angle, 1) . '°';
}

/**
 * Formate une date en format français
 * @param string $date La date au format MySQL
 * @return string La date formatée
 */
function formatDate($date) {
    if (!$date) return 'N/A';
    return date('d/m/Y', strtotime($date));
}

/**
 * Formate une date et heure en format français
 * @param string $datetime La date et heure au format MySQL
 * @return string La date et heure formatées
 */
function formatDateTime($datetime) {
    if (!$datetime) return 'N/A';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Formate une distance en kilomètres
 * @param float $distance La distance en kilomètres
 * @return string La distance formatée
 */
function formatDistance($distance) {
    if (!$distance) return 'N/A';
    return round($distance, 2) . ' km';
}

/**
 * Formate une température en degrés Celsius
 * @param float $temperature La température en degrés Celsius
 * @return string La température formatée
 */
function formatTemperature($temperature) {
    if (!$temperature) return 'N/A';
    return round($temperature, 1) . '°C';
}

/**
 * Formate une pression en bar
 * @param float $pressure La pression en bar
 * @return string La pression formatée
 */
function formatPressure($pressure) {
    if (!$pressure) return 'N/A';
    return round($pressure, 2) . ' bar';
}

/**
 * Formate une force en g
 * @param float $force La force en g
 * @return string La force formatée
 */
function formatForce($force) {
    if (!$force) return 'N/A';
    return round($force, 2) . ' g';
} 