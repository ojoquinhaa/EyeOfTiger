<?php
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

function initializeFiles() {
    if (!file_exists('.env')) {
        copy('.env.example', '.env');
    }

    if (!is_dir('data')) {
        mkdir('data', 0777, true);
    }

    $files = ['data/informations.txt', 'data/blacklist.txt', 'data/redirect.txt'];
    foreach ($files as $file) {
        if (!file_exists($file)) {
            touch($file);
        }
    }
}

initializeFiles();
loadEnv(__DIR__ . '/.env');
?>