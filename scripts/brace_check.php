<?php
$f = __DIR__ . '/../app/Jobs/GenerateProductionReports.php';
$h = fopen($f, 'r');
$ln = 0; $depth = 0;
while (!feof($h)) {
    $ln++; $line = fgets($h);
    $depth += substr_count($line, '{') - substr_count($line, '}');
    echo sprintf("%04d d=%d %s", $ln, $depth, rtrim($line)) . PHP_EOL;
}
fclose($h);
echo "Final depth: $depth\n";
