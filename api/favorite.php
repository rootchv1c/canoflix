<?php
session_start();
require_once '../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'toggle';
$name = $data['name'] ?? '';
$type = $data['type'] ?? 'film';
$logo = $data['logo'] ?? '';

if (empty($name)) {
    echo json_encode(['error' => 'Name required']);
    exit;
}

$userId = $_SESSION['user_id'];
$users = getUsers();
$user = $users[$userId] ?? null;

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

$key = $type . '_' . md5($name);
$favs = $user['favorites'] ?? [];
$isFav = isset($favs[$key]);

if ($isFav) {
    removeFromFavorites($userId, $key);
    echo json_encode(['success' => true, 'favorited' => false, 'message' => 'Favorilerden çıkarıldı']);
} else {
    addToFavorites($userId, ['id' => md5($name), 'type' => $type, 'name' => $name, 'logo' => $logo]);
    echo json_encode(['success' => true, 'favorited' => true, 'message' => 'Favorilere eklendi']);
}
