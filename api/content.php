<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/data.php';
require_once '../includes/tmdb.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$type = $_GET['type'] ?? 'film';
$name = urldecode($_GET['name'] ?? '');

if (empty($name)) {
    echo json_encode(['error' => 'Name required']);
    exit;
}

$response = [];

if ($type === 'film') {
    $film = getFilmByName($name);
    if (!$film) {
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    $tmdb = getContentInfo($name, 'film');
    $response = [
        'type' => 'film',
        'name' => $film['name'],
        'logo' => $film['logo'],
        'genres' => $film['genres'],
        'versions' => $film['versions'],
        'tmdb' => $tmdb
    ];
} else {
    $show = getShowByName($name);
    if (!$show) {
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    $tmdb = getContentInfo($name, 'dizi');
    
    // Format seasons
    $seasons_out = [];
    foreach ($show['seasons'] as $s => $eps) {
        $unique = [];
        $seen = [];
        foreach ($eps as $ep) {
            $k = $ep['episode'];
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $unique[] = $ep;
            }
        }
        usort($unique, fn($a,$b) => $a['episode'] - $b['episode']);
        $seasons_out[$s] = $unique;
    }
    ksort($seasons_out);
    
    $response = [
        'type' => 'dizi',
        'name' => $show['name'],
        'logo' => $show['logo'],
        'seasons' => $seasons_out,
        'tmdb' => $tmdb
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
