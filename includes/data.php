<?php

define('FILMS_FILE', __DIR__ . '/../data/filmler.json');
define('SHOWS_FILE', __DIR__ . '/../data/diziler.json');

function loadFilms() {
    static $films = null;
    if ($films === null) {
        $films = json_decode(file_get_contents(FILMS_FILE), true) ?: [];
    }
    return $films;
}

function loadShows() {
    static $shows = null;
    if ($shows === null) {
        $shows = json_decode(file_get_contents(SHOWS_FILE), true) ?: [];
    }
    return $shows;
}

function getRecentFilms($limit = 20) {
    $films = loadFilms();
    return array_slice($films, 0, $limit);
}

function getRecentShows($limit = 20) {
    $shows = loadShows();
    $result = [];
    foreach ($shows as $name => $data) {
        $seasons = [];
        foreach ($data['episodes'] as $ep) {
            $seasons[$ep['season']] = true;
        }
        $result[] = [
            'name' => $name,
            'logo' => $data['logo'],
            'episode_count' => count($data['episodes']),
            'season_count' => count($seasons)
        ];
    }
    return array_slice($result, 0, $limit);
}

function getFeaturedFilms($limit = 8) {
    $films = loadFilms();
    // Pick films with good logos (not placeholder)
    $featured = array_filter($films, function($f) {
        return !empty($f['logo']) && strpos($f['logo'], 'placeholder') === false;
    });
    shuffle($featured);
    return array_slice(array_values($featured), 0, $limit);
}

function getAllGenres() {
    $films = loadFilms();
    $genres = [];
    foreach ($films as $film) {
        foreach ($film['genres'] as $g) {
            $genres[$g] = ($genres[$g] ?? 0) + 1;
        }
    }
    arsort($genres);
    return array_keys($genres);
}

function getFilmsByGenre($genre, $limit = 20, $offset = 0) {
    $films = loadFilms();
    $result = [];
    foreach ($films as $film) {
        foreach ($film['genres'] as $g) {
            if (stripos($g, $genre) !== false) {
                $result[] = $film;
                break;
            }
        }
    }
    return array_slice($result, $offset, $limit);
}

function getFilmByName($name) {
    $films = loadFilms();
    foreach ($films as $film) {
        if (strtolower($film['name']) === strtolower($name)) {
            return $film;
        }
    }
    return null;
}

function getShowByName($name) {
    $shows = loadShows();
    if (isset($shows[$name])) {
        $data = $shows[$name];
        $seasons = [];
        foreach ($data['episodes'] as $ep) {
            $s = $ep['season'];
            if (!isset($seasons[$s])) $seasons[$s] = [];
            $seasons[$s][] = $ep;
        }
        ksort($seasons);
        return [
            'name' => $name,
            'logo' => $data['logo'],
            'seasons' => $seasons
        ];
    }
    return null;
}

function searchContent($query, $limit = 30) {
    $query = strtolower(trim($query));
    if (strlen($query) < 2) return [];
    
    $results = [];
    
    // Search films
    $films = loadFilms();
    foreach ($films as $film) {
        if (stripos($film['name'], $query) !== false) {
            $results[] = [
                'name' => $film['name'],
                'logo' => $film['logo'],
                'type' => 'film',
                'genres' => $film['genres'],
                'versions' => array_unique(array_column($film['versions'], 'audio'))
            ];
        }
    }
    
    // Search shows
    $shows = loadShows();
    foreach ($shows as $name => $data) {
        if (stripos($name, $query) !== false) {
            $seasons = array_unique(array_column($data['episodes'], 'season'));
            $results[] = [
                'name' => $name,
                'logo' => $data['logo'],
                'type' => 'dizi',
                'season_count' => count($seasons),
                'episode_count' => count($data['episodes'])
            ];
        }
    }
    
    return array_slice($results, 0, $limit);
}

function getAllFilmsPaginated($page = 1, $perPage = 48, $genre = '', $audio = '', $sort = 'default') {
    $films = loadFilms();
    
    if ($genre) {
        $films = array_filter($films, function($f) use ($genre) {
            foreach ($f['genres'] as $g) {
                if (stripos($g, $genre) !== false) return true;
            }
            return false;
        });
        $films = array_values($films);
    }
    
    if ($audio) {
        $films = array_filter($films, function($f) use ($audio) {
            foreach ($f['versions'] as $v) {
                if (stripos($v['audio'], $audio) !== false) return true;
            }
            return false;
        });
        $films = array_values($films);
    }
    
    if ($sort === 'az') {
        usort($films, fn($a, $b) => strcmp($a['name'], $b['name']));
    } elseif ($sort === 'za') {
        usort($films, fn($a, $b) => strcmp($b['name'], $a['name']));
    }
    
    $total = count($films);
    $offset = ($page - 1) * $perPage;
    return [
        'items' => array_slice($films, $offset, $perPage),
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}

function getAllShowsPaginated($page = 1, $perPage = 48) {
    $shows = loadShows();
    $result = [];
    foreach ($shows as $name => $data) {
        $seasons = array_unique(array_column($data['episodes'], 'season'));
        $result[] = [
            'name' => $name,
            'logo' => $data['logo'],
            'episode_count' => count($data['episodes']),
            'season_count' => count($seasons)
        ];
    }
    $total = count($result);
    $offset = ($page - 1) * $perPage;
    return [
        'items' => array_slice($result, $offset, $perPage),
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}

function getSimilarFilms($film, $limit = 12) {
    if (empty($film['genres'])) return [];
    $films = loadFilms();
    $scores = [];
    foreach ($films as $f) {
        if ($f['name'] === $film['name']) continue;
        $score = count(array_intersect($f['genres'], $film['genres']));
        if ($score > 0) {
            $scores[] = ['film' => $f, 'score' => $score];
        }
    }
    usort($scores, fn($a, $b) => $b['score'] - $a['score']);
    return array_column(array_slice($scores, 0, $limit), 'film');
}
