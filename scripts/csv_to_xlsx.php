<?php
// scripts/csv_to_xlsx.php
// Usage: php scripts/csv_to_xlsx.php [source_dir] [target_dir]
// If no args provided, defaults to storage/app/reports/excel and will write .xlsx next to CSV files.

// This script no longer requires OpenSpout. It will create an HTML-based .xls file
// (Excel can open HTML tables) to ensure an Excel file is always produced.
ini_set('display_errors', 1);
error_reporting(E_ALL);

$cwd = realpath(__DIR__ . '/..');

$defaultSource = $cwd . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'excel';
$sourceDir = $argv[1] ?? $defaultSource;
$targetDirArg = $argv[2] ?? null;

// Determine preferred root for saving excel files (try mounted share first)
$preferredRoots = [
    '/mnt/report_pcs', // typical Linux mount for Z: share
    $cwd . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'reports', // fallback public reports
];

// If running on Windows, prefer mapped Z: drive path
if (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
    $preferredRoots = array_merge([
        'Z:\\PROD\\REPORT PCS',
        'Z:\\',
    ], $preferredRoots);
}

// (PDF copy will run after CSV conversion once $pdfFiles is collected)

// Determine target root. Priority: explicit arg -> Windows Z: default -> writable preferred root -> source parent
$targetRoot = null;
if (! empty($targetDirArg)) {
    $targetRoot = $targetDirArg;
} else {
    if (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
        $targetRoot = 'Z:\\PROD\\REPORT PCS';
    } else {
        foreach ($preferredRoots as $pr) {
            if (is_dir($pr) && is_writable($pr)) { $targetRoot = $pr; break; }
        }
        if ($targetRoot === null) { $targetRoot = dirname($sourceDir); }
    }
}

echo "Using target root: {$targetRoot}\n";

// Try to create the target root if it doesn't exist (helps when Z: mapped but subfolder missing)
if (! is_dir($targetRoot)) {
    if (@mkdir($targetRoot, 0777, true)) {
        echo "Created target root: {$targetRoot}\n";
    } else {
        echo "Warning: cannot create or access target root: {$targetRoot}.\n";
        echo "Ensure the drive is mapped and the PHP user has write permissions.\n";
    }
}

if (!is_dir($sourceDir)) {
    echo "Source directory does not exist: {$sourceDir}\n";
    exit(1);
}

$csvFiles = (function($dir){
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($it as $f) {
        if (! $f->isFile()) continue;
        if (strtolower($f->getExtension()) === 'csv') {
            $files[] = $f->getPathname();
        }
    }
    return $files;
})($sourceDir);

// Also collect any PDF files to copy to the pdf folder
$pdfFiles = (function($dir){
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($it as $f) {
        if (! $f->isFile()) continue;
        if (strtolower($f->getExtension()) === 'pdf') {
            $files[] = $f->getPathname();
        }
    }
    return $files;
})($sourceDir);

if (empty($csvFiles)) {
    echo "No CSV files found under {$sourceDir}\n";
    exit(0);
}

echo "Found " . count($csvFiles) . " CSV file(s)\n";

foreach ($csvFiles as $csv) {
    // Normalize paths to compute relative path reliably on Windows/Linux
    $normSource = str_replace(['\\','/'], '/', rtrim($sourceDir, "\\/"));
    $normCsv = str_replace(['\\','/'], '/', $csv);
    $relPath = ltrim(substr($normCsv, strlen($normSource)), '/');
    $relDir = dirname($relPath);
    if ($relDir === '.') { $relDir = ''; }
    // Build target subdirectories under targetRoot for csv and excel mirroring source structure
    $csvBase = rtrim($targetRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'csv';
    $excelBase = rtrim($targetRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'excel';
    $relDirDs = $relDir !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relDir) : '';
    $csvSubDir = $csvBase . $relDirDs;
    $excelSubDir = $excelBase . $relDirDs;

    if (! is_dir($csvSubDir)) {
        if (! @mkdir($csvSubDir, 0777, true)) {
            echo "  Warning: failed to create CSV target dir: {$csvSubDir}\n";
        }
    }
    if (! is_dir($excelSubDir)) {
        if (! @mkdir($excelSubDir, 0777, true)) {
            echo "  Warning: failed to create Excel target dir: {$excelSubDir}\n";
        }
    }

    // Debug info about target dirs
    echo "  CSV target dir: {$csvSubDir} (exists=" . (is_dir($csvSubDir) ? 'yes' : 'no') . ", writable=" . (is_writable($csvSubDir) ? 'yes' : 'no') . ")\n";
    echo "  Excel target dir: {$excelSubDir} (exists=" . (is_dir($excelSubDir) ? 'yes' : 'no') . ", writable=" . (is_writable($excelSubDir) ? 'yes' : 'no') . ")\n";

    $xlsxPath = $excelSubDir . DIRECTORY_SEPARATOR . pathinfo($csv, PATHINFO_FILENAME) . '.xls';
    $csvCopyPath = $csvSubDir . DIRECTORY_SEPARATOR . pathinfo($csv, PATHINFO_BASENAME);

    echo "Converting: {$csv} -> {$xlsxPath} (and copy CSV -> {$csvCopyPath})\n";

    // Copy original CSV to target folder (report errors)
    if (! @copy($csv, $csvCopyPath)) {
        $err = error_get_last();
        echo "  Failed to copy CSV to {$csvCopyPath}. error=" . ($err['message'] ?? 'unknown') . "\n";
    } else {
        clearstatcache(true, $csvCopyPath);
        $size = @filesize($csvCopyPath);
        echo "  CSV copied: {$csvCopyPath} (size=" . ($size !== false ? $size : 'unknown') . ")\n";
    }

    // Create simple HTML-based .xls file (Excel can open HTML tables)
    try {
        if (($h = fopen($csv, 'r')) === false) {
            echo "  Failed to open CSV: {$csv}\n";
            continue;
        }
        $out = [];
        $out[] = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/></head><body><table border=\"1\">";
        while (($row = fgetcsv($h, 0, ',', '"', "\\")) !== false) {
            $out[] = '<tr>' . implode('', array_map(function($c){
                $cell = htmlspecialchars((string)$c, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                return '<td>' . $cell . '</td>';
            }, $row)) . '</tr>';
        }
        $out[] = '</table></body></html>';
        fclose($h);
        $written = @file_put_contents($xlsxPath, implode("\n", $out));
        if ($written === false) {
            $err = error_get_last();
            echo "  Failed to write Excel file {$xlsxPath}. error=" . ($err['message'] ?? 'unknown') . "\n";
        } else {
            clearstatcache(true, $xlsxPath);
            $xsize = @filesize($xlsxPath);
            echo "  Excel written: {$xlsxPath} (bytes=" . ($xsize !== false ? $xsize : $written) . ")\n";
        }
    } catch (\Throwable $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
