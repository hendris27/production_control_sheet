# Production Setup Guide - Report Path Configuration

## Network Setup

- **aaPanel Server**: 192.168.62.38 (Web application)
- **Share Server**: 192.168.62.12 (File storage)
- **Share Folder**: `14 Prod-02`
- **Report Path**: `PROD\REPORT PCS`

## UNC Path

```
\\192.168.62.12\14 Prod-02\PROD\REPORT PCS
```

## Configuration Files

### .env (Environment Variables)

For **localhost XAMPP**:

```ini
REPORT_PREFERRED_ROOT=Z:\PROD\REPORT PCS
REPORT_FORCE_PREFERRED=true
REPORT_FALLBACK_SUBDIR=reports
```

For **aaPanel Production Server** (192.168.62.38):

```ini
REPORT_PREFERRED_ROOT=\\192.168.62.12\14 Prod-02\PROD\REPORT PCS
REPORT_FORCE_PREFERRED=true
REPORT_FALLBACK_SUBDIR=reports
```

### config/report.php

```php
'preferred_root' => env('REPORT_PREFERRED_ROOT', 'Z:\\PROD\\REPORT PCS'),
'force_preferred' => env('REPORT_FORCE_PREFERRED', true),
'fallback_subdir' => env('REPORT_FALLBACK_SUBDIR', 'reports'),
```

## Features

✅ **Primary Save**: Files saved to configured preferred path
✅ **Auto Backup**: Files automatically backed up to `storage/app/reports`
✅ **Fallback**: If primary path fails, automatically uses local storage
✅ **Logging**: All operations logged to `storage/logs/laravel.log`

## Verification

### Test UNC Path Accessibility

```php
php -r "echo is_dir('\\\\192.168.62.12\\14 Prod-02\\PROD\\REPORT PCS') ? 'OK' : 'FAIL';"
```

### Check Backup Files

```bash
php artisan tinker
>>> glob(storage_path('app/reports/**/*.csv'))
>>> glob(storage_path('app/reports/**/*.xlsx'))
>>> glob(storage_path('app/reports/**/*.pdf'))
```

## Subdirectory Structure

```
\\192.168.62.12\14 Prod-02\PROD\REPORT PCS\
├── csv\
│   ├── januari\
│   ├── februari\
│   └── maret\
├── excel\
│   ├── january\
│   ├── februari\
│   └── maret\
└── pdf\
    ├── january\
    ├── februari\
    └── maret\
        └── IC\
            └── 2026-03-05\
```

## When Uploading to aaPanel

1. Copy `.env` and update `REPORT_PREFERRED_ROOT` to UNC path
2. Ensure aaPanel server can access network share
3. Create directories if needed: `/mnt/production_control_sheet/storage/app/reports`
4. Test with `php artisan route:list` to verify routes
5. Check logs at: `storage/logs/laravel.log`

## Troubleshooting

If files not saving:

1. Check `storage/logs/laravel.log` for detailed errors
2. Verify network connectivity to 192.168.62.12
3. Test UNC path manually from server terminal
4. Ensure fallback path `storage/app/reports` has write permissions
5. Check firewall rules between servers

Last Updated: 2026-03-05
