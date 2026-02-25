<?php

define('TMDB_API_KEY', '5a9cb9e0d8d510f5acfd0240871e6352');
define('TMDB_TOKEN', 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1YTljYjllMGQ4ZDUxMGY1YWNmZDAyNDA4NzFlNjM1MiIsIm5iZiI6MTc3MTk1NTU3OC43MzcsInN1YiI6IjY5OWRlNTdhNDA5ZTg4OWU2MDQ1NWY2NiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.7t2kzHEfvf37RU0ELNBZIVfIpWxEkK-ofPozZn-ichk');
define('TMDB_CACHE_DIR', __DIR__ . '/../data/tmdb_cache/');
define('TMDB_CACHE_TTL', 86400 * 7); // 7 days

function tmdbRequest($endpoint, $params = []) {
    if (!is_dir(TMDB_CACHE_DIR)) {
        mkdir(TMDB_CACHE_DIR, 0777, true);
    }
    
    $params['language'] = 'tr-TR';
    $url = 'https://api.themoviedb.org/3' . $endpoint . '?' . http_build_query($params);
    $cacheKey = TMDB_CACHE_DIR . md5($url) . '.json';
    
    // Check cache
    if (file_exists($cacheKey) && (time() - filemtime($cacheKey)) < TMDB_CACHE_TTL) {
        return json_decode(file_get_contents($cacheKey), true);
    }
    
    $ctx = stream_context_create([
        'http' => [
            'header' => [
                'Authorization: Bearer ' . TMDB_TOKEN,
                'Content-Type: application/json'
            ],
            'timeout' => 8
        ]
    ]);
    
    $result = @file_get_contents($url, false, $ctx);
    if ($result) {
        file_put_contents($cacheKey, $result);
        return json_decode($result, true);
    }
    return null;
}

function tmdbSearchMovie($title) {
    // Clean title: remove year, rating, etc.
    $clean = preg_replace('/\s*\(\d{4}\).*$/', '', $title);
    $clean = preg_replace('/\s*\|\s*⭐.*$/', '', $clean);
    $clean = preg_replace('/\s*\[[\d.]+\]\s*/', '', $clean);
    $clean = trim($clean);
    
    $data = tmdbRequest('/search/movie', ['query' => $clean]);
    if ($data && !empty($data['results'])) {
        return $data['results'][0];
    }
    return null;
}

function tmdbSearchTV($title) {
    $clean = trim($title);
    $data = tmdbRequest('/search/tv', ['query' => $clean]);
    if ($data && !empty($data['results'])) {
        return $data['results'][0];
    }
    return null;
}

function tmdbGetMovieDetails($id) {
    return tmdbRequest('/movie/' . $id, ['append_to_response' => 'credits,videos']);
}

function tmdbGetTVDetails($id) {
    return tmdbRequest('/tv/' . $id, ['append_to_response' => 'credits,videos']);
}

function tmdbImgUrl($path, $size = 'w500') {
    if (empty($path)) return 'assets/placeholder.svg';
    return 'https://image.tmdb.org/t/p/' . $size . $path;
}

function getContentInfo($name, $type = 'film') {
    if ($type === 'film') {
        $result = tmdbSearchMovie($name);
        if ($result) {
            $details = tmdbGetMovieDetails($result['id']);
            return formatMovieInfo($details ?: $result);
        }
    } else {
        $result = tmdbSearchTV($name);
        if ($result) {
            $details = tmdbGetTVDetails($result['id']);
            return formatTVInfo($details ?: $result);
        }
    }
    return null;
}

function formatMovieInfo($data) {
    $trailer = '';
    if (!empty($data['videos']['results'])) {
        foreach ($data['videos']['results'] as $v) {
            if ($v['type'] === 'Trailer' && $v['site'] === 'YouTube') {
                $trailer = $v['key'];
                break;
            }
        }
    }
    $cast = [];
    if (!empty($data['credits']['cast'])) {
        foreach (array_slice($data['credits']['cast'], 0, 6) as $c) {
            $cast[] = [
                'name' => $c['name'],
                'character' => $c['character'] ?? '',
                'photo' => !empty($c['profile_path']) ? tmdbImgUrl($c['profile_path'], 'w185') : ''
            ];
        }
    }
    return [
        'tmdb_id' => $data['id'],
        'title' => $data['title'] ?? $data['original_title'] ?? '',
        'title_tr' => $data['title'] ?? '',
        'overview' => $data['overview'] ?? '',
        'poster' => !empty($data['poster_path']) ? tmdbImgUrl($data['poster_path'], 'w500') : '',
        'backdrop' => !empty($data['backdrop_path']) ? tmdbImgUrl($data['backdrop_path'], 'w1280') : '',
        'year' => !empty($data['release_date']) ? substr($data['release_date'], 0, 4) : '',
        'rating' => number_format($data['vote_average'] ?? 0, 1),
        'vote_count' => $data['vote_count'] ?? 0,
        'runtime' => $data['runtime'] ?? 0,
        'genres' => array_column($data['genres'] ?? [], 'name'),
        'tagline' => $data['tagline'] ?? '',
        'trailer' => $trailer,
        'cast' => $cast,
        'status' => $data['status'] ?? ''
    ];
}

function formatTVInfo($data) {
    $trailer = '';
    if (!empty($data['videos']['results'])) {
        foreach ($data['videos']['results'] as $v) {
            if ($v['type'] === 'Trailer' && $v['site'] === 'YouTube') {
                $trailer = $v['key'];
                break;
            }
        }
    }
    $cast = [];
    if (!empty($data['credits']['cast'])) {
        foreach (array_slice($data['credits']['cast'], 0, 6) as $c) {
            $cast[] = [
                'name' => $c['name'],
                'character' => $c['character'] ?? '',
                'photo' => !empty($c['profile_path']) ? tmdbImgUrl($c['profile_path'], 'w185') : ''
            ];
        }
    }
    return [
        'tmdb_id' => $data['id'],
        'title' => $data['name'] ?? $data['original_name'] ?? '',
        'overview' => $data['overview'] ?? '',
        'poster' => !empty($data['poster_path']) ? tmdbImgUrl($data['poster_path'], 'w500') : '',
        'backdrop' => !empty($data['backdrop_path']) ? tmdbImgUrl($data['backdrop_path'], 'w1280') : '',
        'year' => !empty($data['first_air_date']) ? substr($data['first_air_date'], 0, 4) : '',
        'rating' => number_format($data['vote_average'] ?? 0, 1),
        'vote_count' => $data['vote_count'] ?? 0,
        'episode_count' => $data['number_of_episodes'] ?? 0,
        'season_count' => $data['number_of_seasons'] ?? 0,
        'genres' => array_column($data['genres'] ?? [], 'name'),
        'tagline' => $data['tagline'] ?? '',
        'trailer' => $trailer,
        'cast' => $cast,
        'status' => $data['status'] ?? ''
    ];
}
