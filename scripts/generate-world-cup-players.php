<?php

/**
 * Regenerate app/Data/world-cup-2026-players.php from app/Data/updated-player-data.json.
 * Run: php scripts/generate-world-cup-players.php
 */

use App\Support\WorldCupPlayerImport;

require __DIR__.'/../vendor/autoload.php';

$inputPath = __DIR__.'/../app/Data/updated-player-data.json';
$outputPath = __DIR__.'/../app/Data/world-cup-2026-players.php';

try {
    $squads = WorldCupPlayerImport::squadsFromJsonFile($inputPath);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
}

$output = "<?php\n\n/**\n * World Cup 2026 player squads (code-maintained, not DB).\n */\nreturn [\n";

foreach ($squads as $key => $squad) {
    $output .= "    '{$key}' => [\n";
    $output .= "        'country' => ".var_export($squad['country'], true).",\n";
    $output .= "        'players' => [\n";

    foreach ($squad['players'] as $player) {
        $output .= '            ['
            ."'name' => ".var_export($player['name'], true).', '
            ."'position' => ".var_export($player['position'], true).', '
            ."'club' => ".var_export($player['club'], true)."],\n";
    }

    $output .= "        ],\n";
    $output .= "    ],\n";
}

$output .= "];\n";

file_put_contents($outputPath, $output);

$playerCount = 0;

foreach ($squads as $squad) {
    $playerCount += count($squad['players']);
}

echo 'Generated '.$playerCount.' players across '.count($squads)." squads.\n";
