<?php
/**
 * StreamFlix — HLS Stream Proxy
 * CORS sorununu aşmak için stream URL'lerini sunucu üzerinden proxy'ler
 */
session_start();
require_once __DIR__ . '/includes/auth.php';

// Sadece giriş yapmış kullanıcılar
if (!isLoggedIn()) {
    http_response_code(403);
    exit('Unauthorized');
}

$url = $_GET['url'] ?? '';
if (empty($url)) {
    http_response_code(400);
    exit('URL required');
}

// Sadece http/https URL'lerine izin ver
if (!preg_match('#^https?://#i', $url)) {
    http_response_code(400);
    exit('Invalid URL');
}

// Güvenlik: Lokal IP'lere erişimi engelle
if (preg_match('#^https?://(localhost|127\.|192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.)#i', $url)) {
    http_response_code(403);
    exit('Forbidden');
}

// M3U8 içeriğini fetch et
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER     => [
        'Referer: https://www.google.com/',
        'Accept: */*',
        'Accept-Language: tr-TR,tr;q=0.9',
        'Origin: https://www.google.com',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
    ],
    CURLOPT_HEADER         => true,  // response header'larını da al
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if (!$response || $httpCode < 200 || $httpCode >= 400) {
    http_response_code(502);
    exit('Stream unavailable');
}

$body = substr($response, $headerSize);

// M3U8 dosyasıysa içindeki relative URL'leri absolute'a çevir
$baseUrl = preg_replace('#[^/]*$#', '', $url);  // URL'nin dizini

if (strpos($contentType, 'mpegurl') !== false || strpos($contentType, 'm3u') !== false 
    || str_contains($url, '.m3u8')) {
    
    // Relative .m3u8 ve .ts URL'lerini proxy üzerinden geçir
    $siteUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $proxyBase = $siteUrl . '/proxy.php?url=';

    $lines = explode("\n", $body);
    $newLines = [];
    foreach ($lines as $line) {
        $line = rtrim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            $newLines[] = $line;
            continue;
        }
        // Bu satır bir URL
        if (!preg_match('#^https?://#i', $line)) {
            // Relative URL → absolute yap
            if (str_starts_with($line, '/')) {
                // Root-relative
                $parsed = parse_url($url);
                $line = $parsed['scheme'] . '://' . $parsed['host'] . $line;
            } else {
                $line = $baseUrl . $line;
            }
        }
        // Proxy üzerinden geçir
        $newLines[] = $proxyBase . urlencode($line);
    }
    $body = implode("\n", $newLines);
    $contentType = 'application/vnd.apple.mpegurl';
}

// CORS header'ları ekle — tarayıcının okuyabilmesi için
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
header('Cache-Control: no-cache, no-store');

echo $body;
