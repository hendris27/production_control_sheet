<?php

return [
    // Preferred root path for generated reports. Set to a UNC path if the web
    // service account cannot access a mapped drive letter.
    // Updated default to shared production reports folder
    'preferred_root' => env('REPORT_PREFERRED_ROOT', 'Z:\\PROD\\REPORT PCS'),

    // When true, attempt to use preferred_root only if it exists and is writable.
    // When false, always use fallback storage unless preferred is explicitly
    // present and writable.
    'force_preferred' => env('REPORT_FORCE_PREFERRED', false),

    // Fallback local storage path (relative to storage_path('app')).
    'fallback_subdir' => env('REPORT_FALLBACK_SUBDIR', 'reports'),
    // Preferred root for Excel/CSV exports
    'excel_root' => env('REPORT_EXCEL_ROOT', 'Z:\\PROD\\REPORT PCS EXCEL'),
];
