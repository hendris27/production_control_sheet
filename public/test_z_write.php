<?php
// Simple web-accessible test to check if the webserver process can write to Z: drive
header('Content-Type: text/plain; charset=utf-8');
$path = 'Z:' . DIRECTORY_SEPARATOR . 'PROD' . DIRECTORY_SEPARATOR . 'REPORT PCS';
$testFile = $path . DIRECTORY_SEPARATOR . 'web-write-test-' . uniqid() . '.txt';

echo "Testing webserver write to: $path\n\n";

$dirExists = @is_dir($path);
$dirWritable = @is_writable($path);

echo "is_dir: " . ($dirExists ? 'YES' : 'NO') . "\n";
echo "is_writable: " . ($dirWritable ? 'YES' : 'NO') . "\n\n";

if (! $dirExists) {
    echo "Attempting to create directory...\n";
    try {
        @mkdir($path, 0777, true);
        echo "mkdir result: " . (is_dir($path) ? 'OK' : 'FAILED') . "\n";
    } catch (Throwable $e) {
        echo "mkdir exception: " . $e->getMessage() . "\n";
    }
}

echo "Attempting to write test file: $testFile\n";
$res = @file_put_contents($testFile, "web test " . date('c'));
if ($res !== false) {
    echo "Write OK (bytes: $res)\n";
    // Optionally remove the file
    @unlink($testFile);
} else {
    echo "Write FAILED\n";
}

// Also echo PHP user and effective UID
echo "\nServer API: " . php_sapi_name() . "\n";
if (function_exists('posix_getpwuid')) {
    $uid = posix_geteuid();
    $pw = posix_getpwuid($uid);
    echo "UID: $uid\n";
    echo "User: " . ($pw['name'] ?? '') . "\n";
} else {
    echo "posix functions not available on Windows.\n";
}
