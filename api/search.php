<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/data.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = searchContent($q, 20);
echo json_encode($results, JSON_UNESCAPED_UNICODE);
