<?php
/**
 * Actualiza o agrega claves en un archivo .env.
 *
 * Uso:
 *   php scripts/set-env.php .env "APP_URL=http://ejemplo.test" "DB_DATABASE=lapt_core"
 */
$file = $argv[1] ?? '.env';
$pairs = array_slice($argv, 2);

if (!is_file($file)) {
    fwrite(STDERR, "No existe $file\n");
    exit(1);
}

$lines = file($file, FILE_IGNORE_NEW_LINES);
$index = [];

foreach ($lines as $i => $line) {
    if (preg_match('/^\s*([A-Za-z0-9_]+)\s*=/', $line, $m)) {
        $index[$m[1]] = $i;
    }
}

foreach ($pairs as $pair) {
    $parts = explode('=', $pair, 2);
    $key = trim($parts[0]);
    $value = $parts[1] ?? '';

    if ($key === '') {
        continue;
    }

    // Laravel rechaza valores sin comillas que contienen espacios.
    if (preg_match('/[\s"#]/', $value) === 1) {
        $yaCitado = strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"';
        if (! $yaCitado) {
            $value = '"' . str_replace('"', '\"', $value) . '"';
        }
    }

    if (isset($index[$key])) {
        $lines[$index[$key]] = $key . '=' . $value;
    } else {
        $lines[] = $key . '=' . $value;
        $index[$key] = count($lines) - 1;
    }
}

file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);
echo "OK $file\n";
