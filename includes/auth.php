<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('USERS_FILE', __DIR__ . '/../data/users.json');

function getUsers() {
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([]));
        return [];
    }
    return json_decode(file_get_contents(USERS_FILE), true) ?: [];
}

function saveUsers($users) {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $users = getUsers();
    return $users[$_SESSION['user_id']] ?? null;
}

function login($username, $password) {
    $users = getUsers();
    foreach ($users as $id => $user) {
        if (strtolower($user['username']) === strtolower($username) && 
            password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $user['username'];
            // Update last login
            $users[$id]['last_login'] = date('Y-m-d H:i:s');
            saveUsers($users);
            return true;
        }
    }
    return false;
}

function register($username, $password, $email) {
    $users = getUsers();
    // Check if username exists
    foreach ($users as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            return ['success' => false, 'message' => 'Bu kullanıcı adı zaten kullanılıyor.'];
        }
        if (!empty($email) && strtolower($user['email']) === strtolower($email)) {
            return ['success' => false, 'message' => 'Bu e-posta zaten kullanılıyor.'];
        }
    }
    if (strlen($username) < 3) {
        return ['success' => false, 'message' => 'Kullanıcı adı en az 3 karakter olmalı.'];
    }
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Şifre en az 6 karakter olmalı.'];
    }
    $id = uniqid('u_', true);
    $users[$id] = [
        'id' => $id,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'avatar' => 'https://api.dicebear.com/7.x/thumbs/svg?seed=' . urlencode($username),
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => date('Y-m-d H:i:s'),
        'favorites' => [],
        'watchlist' => [],
        'watch_history' => []
    ];
    saveUsers($users);
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;
    return ['success' => true];
}

function logout() {
    session_destroy();
}

function addToFavorites($userId, $item) {
    $users = getUsers();
    if (!isset($users[$userId])) return false;
    $favs = $users[$userId]['favorites'] ?? [];
    $key = $item['type'] . '_' . $item['id'];
    $favs[$key] = array_merge($item, ['added_at' => date('Y-m-d H:i:s')]);
    $users[$userId]['favorites'] = $favs;
    saveUsers($users);
    return true;
}

function removeFromFavorites($userId, $key) {
    $users = getUsers();
    if (!isset($users[$userId])) return false;
    unset($users[$userId]['favorites'][$key]);
    saveUsers($users);
    return true;
}

function addToWatchHistory($userId, $item) {
    $users = getUsers();
    if (!isset($users[$userId])) return false;
    $history = $users[$userId]['watch_history'] ?? [];
    array_unshift($history, array_merge($item, ['watched_at' => date('Y-m-d H:i:s')]));
    $history = array_slice($history, 0, 100); // keep last 100
    $users[$userId]['watch_history'] = $history;
    saveUsers($users);
    return true;
}
