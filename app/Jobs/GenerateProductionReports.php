<?php

namespace App\Jobs;

use App\Models\ProductionControlShift1;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProductionReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $ids;

    /**
     * Create a new job instance.
     *
     * @param array|string $ids
     */
    public function __construct($ids)
    {
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        $this->ids = (array) $ids;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (empty($this->ids)) {
            return;
        }

        $records = ProductionControlShift1::whereIn('id', $this->ids)->get();
        if ($records->isEmpty()) {
            return;
        }

        // Ambil konfigurasi lokasi laporan dan fallback lokal
        // preferred_root: target (Z:\PROD\REPORT PCS - mapped drive)
        // fallback_subdir: subfolder di storage/app jika preferred tidak tersedia
        $preferredRoot = 'Z:' . DIRECTORY_SEPARATOR . 'PROD' . DIRECTORY_SEPARATOR . 'REPORT PCS';
        // aaPanel default path Anda (jika ingin hardcoded) dan juga dapat di-override via .env AAPANEL_REPORTS_DIR
        $aapanelRoot = env('AAPANEL_REPORTS_DIR', '/www/wwwroot/production_control_sheet/storage/app/reports');
        // normalize preferredRoot / aapanelRoot separators
        $preferredRoot = $preferredRoot ? rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $preferredRoot), DIRECTORY_SEPARATOR) : null;
        $aapanelRoot = $aapanelRoot ? rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $aapanelRoot), DIRECTORY_SEPARATOR) : null;
        // sub-roots inside AAPANEL_REPORTS_DIR
        $aapanelPdfRoot = $aapanelRoot ? $aapanelRoot . DIRECTORY_SEPARATOR . 'pdf' : null;
        $aapanelExcelRoot = $aapanelRoot ? $aapanelRoot . DIRECTORY_SEPARATOR . 'excel' : null;
        $aapanelCsvRoot = $aapanelRoot ? $aapanelRoot . DIRECTORY_SEPARATOR . 'csv' : null;
        $fallbackRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports'));
        $fallbackRoot = rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $fallbackRoot), DIRECTORY_SEPARATOR);

        // Helper untuk test write capability dengan membuat temporary file
        $testWritable = function (string $dir) {
            try {
                // Normalize path dengan backslash untuk Windows UNC
                if (strpos($dir, '\\192') === 0 || strpos($dir, '\\\\') === 0) {
                    // UNC path - gunakan backslash
                    $testDir = str_replace('/', '\\', $dir);
                } else {
                    // Mapped drive atau local - normalize dengan DIRECTORY_SEPARATOR
                    $testDir = rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $dir), DIRECTORY_SEPARATOR);
                }

                \Illuminate\Support\Facades\Log::debug('GenerateProductionReports: testing write to', ['dir' => $testDir]);

                if (! @is_dir($testDir)) {
                    @mkdir($testDir, 0777, true);
                    \Illuminate\Support\Facades\Log::debug('GenerateProductionReports: created directory', ['dir' => $testDir]);
                }

                $testFile = $testDir . DIRECTORY_SEPARATOR . '.write-test-' . uniqid();
                $res = @file_put_contents($testFile, 'test');
                if ($res === false) {
                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: write test failed', ['dir' => $testDir, 'testFile' => $testFile]);
                    return false;
                }

                @unlink($testFile);
                \Illuminate\Support\Facades\Log::info('GenerateProductionReports: write test succeeded', ['dir' => $testDir]);
                return true;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: write test exception', ['dir' => $dir, 'error' => $e->getMessage()]);
                return false;
            }
        };

        // Helper to ensure a directory exists and attempt to set permissive permissions
        $ensureDir = function (string $dir) {
            try {
                $dir = rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $dir), DIRECTORY_SEPARATOR);
                if (! is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                // Try to set permissive mode where possible (no-op on some systems)
                try { @chmod($dir, 0777); } catch (\Throwable $__e) { }
                return is_dir($dir);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: ensureDir exception', ['dir' => $dir, 'error' => $e->getMessage()]);
                return false;
            }
        };

        // Helper to attempt copying a file with retries and small delays
        $verifyAndRetryCopy = function (string $source, string $dest, int $attempts = 3, int $delayMs = 500) {
            try {
                for ($i = 0; $i < $attempts; $i++) {
                    if (file_exists($dest) && filesize($dest) > 0) {
                        return true;
                    }

                    if (! is_dir(dirname($dest))) { @mkdir(dirname($dest), 0777, true); }
                    if (file_exists($source) && filesize($source) > 0) {
                        @copy($source, $dest);
                        clearstatcache(true, $dest);
                        if (file_exists($dest) && filesize($dest) > 0) {
                            return true;
                        }
                    }

                    // sleep for a bit before retrying
                    usleep($delayMs * 1000);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: verifyAndRetryCopy exception', ['source' => $source, 'dest' => $dest, 'error' => $e->getMessage()]);
            }
            return false;
        };

        // Attempt preferred roots dalam urutan: aaPanel path, Z: drive, UNC path, fallback ke storage lokal
        $candidatePreferredRoots = [];

        // Candidate 0: aaPanel pdf path (direkomendasikan oleh user)
        if (! empty($aapanelPdfRoot)) {
            $candidatePreferredRoots[] = [
                'path' => $aapanelPdfRoot,
                'name' => 'aaPanel pdf folder (AAPANEL_REPORTS_DIR/pdf)',
            ];
        }

        // Candidate 1: Z: mapped drive
        if (! empty($preferredRoot)) {
            $candidatePreferredRoots[] = [
                'path' => $preferredRoot,
                'name' => 'Z: mapped drive',
            ];
        }

        // Candidate 2: UNC path (\\192.168.62.12\14 Prod-02\PROD\REPORT PCS)
        $uncPath = '\\192.168.62.12\\14 Prod-02\\PROD\\REPORT PCS';
        $candidatePreferredRoots[] = [
            'path' => $uncPath,
            'name' => 'UNC network share',
        ];

        $saveRoot = null;

        // Try each candidate
        foreach ($candidatePreferredRoots as $candidate) {
            $cand = $candidate['path'];
            $candName = $candidate['name'];

            if (empty($cand)) { continue; }

            if ($testWritable($cand)) {
                $saveRoot = $cand;
                \Illuminate\Support\Facades\Log::info('GenerateProductionReports: berhasil memilih preferred report root', ['path' => $saveRoot, 'type' => $candName]);
                break;
            } else {
                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: candidate root tidak writable, skip', ['candidate' => $cand, 'type' => $candName]);
            }
        }

        // If no preferred root works, use local fallback
        if (empty($saveRoot) || $saveRoot === null) {
            $saveRoot = $fallbackRoot;
            \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: tidak ada preferred root yang tersedia, gunakan local storage fallback', ['fallback' => $fallbackRoot]);
        }

        // Nama bulan Bahasa Indonesia untuk membuat folder per-bulan berdasarkan tanggal record
        $monthNames = [1=>'januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];

        \Illuminate\Support\Facades\Log::info('GenerateProductionReports: starting job', ['ids' => $this->ids]);

        $files = [];
        // Collect rows per month for CSV/Excel export
        $excelGroups = [];

        foreach ($records as $rec) {

            // Initialize per-record variables used below
            $recordDate = ! empty($rec->date) ? Carbon::parse($rec->date) : now();
            $dateStr = $recordDate->format('Y-m-d');
            $customerName = $rec->customer_name ?? (isset($rec->customer) && isset($rec->customer->name) ? $rec->customer->name : 'unknown');
            $safeCustomer = preg_replace('/[^A-Za-z0-9\-\._]+/', '-', $customerName);
            $safeCustomer = trim($safeCustomer, '-._');
            // default filename base as requested: shift_-line-nama model
            $shiftPart = $rec->select_shift ?? $rec->shift ?? '';
            $linePart = $rec->line ?? '';
            $modelPart = $rec->model ?? '';
            $filenameBase = 'shift_' . ($shiftPart !== '' ? $shiftPart : 'unknown') . '-line_' . ($linePart !== '' ? $linePart : 'unknown') . '-' . ($modelPart !== '' ? $modelPart : 'unknown');
            // dataset for view rendering
            $data = is_array($rec) ? $rec : (method_exists($rec, 'toArray') ? $rec->toArray() : (array)$rec);

            // sanitize filename (allow letters, numbers, dash, underscore, dot)
            $filenameBase = preg_replace('/[^A-Za-z0-9\-\._]+/', '-', $filenameBase);
            $filenameBase = trim($filenameBase, '-._');

            $monthName = $monthNames[(int) $recordDate->format('n')];

            // Build PDF directory with proper path handling for Windows UNC/mapped drives
            // store PDFs under a top-level 'pdf' folder: preferred_root/pdf/<bulan>/<customer>/<tanggal>
            $rootForPdf = (strtolower(basename($saveRoot)) === 'pdf') ? $saveRoot : $saveRoot . DIRECTORY_SEPARATOR . 'pdf';
            $pdfDir = $rootForPdf . DIRECTORY_SEPARATOR . $monthName . DIRECTORY_SEPARATOR . $safeCustomer . DIRECTORY_SEPARATOR . $dateStr;
            if (! is_dir($pdfDir)) {
                $mkdirRes = $ensureDir($pdfDir);
                if (! $mkdirRes) {
                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to create PDF directory', ['dir' => $pdfDir, 'id' => $rec->id]);
                }
            }

            // Only write PDF files into the date folder (no HTML fallback saved there)
            if (app()->bound('dompdf.wrapper')) {
                try {
                    $pdf = app('dompdf.wrapper')->loadView('pdf.production_control', $data);
                    $content = $pdf->output();
                    $path = $pdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';

                    $written = false;
                    try {
                        $res = @file_put_contents($path, $content);
                        $written = $res !== false;
                        if ($written) {
                            \Illuminate\Support\Facades\Log::info('GenerateProductionReports: PDF berhasil ditulis', ['path' => $path, 'id' => $rec->id, 'bytes' => $res]);
                        }
                    } catch (\Throwable $e) {
                        $written = false;
                        \Illuminate\Support\Facades\Log::error('GenerateProductionReports: exception saat write PDF', ['path' => $path, 'id' => $rec->id, 'error' => $e->getMessage()]);
                    }

                    // If write failed and we're not already on the local fallback, try storage fallback
                    if (! $written && $saveRoot !== $fallbackRoot) {
                        try {
                            $fallbackPdfDir = $fallbackRoot . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $monthName . DIRECTORY_SEPARATOR . $safeCustomer . DIRECTORY_SEPARATOR . $dateStr;
                            if (! is_dir($fallbackPdfDir)) { $ensureDir($fallbackPdfDir); }
                            $fallbackPath = $fallbackPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                            $res2 = @file_put_contents($fallbackPath, $content);
                            if ($res2 !== false) {
                                $files[] = $fallbackPath;
                                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: pdf write to preferred path failed, wrote to fallback', ['preferred' => $path, 'fallback' => $fallbackPath, 'id' => $rec->id]);

                                $rec->downloaded_at = now();
                                $rec->save();
                                continue;
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('GenerateProductionReports: pdf fallback write error', ['id' => $rec->id, 'error' => $e->getMessage()]);
                        }
                    }

                    if ($written) {
                        $files[] = $path;
                        \Illuminate\Support\Facades\Log::info('GenerateProductionReports: pdf written', ['path' => $path, 'id' => $rec->id]);

                        // Also ensure a local copy exists under storage fallback for easy access
                        try {
                            $localPdfDir = $fallbackRoot . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $monthName . DIRECTORY_SEPARATOR . $safeCustomer . DIRECTORY_SEPARATOR . $dateStr;
                            if (! is_dir($localPdfDir)) { $ensureDir($localPdfDir); }
                            $localPath = $localPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                            @file_put_contents($localPath, $content);
                            \Illuminate\Support\Facades\Log::info('GenerateProductionReports: pdf also written to local fallback', ['path' => $localPath, 'id' => $rec->id]);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to write local copy of pdf', ['id' => $rec->id, 'error' => $e->getMessage()]);
                        }

                        // Also copy to public/reports for easy browser access
                        try {
                            $publicPdfDir = public_path('reports' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $monthName . DIRECTORY_SEPARATOR . $safeCustomer . DIRECTORY_SEPARATOR . $dateStr);
                            if (! is_dir($publicPdfDir)) { $ensureDir($publicPdfDir); }
                            $publicPath = $publicPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                            @file_put_contents($publicPath, $content);
                            \Illuminate\Support\Facades\Log::info('GenerateProductionReports: pdf also written to public folder', ['path' => $publicPath, 'id' => $rec->id]);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to write pdf to public folder', ['id' => $rec->id, 'error' => $e->getMessage()]);
                        }

                        // If the preferred path isn't visible after write, try copying the local fallback to preferred (retries)
                        try {
                            if ((!file_exists($path) || filesize($path) === 0) && isset($localPath) && file_exists($localPath) && filesize($localPath) > 0) {
                                $copied = $verifyAndRetryCopy($localPath, $path, 3, 500);
                                if ($copied) {
                                    \Illuminate\Support\Facades\Log::info('GenerateProductionReports: restored preferred pdf by copying local -> preferred', ['preferred' => $path, 'local' => $localPath, 'id' => $rec->id]);
                                } else {
                                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to restore preferred pdf from local copy', ['preferred' => $path, 'local' => $localPath, 'id' => $rec->id]);
                                }
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: exception while attempting to restore preferred pdf', ['id' => $rec->id, 'error' => $e->getMessage()]);
                        }

                        $rec->downloaded_at = now();
                        $rec->save();

                        continue;
                    }

                    \Illuminate\Support\Facades\Log::error('GenerateProductionReports: pdf generation or write failed (no fallback succeeded)', ['id' => $rec->id, 'path' => $path]);
                    continue;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('GenerateProductionReports: pdf generation failed', ['id' => $rec->id, 'error' => $e->getMessage()]);
                    continue;
                }
            } else {
                // Try to fall back to using the underlying Dompdf class if available.
                try {
                    if (class_exists('\\Dompdf\\Dompdf')) {
                        $html = view('pdf.production_control', $data)->render();
                        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
                        $dompdf->loadHtml($html);
                        $dompdf->setPaper('A4', 'landscape');
                        $dompdf->render();
                        $content = $dompdf->output();

                        $path = $pdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                        $written2 = false;
                        try {
                            $res = @file_put_contents($path, $content);
                            $written2 = $res !== false;
                        } catch (\Throwable $e) {
                            $written2 = false;
                        }

                        if (! $written2 && $saveRoot !== $fallbackRoot) {
                            try {
                                $fallbackPdfDir = $fallbackRoot . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $monthName . DIRECTORY_SEPARATOR . $safeCustomer . DIRECTORY_SEPARATOR . $dateStr;
                                if (! is_dir($fallbackPdfDir)) { $ensureDir($fallbackPdfDir); }
                                $fallbackPath = $fallbackPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                                $res2 = @file_put_contents($fallbackPath, $content);
                                if ($res2 !== false) {
                                    $files[] = $fallbackPath;
                                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: pdf write to preferred path failed, wrote to fallback (dompdf class)', ['preferred' => $path, 'fallback' => $fallbackPath, 'id' => $rec->id]);

                                    $rec->downloaded_at = now();
                                    $rec->save();
                                    continue;
                                }
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::error('GenerateProductionReports: pdf fallback write error (dompdf class)', ['id' => $rec->id, 'error' => $e->getMessage()]);
                            }
                        }

                        if ($written2) {
                            $files[] = $path;
                            \Illuminate\Support\Facades\Log::info('GenerateProductionReports: pdf written (dompdf class)', ['path' => $path, 'id' => $rec->id]);

                            // local copy
                            try {
                                $localPdfDir = $fallbackRoot . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $monthName . DIRECTORY_SEPARATOR . $safeCustomer . DIRECTORY_SEPARATOR . $dateStr;
                                if (! is_dir($localPdfDir)) { $ensureDir($localPdfDir); }
                                $localPath = $localPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                                @file_put_contents($localPath, $content);
                                \Illuminate\Support\Facades\Log::info('GenerateProductionReports: pdf also written to local fallback (dompdf class)', ['path' => $localPath, 'id' => $rec->id]);
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to write local copy of pdf (dompdf class)', ['id' => $rec->id, 'error' => $e->getMessage()]);
                            }

                            // Also copy to public/reports for easy browser access (dompdf class)
                            try {
                                $publicPdfDir = public_path('reports' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $monthName . DIRECTORY_SEPARATOR . $safeCustomer . DIRECTORY_SEPARATOR . $dateStr);
                                if (! is_dir($publicPdfDir)) { $ensureDir($publicPdfDir); }
                                $publicPath = $publicPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                                @file_put_contents($publicPath, $content);
                                \Illuminate\Support\Facades\Log::info('GenerateProductionReports: pdf also written to public folder (dompdf class)', ['path' => $publicPath, 'id' => $rec->id]);
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to write pdf to public folder (dompdf class)', ['id' => $rec->id, 'error' => $e->getMessage()]);
                            }

                            // If the preferred path isn't visible after write, try copying the local fallback to preferred (retries)
                            try {
                                if ((!file_exists($path) || filesize($path) === 0) && isset($localPath) && file_exists($localPath) && filesize($localPath) > 0) {
                                    $copied = $verifyAndRetryCopy($localPath, $path, 3, 500);
                                    if ($copied) {
                                        \Illuminate\Support\Facades\Log::info('GenerateProductionReports: restored preferred pdf by copying local -> preferred (dompdf class)', ['preferred' => $path, 'local' => $localPath, 'id' => $rec->id]);
                                    } else {
                                        \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to restore preferred pdf from local copy (dompdf class)', ['preferred' => $path, 'local' => $localPath, 'id' => $rec->id]);
                                    }
                                }
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: exception while attempting to restore preferred pdf (dompdf class)', ['id' => $rec->id, 'error' => $e->getMessage()]);
                            }

                            $rec->downloaded_at = now();
                            $rec->save();

                            continue;
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('GenerateProductionReports: dompdf class fallback failed', ['id' => $rec->id, 'error' => $e->getMessage()]);
                }

                \Illuminate\Support\Facades\Log::error('GenerateProductionReports: dompdf.wrapper not bound and dompdf class unavailable, cannot create pdf', ['id' => $rec->id]);
                continue;
            }
        }

        // Build groups keyed by year_month to avoid mixing different years
        foreach ($records as $rec) {
            $recordDate = ! empty($rec->date) ? Carbon::parse($rec->date) : now();
            $monthKey = $recordDate->format('Y_m'); // e.g. 2026_01
            $monthName = strtolower($monthNames[(int) $recordDate->format('n')]);
            $year = $recordDate->format('Y');

            $row = [
                // Meta
                'id' => $rec->id,
                'date' => ! empty($rec->date) ? Carbon::parse($rec->date)->format('Y-m-d') : '',
                'customer_name' => $rec->customer_name ?? (isset($rec->customer) && isset($rec->customer->name) ? $rec->customer->name : ''),
                'line' => $rec->line ?? '',
                'model' => $rec->model ?? '',
                'dj_number' => $rec->dj_number ?? '',
                'select_shift' => $rec->select_shift ?? $rec->shift ?? '',
                'select_group' => $rec->select_group ?? $rec->group ?? '',
                // Production
                'output' => $rec->output ?? '',
                'target_total' => $rec->target_total ?? '',
                'actual_total' => $rec->actual_total ?? '',
                'ng_total' => $rec->ng_total ?? '',
                'balance_total' => $rec->balance_total ?? '',
                'loss_total' => $rec->loss_total ?? '',
                'output_total_ok' => $rec->output_total_ok ?? '',
                'qty_ok' => $rec->qty_ok ?? '',
                'qty_ng' => $rec->qty_ng ?? '',
                // Quality and operators (arrays)
                'quality_information' => is_array($rec->quality_information ?? null) ? $rec->quality_information : ($rec->quality_information ? (json_decode($rec->quality_information, true) ?: []) : []),
                'operators' => is_array($rec->operators ?? null) ? $rec->operators : ($rec->operators ? (json_decode($rec->operators, true) ?: []) : []),
                // Approval
                'issued_sop' => $rec->issued_sop ?? '',
                'checked_leader' => $rec->checked_leader ?? '',
                'approved_spv' => $rec->approved_spv ?? '',
            ];

            if (! isset($excelGroups[$monthKey])) {
                $excelGroups[$monthKey] = [
                    'year' => $year,
                    'month' => $monthName,
                    'rows' => [],
                ];
            }
            $excelGroups[$monthKey]['rows'][] = $row;
        }

        // --- CSV export: group rows by month and write .csv files with fixed headers ---
        try {
            // CSV export roots: prefer aaPanel csv folder, then Z: or UNC, else fallback
            $preferredCsvRoot = 'Z:' . DIRECTORY_SEPARATOR . 'PROD' . DIRECTORY_SEPARATOR . 'REPORT PCS' . DIRECTORY_SEPARATOR . 'csv';
            $preferredCsvRoot = ! empty($preferredCsvRoot) ? $preferredCsvRoot : null;

            $candidateCsvRoots = [];

            // Candidate 0: aaPanel csv folder
            if (! empty($aapanelCsvRoot)) {
                $candidateCsvRoots[] = [
                    'path' => $aapanelCsvRoot,
                    'name' => 'aaPanel csv folder (AAPANEL_REPORTS_DIR/csv)',
                ];
            }

            // Candidate 1: Z: drive csv folder
            if (! empty($preferredCsvRoot)) {
                $candidateCsvRoots[] = [
                    'path' => $preferredCsvRoot,
                    'name' => 'Z: csv folder',
                ];
            }

            // Candidate 2: UNC csv folder
            $uncCsvPath = '\\192.168.62.12\\14 Prod-02\\PROD\\REPORT PCS\\csv';
            $candidateCsvRoots[] = [
                'path' => $uncCsvPath,
                'name' => 'UNC csv folder',
            ];

            $csvRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports') . DIRECTORY_SEPARATOR . 'csv');
            $csvRoot = rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $csvRoot), DIRECTORY_SEPARATOR);

            $selectedCsvRoot = null;

            foreach ($candidateCsvRoots as $candidate) {
                $cand = $candidate['path'];
                $candName = $candidate['name'];

                if (empty($cand)) { continue; }

                if ($testWritable($cand)) {
                    $selectedCsvRoot = $cand;
                    $csvRoot = $selectedCsvRoot;
                    \Illuminate\Support\Facades\Log::info('GenerateProductionReports: berhasil memilih preferred csv root', ['path' => $csvRoot, 'type' => $candName]);
                    break;
                } else {
                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: candidate csv root tidak writable, skip', ['candidate' => $cand, 'type' => $candName]);
                }
            }

            if ($selectedCsvRoot === null) {
                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: tidak ada preferred csv root tersedia, gunakan local storage fallback', ['fallback' => $csvRoot]);
            }

            $MAX_QUALITY = config('report.max_quality_columns', 5);
            $MAX_OPERATORS = config('report.max_operators', 10);

            $labelMap = [
                // Meta
                'id' => 'ID',
                'date' => 'Date',
                'customer_name' => 'Customer',
                'line' => 'Line',
                'model' => 'Model',
                'dj_number' => 'DJ Number',
                'select_shift' => 'Shift',
                'select_group' => 'Group',
                // Production
                'output' => 'Output',
                'target_total' => 'Target Total',
                'actual_total' => 'Actual Total',
                'ng_total' => 'NG Total',
                'balance_total' => 'Balance Total',
                'loss_total' => 'Loss Total',
                'output_total_ok' => 'Total Output',
                // Approval
                'issued_sop' => 'Issued SO',
                'checked_leader' => 'Leader',
                'approved_spv' => 'SPV',
            ];

            $subLabelMap = [
                'process' => 'Process',
                'ng_item' => 'NG Item',
                'loc' => 'Location',
                'qty' => 'QTY',
                'results_qc' => 'Result',
                'sop_line' => 'SOP/LDR',
                'ipqc' => 'IPQC',
                'remarks_qc' => 'Remarks',
                'remarks' => 'Remarks',
                'technician' => 'Technician',
                'time' => 'Time',
                'name' => 'Name'
            ];

            // Order must follow the desired CSV layout (metadata -> production -> quality blocks -> operators -> approval)
            $fixedFields = [
                'id','date','customer_name','line','model','dj_number','select_shift','select_group',
                'output','target_total','actual_total','ng_total','balance_total','loss_total','output_total_ok',
                'issued_sop','checked_leader','approved_spv'
            ];

            $qualitySub = ['process','ng_item','loc','qty','results_qc','sop_line','ipqc','remarks_qc'];
            $operatorSub = ['process','name'];

            foreach ($excelGroups as $monthKey => $group) {
                $rows = $group['rows'] ?? [];
                if (empty($rows)) { continue; }

                $year = $group['year'];
                $monthName = $group['month'];

                $monthDir = $csvRoot . DIRECTORY_SEPARATOR . $monthName;
                if (! is_dir($monthDir)) {
                    $mkRes = $ensureDir($monthDir);
                    if (! $mkRes) {
                        \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to create month directory untuk CSV', ['dir' => $monthDir]);
                    }
                }

                $csvPath = $monthDir . DIRECTORY_SEPARATOR . $year . '_' . $monthName . '.csv';

                // build two-row header: primary header (group titles) and sub-header (field titles)
                $primaryHeader = [];
                $subHeader = [];

                // Fixed (non-array) fields: group into logical primary sections and put actual field labels in subheader
                $metaFields = ['id','date','customer_name','line','model','dj_number','select_shift','select_group'];
                $productionFields = ['output','target_total','actual_total','ng_total','balance_total','loss_total','output_total_ok'];
                $approvalFields = ['issued_sop','checked_leader','approved_spv'];

                foreach ($fixedFields as $f) {
                    $label = $labelMap[$f] ?? ucfirst(str_replace('_', ' ', $f));
                    if (in_array($f, $metaFields, true)) {
                        $primaryHeader[] = 'Meta';
                    } elseif (in_array($f, $productionFields, true)) {
                        $primaryHeader[] = 'Production';
                    } elseif (in_array($f, $approvalFields, true)) {
                        $primaryHeader[] = 'Approval';
                    } else {
                        $primaryHeader[] = '';
                    }
                    $subHeader[] = $label;
                }

                // Quality sub-columns: primary header 'Quality 1' etc, subheader uses sub labels
                for ($i = 1; $i <= $MAX_QUALITY; $i++) {
                    foreach ($qualitySub as $sub) {
                        $primaryHeader[] = "Quality {$i}";
                        $subHeader[] = ($subLabelMap[$sub] ?? ucfirst($sub));
                    }
                }

                // Operator sub-columns
                for ($i = 1; $i <= $MAX_OPERATORS; $i++) {
                    foreach ($operatorSub as $sub) {
                        $primaryHeader[] = "Operator {$i}";
                        $subHeader[] = ($subLabelMap[$sub] ?? ucfirst($sub));
                    }
                }

                // Normalize rows
                foreach ($rows as &$r) {
                    if (! isset($r['quality_information']) || ! is_array($r['quality_information'])) {
                        $r['quality_information'] = is_string($r['quality_information']) ? (@json_decode($r['quality_information'], true) ?: []) : [];
                    }
                    if (! isset($r['operators']) || ! is_array($r['operators'])) {
                        $r['operators'] = is_string($r['operators']) ? (@json_decode($r['operators'], true) ?: []) : [];
                    }
                    // normalize date to Y-m-d string for CSV
                    $r['date'] = ! empty($r['date']) ? Carbon::parse($r['date'])->format('Y-m-d') : '';
                }
                unset($r);

                // read existing IDs to avoid duplicates (handle two-row header)
                $existingIds = [];
                if (file_exists($csvPath) && filesize($csvPath) > 0) {
                    if (($h = fopen($csvPath, 'r')) !== false) {
                        $first = fgetcsv($h, 0, ',', '"', "\\");
                        $second = fgetcsv($h, 0, ',', '"', "\\");
                        $idIndex = false;
                        // check both header rows for 'id' column
                        $rowsToCheck = [];
                        if ($first !== false) { $rowsToCheck[] = $first; }
                        if ($second !== false) { $rowsToCheck[] = $second; }
                        foreach ($rowsToCheck as $row) {
                            $normalized = array_map(fn($c)=>strtolower(trim((string)$c)), $row);
                            foreach ($normalized as $i => $col) { if ($col === 'id') { $idIndex = $i; break 2; } }
                        }
                        while (($line = fgetcsv($h, 0, ',', '"', "\\")) !== false) {
                            if ($idIndex !== false && isset($line[$idIndex]) && $line[$idIndex] !== '') { $existingIds[(string)$line[$idIndex]] = true; }
                        }
                        fclose($h);
                    }
                }

                // open for append and write header if needed (use c+ with locking to avoid concurrency/permission issues)
                $intendedCsvPath = $csvPath; // remember the desired preferred location
                $csvOpenedPath = $csvPath;
                $fp = @fopen($csvOpenedPath, 'c+');
                if ($fp === false) {
                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to open csv (c+) on chosen csv root', ['path' => $csvPath, 'csvRoot' => $csvRoot]);
                    // fallback to storage fallback path for csv
                    $fallbackCsvRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports') . DIRECTORY_SEPARATOR . 'csv');
                    if ($csvRoot !== $fallbackCsvRoot) {
                        try {
                            if (! is_dir($fallbackCsvRoot)) { @mkdir($fallbackCsvRoot, 0777, true); }
                            $monthDir = $fallbackCsvRoot . DIRECTORY_SEPARATOR . $monthName;
                            if (! is_dir($monthDir)) { @mkdir($monthDir, 0777, true); }
                            $csvPathFallback = $monthDir . DIRECTORY_SEPARATOR . $year . '_' . $monthName . '.csv';
                            $fp = @fopen($csvPathFallback, 'c+');
                            if ($fp !== false) {
                                \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: opened csv at fallback csv root', ['path' => $csvPathFallback]);
                                $csvOpenedPath = $csvPathFallback;
                            } else {
                                \Illuminate\Support\Facades\Log::error('GenerateProductionReports: failed to open csv on fallback csv root (c+)', ['path' => $csvPathFallback]);
                                continue;
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('GenerateProductionReports: exception while attempting csv fallback', ['error' => $e->getMessage()]);
                            continue;
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::error('GenerateProductionReports: failed to open csv for append and no further fallback available', ['path' => $csvPath]);
                        continue;
                    }
                }

                // Acquire exclusive lock
                $locked = false;
                try {
                    $locked = flock($fp, LOCK_EX);
                } catch (\Throwable $e) {
                    $locked = false;
                }
                if (! $locked) {
                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: could not lock csv file for writing', ['path' => $csvOpenedPath]);
                    fclose($fp);
                    continue;
                }

                // Read existing IDs safely (account for two-row header)
                $existingIds = [];
                try {
                    rewind($fp);
                    $first = fgetcsv($fp, 0, ',', '"', "\\");
                    $second = fgetcsv($fp, 0, ',', '"', "\\");
                    $idIndex = false;
                    $rowsToCheck = [];
                    if ($first !== false) { $rowsToCheck[] = $first; }
                    if ($second !== false) { $rowsToCheck[] = $second; }
                    foreach ($rowsToCheck as $row) {
                        $normalized = array_map(fn($c)=>strtolower(trim((string)$c)), $row);
                        foreach ($normalized as $i => $col) { if ($col === 'id') { $idIndex = $i; break 2; } }
                    }
                    while (($line = fgetcsv($fp, 0, ',', '"', "\\")) !== false) {
                        if ($idIndex !== false && isset($line[$idIndex]) && $line[$idIndex] !== '') { $existingIds[(string)$line[$idIndex]] = true; }
                    }
                } catch (\Throwable $e) {
                    // non-fatal: continue with empty existingIds
                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: error reading existing csv rows', ['path' => $csvOpenedPath, 'error' => $e->getMessage()]);
                }

                // Move to end for append
                fseek($fp, 0, SEEK_END);

                // Determine if we need header (file empty or just created)
                $stat = fstat($fp);
                $needHeader = ($stat['size'] ?? 0) === 0;

                if ($needHeader) {
                    // write two header rows: primary (group) and subheader
                    fputcsv($fp, $primaryHeader);
                    fputcsv($fp, $subHeader);
                }

                // write new rows
                foreach ($rows as $r) {
                    $rid = (string)($r['id'] ?? '');
                    if ($rid !== '' && isset($existingIds[$rid])) { continue; }

                    $out = [];
                    foreach ($fixedFields as $f) {
                        $val = $r[$f] ?? '';
                        if (is_array($val) || is_object($val)) { $val = @json_encode($val); }
                        $out[] = $val;
                    }

                    for ($i = 0; $i < $MAX_QUALITY; $i++) {
                        $item = $r['quality_information'][$i] ?? [];
                        foreach ($qualitySub as $sub) {
                            $val = '';
                            if (is_array($item) && array_key_exists($sub, $item)) { $val = $item[$sub]; }
                            if (is_array($val) || is_object($val)) { $val = @json_encode($val); }
                            $out[] = $val;
                        }
                    }

                    for ($i = 0; $i < $MAX_OPERATORS; $i++) {
                        $item = $r['operators'][$i] ?? [];
                        foreach ($operatorSub as $sub) {
                            $val = '';
                            if (is_array($item) && array_key_exists($sub, $item)) { $val = $item[$sub]; }
                            if (is_array($val) || is_object($val)) { $val = @json_encode($val); }
                            $out[] = $val;
                        }
                    }

                    if (false === fputcsv($fp, $out)) {
                        \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to write csv row', ['path' => $csvOpenedPath, 'id' => $rid]);
                    }
                }

                fflush($fp);
                flock($fp, LOCK_UN);
                fclose($fp);

                // ensure $csvPath reflects the actual opened file (may be fallback)
                $csvPath = $csvOpenedPath;

                // If we wrote to the preferred csv root, also copy a local fallback to storage/app/reports/csv
                try {
                    $fallbackCsvRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports') . DIRECTORY_SEPARATOR . 'csv');
                    if ($csvRoot !== $fallbackCsvRoot) {
                        if (! is_dir($fallbackCsvRoot)) { @mkdir($fallbackCsvRoot, 0777, true); }
                        $fallbackMonthDir = $fallbackCsvRoot . DIRECTORY_SEPARATOR . $monthName;
                        if (! is_dir($fallbackMonthDir)) { @mkdir($fallbackMonthDir, 0777, true); }
                        $fallbackCsvPath = $fallbackMonthDir . DIRECTORY_SEPARATOR . $year . '_' . $monthName . '.csv';
                        if (file_exists($csvPath)) { @copy($csvPath, $fallbackCsvPath); }
                        \Illuminate\Support\Facades\Log::info('GenerateProductionReports: csv also copied to local fallback', ['path' => $fallbackCsvPath, 'source' => $csvPath]);

                        // If we actually wrote to fallback (opened path differs from intended), try to restore to preferred location
                        try {
                            if ($csvOpenedPath !== ($intendedCsvPath ?? '') && file_exists($fallbackCsvPath) && filesize($fallbackCsvPath) > 0) {
                                $restored = $verifyAndRetryCopy($fallbackCsvPath, $intendedCsvPath ?? $csvPath, 3, 500);
                                if ($restored) {
                                    \Illuminate\Support\Facades\Log::info('GenerateProductionReports: restored preferred csv by copying fallback -> preferred', ['preferred' => $intendedCsvPath, 'fallback' => $fallbackCsvPath]);
                                } else {
                                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to restore preferred csv from fallback', ['preferred' => $intendedCsvPath, 'fallback' => $fallbackCsvPath]);
                                }
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: exception while attempting to restore preferred csv', ['error' => $e->getMessage()]);
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('GenerateProductionReports: failed to copy csv to local fallback', ['error' => $e->getMessage()]);
                }

                \Illuminate\Support\Facades\Log::info('GenerateProductionReports: csv written', ['path' => $csvPath, 'rows_written' => count($rows)]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GenerateProductionReports: csv export error: ' . $e->getMessage());
        }
        // ZIP creation and separate export CSVs removed per user request
    }
}
