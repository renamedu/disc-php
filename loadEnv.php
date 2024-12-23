<?php

function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        throw new Exception("Файл .env не найден: " . $filePath);
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);

        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

