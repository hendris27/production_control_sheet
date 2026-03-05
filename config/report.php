<?php

return [
    // Preferred root path for generated reports.
    // Can be a local path (Z:\...) or UNC path (\\server\share)
    // For localhost XAMPP: Z:\\PROD\\REPORT PCS
    // For aaPanel production (192.168.62.38): \\192.168.62.12\14 Prod-02\PROD\REPORT PCS
    'preferred_root' => env('REPORT_PREFERRED_ROOT', 'Z:\\PROD\\REPORT PCS'),

    // When true, attempt to use preferred_root even if it doesn't exist.
    // Will try to create the directory structure.
    'force_preferred' => env('REPORT_FORCE_PREFERRED', true),

    // Fallback local storage path (relative to storage_path('app')).
    'fallback_subdir' => env('REPORT_FALLBACK_SUBDIR', 'reports'),
];
