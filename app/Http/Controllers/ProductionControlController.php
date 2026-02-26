<?php

namespace App\Http\Controllers;

use App\Models\ProductionControlShift1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductionControlController extends Controller
{
    /**
     * Generate and return PDF for the given production control record.
     * If `barryvdh/laravel-dompdf` is installed and configured the controller
     * will use it via the `PDF` facade. Otherwise it will return the HTML
     * view so users can print/save as PDF from the browser.
     */
    public function downloadPdf(ProductionControlShift1 $production_control)
    {
        $data = ['record' => $production_control];

        // Prefer installed DomPDF wrapper if available in the container
        if (app()->bound('dompdf.wrapper')) {
            try {
                $pdf = app('dompdf.wrapper')->loadView('pdf.production_control', $data);
                $shiftPart = $production_control->select_shift ?? $production_control->shift ?? 'unknown';
                $groupPart = $production_control->select_group ?? $production_control->group ?? 'unknown';
                $modelPart = $production_control->model ?? 'unknown';
                $safeShift = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $shiftPart);
                $safeGroup = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $groupPart);
                $safeModel = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $modelPart);
                $safeShift = trim($safeShift, '-');
                $safeGroup = trim($safeGroup, '-');
                $safeModel = trim($safeModel, '-');
                $filename = 'shift_' . ($safeShift ?: 'unknown') . '-group_' . ($safeGroup ?: 'unknown') . '-' . ($safeModel ?: 'unknown') . '.pdf';
                $content = $pdf->output();

                // Preferred save path (config), fallback to storage/app/<subdir>
                $preferredRoot = config('report.preferred_root', 'Z:' . DIRECTORY_SEPARATOR . 'Hendri' . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Report Produksi');
                $fallbackRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports'));
                $forcePreferred = config('report.force_preferred', false);

                // If force_preferred is true, attempt to create the preferred root. Otherwise only
                // use preferred when it already exists and is writable. Either way, fall back to
                // local storage if preferred is unusable.
                if ($forcePreferred) {
                    try {
                        if (! is_dir($preferredRoot)) { @mkdir($preferredRoot, 0777, true); }
                    } catch (\Throwable $e) {
                        // ignore; we'll check below
                    }
                }

                if (is_dir($preferredRoot) && is_writable($preferredRoot)) {
                    $saveRoot = $preferredRoot;
                } else {
                    Log::warning('Preferred report path unavailable or not writable, falling back to storage', ['preferred' => $preferredRoot, 'force_preferred' => $forcePreferred]);
                    $saveRoot = $fallbackRoot;
                    if (! is_dir($saveRoot)) { @mkdir($saveRoot, 0777, true); }
                }

                // use Indonesian month name for folder based on record date (e.g., 'januari')
                $monthNames = [1=>'januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
                $recordDate = $production_control->date ?? now();
                $monthName = $monthNames[(int) $recordDate->format('n')];
                // monthDir berdasarkan saveRoot yang sudah ditentukan di atas
                $monthDir = $saveRoot . DIRECTORY_SEPARATOR . $monthName;
                try {
                    // pastikan folder bulan ada
                    if (! is_dir($monthDir)) { @mkdir($monthDir, 0777, true); }
                    // tulis PDF yang dihasilkan ke disk
                    $path = $monthDir . DIRECTORY_SEPARATOR . $filename;
                    file_put_contents($path, $content);
                    // tandai record sudah didownload
                    $production_control->downloaded_at = now();
                    $production_control->save();
                    return response()->download($path, $filename);
                } catch (\Exception $e) {
                    // jika tidak bisa menyimpan ke disk, kembalikan file langsung dari memory
                    Log::warning('Failed to save report to disk, returning direct PDF download', ['path' => $path ?? null, 'error' => $e->getMessage()]);
                    try { $production_control->downloaded_at = now(); $production_control->save(); } catch (\Exception $ee) {}
                    return response($content, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                }
            } catch (\Exception $e) {
                // Fall back to HTML view on error from PDF generation
            }
        }

        // HTML fallback: render view and save into monthly folder, then return download
        $html = view('pdf.production_control', $data)->render();
        $shiftPart = $production_control->select_shift ?? $production_control->shift ?? 'unknown';
        $groupPart = $production_control->select_group ?? $production_control->group ?? 'unknown';
        $modelPart = $production_control->model ?? 'unknown';
        $safeShift = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $shiftPart);
        $safeGroup = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $groupPart);
        $safeModel = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $modelPart);
        $safeShift = trim($safeShift, '-');
        $safeGroup = trim($safeGroup, '-');
        $safeModel = trim($safeModel, '-');
        $filename = 'shift_' . ($safeShift ?: 'unknown') . '-group_' . ($safeGroup ?: 'unknown') . '-' . ($safeModel ?: 'unknown') . '.html';

        // Preferred save path (konfigurasi) dan fallback lokal.
        // - `preferred_root` diambil dari config/report.php atau .env
        // - `fallback_subdir` jadi subfolder di `storage/app` jika preferred tidak tersedia
        $preferredRoot = config('report.preferred_root', 'Z:' . DIRECTORY_SEPARATOR . 'Hendri' . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Report Produksi');
        $fallbackRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports'));
        $forcePreferred = config('report.force_preferred', false);

        // Jika `force_preferred` true maka coba buat preferredRoot (bergantung pada akses service account).
        // Jika tidak, kita hanya akan gunakan preferred jika sudah ada dan writable.
        if ($forcePreferred) {
            try { if (! is_dir($preferredRoot)) { @mkdir($preferredRoot, 0777, true); } } catch (\Throwable $e) {}
        }

        if (is_dir($preferredRoot) && is_writable($preferredRoot)) {
            // gunakan preferred jika benar-benar ada dan dapat ditulis
            $saveRoot = $preferredRoot;
        } else {
            // catat ke log dan gunakan fallback lokal
            Log::warning('Preferred report path unavailable or not writable, falling back to storage', ['preferred' => $preferredRoot]);
            $saveRoot = $fallbackRoot;
            if (! is_dir($saveRoot)) { @mkdir($saveRoot, 0777, true); }
        }

        // Gunakan nama bulan (Bahasa Indonesia) berdasarkan tanggal record sehingga
        // setiap laporan tersimpan di folder bulan yang sesuai.
        $monthNames = [1=>'januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
        $recordDate = $production_control->date ?? now();
        $monthName = $monthNames[(int) $recordDate->format('n')];
        $monthDir = $saveRoot . DIRECTORY_SEPARATOR . $monthName;
        try {
            if (! is_dir($monthDir)) {
                @mkdir($monthDir, 0777, true);
            }
            $path = $monthDir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($path, $html);
            try {
                $production_control->downloaded_at = now();
                $production_control->save();
            } catch (\Exception $e) {
            }
            return response()->download($path, basename($path));
        } catch (\Exception $e) {
            Log::warning('Failed to save HTML report to disk, returning inline HTML', ['error' => $e->getMessage()]);
            return response($html);
        }
    }

    /**
     * Download multiple records as a ZIP of PDFs (or HTML if PDF generator not available).
     * Accepts query parameter `ids` as comma-separated list of record ids.
     */
    public function downloadMultiple(Request $request)
    {
        $ids = $request->query('ids', '');
        $ids = array_filter(array_map('trim', explode(',', $ids)));
        if (empty($ids)) {
            abort(400, 'No IDs provided');
        }

        $records = ProductionControlShift1::whereIn('id', $ids)->get();
        if ($records->isEmpty()) {
            abort(404, 'No records found');
        }

        $preferredRoot = config('report.preferred_root', 'Z:' . DIRECTORY_SEPARATOR . 'Hendri' . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Report Produksi');
        $fallbackRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports'));
        $forcePreferred = config('report.force_preferred', false);

        if ($forcePreferred) {
            try {
                if (! is_dir($preferredRoot)) { @mkdir($preferredRoot, 0777, true); }
            } catch (\Throwable $e) {
            }
        }

        if (is_dir($preferredRoot) && is_writable($preferredRoot)) {
            $saveRoot = $preferredRoot;
        } else {
            Log::warning('Preferred report path unavailable or not writable, falling back to storage', ['preferred' => $preferredRoot, 'force_preferred' => $forcePreferred]);
            $saveRoot = $fallbackRoot;
            if (! is_dir($saveRoot)) { @mkdir($saveRoot, 0777, true); }
        }

        $monthNames = [1=>'januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];

        $files = [];

        foreach ($records as $rec) {
            $data = ['record' => $rec];

            // filename: date-customer-line
            $recordDate = $rec->date ?? now();
            $dateStr = optional($rec->date)->format('d-m-Y') ?? now()->format('d-m-Y');
            $customer = $rec->customer_name ?? ($rec->customer->name ?? 'customer');
            $safeCustomer = preg_replace('/[^A-Za-z0-9]+/', '-', $customer);
            $safeCustomer = trim($safeCustomer, '-');
            $line = $rec->line ?? 'line';
            $safeLine = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $line);
            $safeLine = trim($safeLine, '-');
            $filenameBase = $dateStr . '-' . $safeCustomer . '-' . $safeLine;

            // per-record month folder based on record date
            $monthDir = $saveRoot . DIRECTORY_SEPARATOR . $monthNames[(int) $recordDate->format('n')];
            if (! is_dir($monthDir)) { @mkdir($monthDir, 0777, true); }

            // Prefer PDF generation if available
            if (app()->bound('dompdf.wrapper')) {
                try {
                    $pdf = app('dompdf.wrapper')->loadView('pdf.production_control', $data);
                    $content = $pdf->output();
                    $path = $monthDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                    file_put_contents($path, $content);
                    $files[] = $path;
                    // mark downloaded
                    $rec->downloaded_at = now();
                    $rec->save();
                    continue;
                } catch (\Exception $e) {
                    // fall through to HTML fallback
                }
            }

            // HTML fallback
            $html = view('pdf.production_control', $data)->render();
            $path = $monthDir . DIRECTORY_SEPARATOR . $filenameBase . '.html';
            file_put_contents($path, $html);
            $files[] = $path;

            // mark downloaded (even if only HTML)
            try {
                $rec->downloaded_at = now();
                $rec->save();
            } catch (\Exception $e) {
                // ignore
            }
        }

        // If ZipArchive available, create zip
        $zipPath = $monthDir . DIRECTORY_SEPARATOR . 'pcs_reports_' . now()->format('Ymd_His') . '.zip';
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                return response()->download($zipPath, basename($zipPath));
            }
        }

        // If no ZipArchive, if only one file return it directly, else create a .tar.gz fallback
        if (count($files) === 1) {
            return response()->download($files[0], basename($files[0]));
        }

        // try creating a tar.gz via PharData (may not be available)
        try {
            $tarPath = $monthDir . DIRECTORY_SEPARATOR . 'pcs_reports_' . uniqid() . '.tar';
            $phar = new \PharData($tarPath);
            foreach ($files as $file) {
                $phar->addFile($file, basename($file));
            }
            $gzPath = $tarPath . '.gz';
            $phar->compress(\Phar::GZ);
            return response()->download($gzPath, 'pcs_reports.tar.gz');
        } catch (\Exception $e) {
            // final fallback: return concatenated HTML
            $content = '';
            foreach ($files as $file) {
                $content .= file_get_contents($file) . "\n\n<hr style=\"page-break-after:always\">\n";
            }
            return response($content, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'attachment; filename="pcs_reports.html"',
            ]);
        }
    }

    /**
     * Schedule generation of reports after 1 hour (saved to server path).
     * Accepts query parameter `ids` as comma-separated list.
     */
    public function scheduleDownload(Request $request)
    {
        $ids = $request->query('ids', '');
        $ids = array_filter(array_map('trim', explode(',', $ids)));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No IDs provided for scheduling.');
        }

        // Dispatch job with 1 minute delay
        \App\Jobs\GenerateProductionReports::dispatch($ids)->delay(now()->addMinute());

        return redirect()->back()->with('success', 'Reports scheduled to be generated and saved after 1 minute.');
    }

    /**
     * Diagnostic: test writing a small file to the preferred report path.
     * Returns JSON with success or error details.
     */
    public function testReportPath(Request $request)
    {
        $preferredRoot = config('report.preferred_root', 'Z:' . DIRECTORY_SEPARATOR . 'Hendri' . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Report Produksi');
        $result = ['preferred' => $preferredRoot];

        if (! is_dir($preferredRoot)) {
            $result['ok'] = false;
            $result['error'] = 'Preferred path does not exist.';
            return response()->json($result, 404);
        }

        if (! is_writable($preferredRoot)) {
            $result['ok'] = false;
            $result['error'] = 'Preferred path is not writable by PHP process.';
            return response()->json($result, 403);
        }

        $testFile = $preferredRoot . DIRECTORY_SEPARATOR . 'pcs_test_' . time() . '.txt';
        try {
            file_put_contents($testFile, "test\n");
            $result['ok'] = true;
            $result['written'] = $testFile;
            // keep the file for inspection, do not delete automatically
            return response()->json($result);
        } catch (\Exception $e) {
            $result['ok'] = false;
            $result['error'] = $e->getMessage();
            Log::warning('Failed writing diagnostic test file to preferred report path', ['path' => $preferredRoot, 'error' => $e->getMessage()]);
            return response()->json($result, 500);
        }
    }
}
