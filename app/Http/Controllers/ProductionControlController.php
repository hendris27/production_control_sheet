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
                $linePart = $production_control->line ?? 'unknown';
                $safeShift = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $shiftPart);
                $safeGroup = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $groupPart);
                $safeModel = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $modelPart);
                $safeLine = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $linePart);
                $safeShift = trim($safeShift, '-');
                $safeGroup = trim($safeGroup, '-');
                $safeModel = trim($safeModel, '-');
                $safeLine = trim($safeLine, '-');
                $filename = '' . ($safeModel ?: 'unknown') . '-' . ($safeLine ?: 'unknown') . '-' . ($safeShift ?: 'unknown') . '-' . ($safeGroup ?: 'unknown') . '.pdf';
                $content = $pdf->output();

                // Get reliable save root
                $saveRoot = $this->getReliableSaveRoot();

                // use Indonesian month name for folder based on record date (e.g., 'januari')
                $monthNames = [1=>'januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
                $recordDate = $production_control->date ?? now();
                $monthName = $monthNames[(int) $recordDate->format('n')];
                // monthDir berdasarkan saveRoot yang sudah ditentukan di atas
                $monthDir = $saveRoot . DIRECTORY_SEPARATOR . $monthName;
                try {
                    // pastikan folder bulan ada
                    if (! is_dir($monthDir)) { mkdir($monthDir, 0777, true); }
                    // tulis PDF yang dihasilkan ke disk
                    $path = $monthDir . DIRECTORY_SEPARATOR . $filename;
                    file_put_contents($path, $content);
                    // tambahan: simpan CSV, PDF, dan Excel ke reliable share root
                    $shareRoot = $this->getReliableShareRoot();
                    $shareMonthDir = $shareRoot . DIRECTORY_SEPARATOR . $monthName;
                    try { if (! is_dir($shareMonthDir)) { mkdir($shareMonthDir, 0777, true); } } catch (\Throwable $_) {}
                    $csvDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'csv';
                    $excelDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'excel';
                    $pdfDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'pdf';
                    try { foreach ([$csvDir, $excelDir, $pdfDir] as $d) { if (! is_dir($d)) { mkdir($d, 0777, true); } } } catch (\Throwable $_) {}

                    $base = preg_replace('/\.pdf$/i','', $filename);
                    try { $this->saveRecordCsv($production_control, $csvDir . DIRECTORY_SEPARATOR . $base . '.csv'); } catch (\Throwable $e) { Log::warning('Failed to save CSV', ['path' => $csvDir . DIRECTORY_SEPARATOR . $base . '.csv', 'error' => $e->getMessage()]); }
                    try { $this->saveRecordExcel($production_control, $excelDir . DIRECTORY_SEPARATOR . $base . '.xlsx'); } catch (\Throwable $e) { Log::warning('Failed to save Excel', ['path' => $excelDir . DIRECTORY_SEPARATOR . $base . '.xlsx', 'error' => $e->getMessage()]); }
                    try { file_put_contents($pdfDir . DIRECTORY_SEPARATOR . $filename, $content); $this->backupFile($pdfDir . DIRECTORY_SEPARATOR . $filename, 'pdf'); } catch (\Throwable $e) { Log::warning('Failed to save PDF to month/pdf folder', ['path' => $pdfDir . DIRECTORY_SEPARATOR . $filename, 'error' => $e->getMessage()]); }

                    // tetap simpan juga PDF per-customer: berbanding dengan share root
                    $customerName = $production_control->customer ?? $production_control->customer_name ?? 'unknown_customer';
                    $safeCustomer = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $customerName);
                    $safeCustomer = trim($safeCustomer, '-');
                    $customerPdfDir = $shareRoot . DIRECTORY_SEPARATOR . ($safeCustomer ?: 'unknown') . DIRECTORY_SEPARATOR . $monthName;
                    try { if (! is_dir($customerPdfDir)) { mkdir($customerPdfDir, 0777, true); } } catch (\Throwable $_) {}
                    try { file_put_contents($customerPdfDir . DIRECTORY_SEPARATOR . $filename, $content); $this->backupFile($customerPdfDir . DIRECTORY_SEPARATOR . $filename, 'pdf'); } catch (\Throwable $e) { Log::warning('Failed to save PDF to customer folder', ['path' => $customerPdfDir . DIRECTORY_SEPARATOR . $filename, 'error' => $e->getMessage()]); }
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
        $linePart = $production_control->line ?? 'unknown';
        $safeShift = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $shiftPart);
        $safeGroup = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $groupPart);
        $safeModel = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $modelPart);
        $safeLine = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $linePart);
        $safeShift = trim($safeShift, '-');
        $safeGroup = trim($safeGroup, '-');
        $safeModel = trim($safeModel, '-');
        $safeLine = trim($safeLine, '-');
        $filename = '' . ($safeModel ?: 'unknown') . '-' . ($safeLine ?: 'unknown') . '-' . ($safeShift ?: 'unknown') . '-' . ($safeGroup ?: 'unknown') . '.html';

        // Get reliable save root using helper method
        $saveRoot = $this->getReliableSaveRoot();

        // Gunakan nama bulan (Bahasa Indonesia) berdasarkan tanggal record sehingga
        // setiap laporan tersimpan di folder bulan yang sesuai.
        $monthNames = [1=>'januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
        $recordDate = $production_control->date ?? now();
        $monthName = $monthNames[(int) $recordDate->format('n')];
        $monthDir = $saveRoot . DIRECTORY_SEPARATOR . $monthName;
        try {
            if (! is_dir($monthDir)) {
                mkdir($monthDir, 0777, true);
            }
            $path = $monthDir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($path, $html);
            // tambahan: simpan CSV dan Excel ke reliable share root
            $shareRoot = $this->getReliableShareRoot();
            $shareMonthDir = $shareRoot . DIRECTORY_SEPARATOR . $monthName;
            try { if (! is_dir($shareMonthDir)) { mkdir($shareMonthDir, 0777, true); } } catch (\Throwable $_) {}
            $csvDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'csv';
            $excelDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'excel';
            try { foreach ([$csvDir, $excelDir] as $d) { if (! is_dir($d)) { mkdir($d, 0777, true); } } } catch (\Throwable $_) {}
            $base = preg_replace('/\.html$/i','',$filename);
            try { $this->saveRecordCsv($production_control, $csvDir . DIRECTORY_SEPARATOR . $base . '.csv'); } catch (\Throwable $e) { Log::warning('Failed to save CSV', ['path' => $csvDir . DIRECTORY_SEPARATOR . $base . '.csv', 'error' => $e->getMessage()]); }
            try { $this->saveRecordExcel($production_control, $excelDir . DIRECTORY_SEPARATOR . $base . '.xlsx'); } catch (\Throwable $e) { Log::warning('Failed to save Excel', ['path' => $excelDir . DIRECTORY_SEPARATOR . $base . '.xlsx', 'error' => $e->getMessage()]); }
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

        $saveRoot = $this->getReliableSaveRoot();

        $monthNames = [1=>'januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];

        $files = [];

        foreach ($records as $rec) {
            $data = ['record' => $rec];

            // filename: date-customer-line
            $recordDate = $rec->date ?? now();
            $dateStr = optional($rec->date)->format('d-m-Y') ?? now()->format('d-m-Y');
            $model = $rec->model ?? 'unknown';
            $safeModel = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $model);
            $safeModel = trim($safeModel, '-');
            $line = $rec->line ?? 'line';
            $safeLine = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $line);
            $safeLine = trim($safeLine, '-');
            $shift = $rec->select_shift ?? $rec->shift ?? 'unknown';
            $safeShift = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $shift);
            $safeShift = trim($safeShift, '-');
            $group = $rec->select_group ?? $rec->group ?? 'unknown';
            $safeGroup = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $group);
            $safeGroup = trim($safeGroup, '-');
            $filenameBase = $safeModel . '-' . $safeLine . '-' . $safeShift . '-' . $safeGroup;

            // per-record month folder based on record date
            $monthDir = $saveRoot . DIRECTORY_SEPARATOR . $monthNames[(int) $recordDate->format('n')];
            if (! is_dir($monthDir)) { mkdir($monthDir, 0777, true); }

            // Prefer PDF generation if available
            if (app()->bound('dompdf.wrapper')) {
                try {
                    $pdf = app('dompdf.wrapper')->loadView('pdf.production_control', $data);
                    $content = $pdf->output();
                    $path = $monthDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                    file_put_contents($path, $content);
                    $files[] = $path;
                    // simpan CSV, PDF, & Excel ke reliable share root
                    $shareRoot = $this->getReliableShareRoot();
                    $shareMonthDir = $shareRoot . DIRECTORY_SEPARATOR . $monthNames[(int) $recordDate->format('n')];
                    try { if (! is_dir($shareMonthDir)) { mkdir($shareMonthDir, 0777, true); } } catch (\Throwable $_) {}
                    $csvDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'csv';
                    $excelDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'excel';
                    $pdfDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'pdf';
                    try { foreach ([$csvDir, $excelDir, $pdfDir] as $d) { if (! is_dir($d)) { mkdir($d, 0777, true); } } } catch (\Throwable $_) {}
                    try { $this->saveRecordCsv($rec, $csvDir . DIRECTORY_SEPARATOR . $filenameBase . '.csv'); } catch (\Throwable $e) { Log::warning('Failed to save CSV', ['path' => $csvDir . DIRECTORY_SEPARATOR . $filenameBase . '.csv', 'error' => $e->getMessage()]); }
                    try { $this->saveRecordExcel($rec, $excelDir . DIRECTORY_SEPARATOR . $filenameBase . '.xlsx'); } catch (\Throwable $e) { Log::warning('Failed to save Excel', ['path' => $excelDir . DIRECTORY_SEPARATOR . $filenameBase . '.xlsx', 'error' => $e->getMessage()]); }
                    try { file_put_contents($pdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf', $content); } catch (\Throwable $e) { Log::warning('Failed to save PDF to month/pdf folder', ['path' => $pdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf', 'error' => $e->getMessage()]); }
                    // simpan PDF juga di struktur customer/bulan
                    $customerName = $rec->customer ?? $rec->customer_name ?? 'unknown_customer';
                    $safeCustomer = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $customerName);
                    $safeCustomer = trim($safeCustomer, '-');
                    $customerPdfDir = $shareRoot . DIRECTORY_SEPARATOR . ($safeCustomer ?: 'unknown') . DIRECTORY_SEPARATOR . $monthNames[(int) $recordDate->format('n')];
                    try { if (! is_dir($customerPdfDir)) { mkdir($customerPdfDir, 0777, true); } } catch (\Throwable $_) {}
                    try { file_put_contents($customerPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf', $content); } catch (\Throwable $e) { Log::warning('Failed to save PDF to customer folder', ['path' => $customerPdfDir . DIRECTORY_SEPARATOR . $filenameBase . '.pdf', 'error' => $e->getMessage()]); }
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

            // simpan CSV & Excel untuk HTML fallback juga ke reliable share root
            $shareRoot = $this->getReliableShareRoot();
            $shareMonthDir = $shareRoot . DIRECTORY_SEPARATOR . $monthNames[(int) $recordDate->format('n')];
            try { if (! is_dir($shareMonthDir)) { mkdir($shareMonthDir, 0777, true); } } catch (\Throwable $_) {}
            $csvDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'csv';
            $excelDir = $shareMonthDir . DIRECTORY_SEPARATOR . 'excel';
            try { foreach ([$csvDir, $excelDir] as $d) { if (! is_dir($d)) { mkdir($d, 0777, true); } } } catch (\Throwable $_) {}
            try { $this->saveRecordCsv($rec, $csvDir . DIRECTORY_SEPARATOR . $filenameBase . '.csv'); } catch (\Throwable $e) { Log::warning('Failed to save CSV', ['path' => $csvDir . DIRECTORY_SEPARATOR . $filenameBase . '.csv', 'error' => $e->getMessage()]); }
            try { $this->saveRecordExcel($rec, $excelDir . DIRECTORY_SEPARATOR . $filenameBase . '.xlsx'); } catch (\Throwable $e) { Log::warning('Failed to save Excel', ['path' => $excelDir . DIRECTORY_SEPARATOR . $filenameBase . '.xlsx', 'error' => $e->getMessage()]); }

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
        $preferredRoot = config('report.preferred_root');
        $fallbackRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports'));
        $forcePreferred = config('report.force_preferred', false);

        $result = [
            'status' => 'checking',
            'preferred_path' => $preferredRoot,
            'fallback_path' => $fallbackRoot,
            'force_preferred' => $forcePreferred,
            'used_path' => null,
            'details' => [],
        ];

        // Test preferred path (with tryCreate if force_preferred)
        if (!empty($preferredRoot)) {
            $result['details']['preferred'] = [
                'configured' => true,
                'exists' => @is_dir($preferredRoot),
                'is_writable' => @is_writable($preferredRoot),
                'force_preferred' => $forcePreferred,
            ];

            if ($this->isPathAccessible($preferredRoot, $forcePreferred)) {
                $result['used_path'] = $preferredRoot;
                $result['status'] = 'using_preferred';
                $result['details']['preferred']['accessible'] = true;
                Log::info('Test report path: Using preferred', ['path' => $preferredRoot, 'force' => $forcePreferred]);
                return response()->json($result, 200);
            } else {
                $result['details']['preferred']['accessible'] = false;
            }
        } else {
            $result['details']['preferred'] = ['configured' => false];
        }

        // Test fallback path
        $result['details']['fallback'] = [
            'exists' => @is_dir($fallbackRoot),
            'is_writable' => @is_writable($fallbackRoot),
        ];

        if ($this->isPathAccessible($fallbackRoot)) {
            $result['used_path'] = $fallbackRoot;
            $result['status'] = 'using_fallback';
            $result['details']['fallback']['accessible'] = true;
            Log::info('Test report path: Using fallback', ['path' => $fallbackRoot]);
            return response()->json($result, 200);
        }

        // Try to create fallback if not exists
        try {
            if (! is_dir($fallbackRoot)) {
                mkdir($fallbackRoot, 0777, true);
                chmod($fallbackRoot, 0777);
            }

            // Test write
            $testFile = $fallbackRoot . DIRECTORY_SEPARATOR . 'pcs_test_' . time() . '.txt';
            if (file_put_contents($testFile, "test write\n") !== false) {
                chmod($testFile, 0777);
                @unlink($testFile);

                $result['used_path'] = $fallbackRoot;
                $result['status'] = 'fallback_created_and_working';
                $result['details']['fallback']['accessible'] = true;
                $result['details']['fallback']['created_success'] = true;
                Log::info('Test report path: Fallback created and working', ['path' => $fallbackRoot]);
                return response()->json($result, 200);
            }
        } catch (\Throwable $e) {
            $result['details']['fallback']['creation_error'] = $e->getMessage();
            Log::warning('Failed to create/test fallback report path', ['path' => $fallbackRoot, 'error' => $e->getMessage()]);
        }

        $result['status'] = 'error';
        $result['error'] = 'No accessible path found. Both preferred and fallback paths are not writable or cannot be created.';
        Log::error('Test report path: All paths failed', $result['details']);
        return response()->json($result, 500);
    }

    /**
     * Save a model's attributes as CSV to $path
     */
    private function saveRecordCsv($record, $path)
    {
        // Ensure directory exists with proper permissions
        $dir = dirname($path);
        if (! is_dir($dir)) {
            try {
                mkdir($dir, 0777, true);
                chmod($dir, 0777);
                Log::debug('Created CSV directory', ['path' => $dir]);
            } catch (\Throwable $e) {
                throw new \RuntimeException('Unable to create directory for CSV: ' . $dir . ' (' . $e->getMessage() . ')');
            }
        }

        if (! is_writable($dir)) {
            throw new \RuntimeException('Directory is not writable for CSV: ' . $dir);
        }

        try {
            $attrs = $record->getAttributes();
            $fp = fopen($path, 'w');
            if (! $fp) {
                throw new \RuntimeException('Unable to open file for writing');
            }

            try {
                // header
                if (fputcsv($fp, array_keys($attrs)) === false) {
                    throw new \RuntimeException('Failed to write CSV header');
                }
                // values (cast everything to string)
                $values = array_map(function ($v) { return is_scalar($v) || $v === null ? (string) $v : json_encode($v); }, array_values($attrs));
                if (fputcsv($fp, $values) === false) {
                    throw new \RuntimeException('Failed to write CSV values');
                }
                chmod($path, 0777);
                Log::debug('Successfully saved CSV', ['path' => $path]);

                // Auto-backup to local storage
                $this->backupFile($path, 'csv');
            } finally {
                fclose($fp);
            }
        } catch (\Throwable $e) {
            Log::error('CSV save failed', ['path' => $path, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Save a model's attributes as Excel (.xlsx) if PhpSpreadsheet available,
     * otherwise write a simple HTML-based .xls.
     */
    private function saveRecordExcel($record, $path)
    {
        // Ensure directory exists with proper permissions
        $dir = dirname($path);
        if (! is_dir($dir)) {
            try {
                mkdir($dir, 0777, true);
                chmod($dir, 0777);
                Log::debug('Created Excel directory', ['path' => $dir]);
            } catch (\Throwable $e) {
                throw new \RuntimeException('Unable to create directory for Excel: ' . $dir . ' (' . $e->getMessage() . ')');
            }
        }

        if (! is_writable($dir)) {
            throw new \RuntimeException('Directory is not writable for Excel: ' . $dir);
        }

        try {
            $attrs = $record->getAttributes();
            // if PhpSpreadsheet available, use it
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $row = 1;
                // tulis header
                $colIndex = 1;
                foreach (array_keys($attrs) as $k) {
                    $colLetter = $this->colIndexToLetter($colIndex++);
                    $sheet->setCellValue($colLetter . $row, $k);
                }
                // tulis nilai
                $row = 2; $colIndex = 1;
                foreach (array_values($attrs) as $v) {
                    $colLetter = $this->colIndexToLetter($colIndex++);
                    $sheet->setCellValue($colLetter . $row, is_scalar($v) || $v === null ? (string) $v : json_encode($v));
                }
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save($path);
                chmod($path, 0777);
                Log::debug('Successfully saved Excel (XLSX)', ['path' => $path]);

                // Auto-backup to local storage
                $this->backupFile($path, 'excel');
                return;
            }

            // Fallback: write HTML table and save as .xls (Excel can open it)
            $html = '<table border="1"><tr>';
            foreach (array_keys($attrs) as $k) { $html .= '<th>' . htmlspecialchars($k) . '</th>'; }
            $html .= '</tr><tr>';
            foreach (array_values($attrs) as $v) { $html .= '<td>' . htmlspecialchars(is_scalar($v) || $v === null ? (string) $v : json_encode($v)) . '</td>'; }
            $html .= '</tr></table>';
            file_put_contents($path, $html);
            chmod($path, 0777);
            Log::debug('Successfully saved Excel (HTML fallback)', ['path' => $path]);

            // Auto-backup to local storage
            $this->backupFile($path, 'excel');
        } catch (\Throwable $e) {
            Log::error('Excel save failed', ['path' => $path, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get the most reliable share root for reports (CSV, Excel, PDF backups)
     */
    private function getReliableShareRoot()
    {
        $forcePreferred = config('report.force_preferred', false);

        // Try primary share root first with option to create if force_preferred
        $shareRoot = config('report.preferred_root');
        if (!empty($shareRoot) && $this->isPathAccessible($shareRoot, $forcePreferred)) {
            return $shareRoot;
        }

        // Try alternate UNC paths if available from env
        $alternate = env('REPORT_ALTERNATE_ROOT');
        if ($alternate && $this->isPathAccessible($alternate, $forcePreferred)) {
            Log::info('Using alternate share root for reports', ['path' => $alternate]);
            return $alternate;
        }

        // Fallback to local storage/app/reports-share
        $fallback = storage_path('app' . DIRECTORY_SEPARATOR . 'reports-share');
        if (!empty($shareRoot)) {
            Log::warning('Using fallback for share root (share path not accessible)', ['preferred' => $shareRoot, 'fallback' => $fallback]);
        }

        $this->ensureDirectory($fallback);
        return $fallback;
    }

    /**
     * Create directory with proper permissions and logging
     */
    private function ensureDirectory($path)
    {
        if (is_dir($path)) {
            if (! is_writable($path)) {
                try {
                    chmod($path, 0777);
                } catch (\Throwable $e) {
                    Log::warning('Could not fix permissions on existing directory', ['path' => $path, 'error' => $e->getMessage()]);
                }
            }
            return true;
        }

        try {
            mkdir($path, 0777, true);
            chmod($path, 0777);
            Log::debug('Created directory', ['path' => $path]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to create directory', ['path' => $path, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Convert 1-based column index to Excel column letters (1 -> A, 27 -> AA)
     */
    private function colIndexToLetter($index)
    {
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intval(($index - $mod) / 26);
        }
        return $letters;
    }

    /**
     * Test if a path is actually accessible (for remote/network drive checking)
     * Returns true only if path exists AND is writable
     */
    private function isPathAccessible($path, $tryCreate = false)
    {
        if (empty($path)) {
            return false;
        }

        // If path doesn't exist and tryCreate is true, attempt to create it
        if ($tryCreate && ! is_dir($path)) {
            try {
                mkdir($path, 0777, true);
                chmod($path, 0777);
                Log::debug('Created path for accessibility test', ['path' => $path]);
            } catch (\Throwable $e) {
                Log::debug('Could not create path', ['path' => $path, 'error' => $e->getMessage()]);
                return false;
            }
        }

        if (! is_dir($path)) {
            return false;
        }

        if (! is_writable($path)) {
            if ($tryCreate) {
                try {
                    chmod($path, 0777);
                    Log::debug('Fixed permissions on path', ['path' => $path]);
                } catch (\Throwable $e) {
                    Log::debug('Could not fix permissions', ['path' => $path, 'error' => $e->getMessage()]);
                    return false;
                }
            } else {
                return false;
            }
        }

        // Try actual write test to be sure
        $testFile = $path . DIRECTORY_SEPARATOR . '.pcs_write_test_' . uniqid();
        try {
            $result = file_put_contents($testFile, 'test');
            if ($result === false) {
                return false;
            }
            @unlink($testFile);
            return true;
        } catch (\Throwable $e) {
            Log::debug('Path accessibility write test failed', ['path' => $path, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get the most reliable save root based on configured preferences and actual accessibility
     */
    private function getReliableSaveRoot()
    {
        $preferredRoot = config('report.preferred_root');
        $fallbackRoot = storage_path('app' . DIRECTORY_SEPARATOR . config('report.fallback_subdir', 'reports'));
        $forcePreferred = config('report.force_preferred', false);

        // Try to use preferred with option to create if force_preferred is true
        if (!empty($preferredRoot)) {
            if ($this->isPathAccessible($preferredRoot, $forcePreferred)) {
                Log::info('Using preferred report root', ['path' => $preferredRoot, 'force_preferred' => $forcePreferred]);
                return $preferredRoot;
            }
        }

        // Fallback to local storage
        if (!empty($preferredRoot)) {
            Log::warning('Preferred path not accessible, using fallback', ['preferred' => $preferredRoot, 'fallback' => $fallbackRoot]);
        }

        if ($this->ensureDirectory($fallbackRoot)) {
            return $fallbackRoot;
        }

        // Last resort - temp directory
        $tempFallback = storage_path('app' . DIRECTORY_SEPARATOR . 'temp-reports');
        Log::warning('Using temp fallback', ['path' => $tempFallback]);
        $this->ensureDirectory($tempFallback);
        return $tempFallback;
    }

    /**
     * Auto-backup file dari path utama ke storage/app/reports
     * Digunakan untuk menjamin backup lokal ketika file disimpan ke Z:\ drive
     */
    private function backupFile($sourcePath, $type = 'pdf')
    {
        if (!file_exists($sourcePath)) {
            Log::warning('Backup source file not found', ['source' => $sourcePath]);
            return false;
        }

        try {
            $filename = basename($sourcePath);
            $sourceDir = dirname($sourcePath);

            // Month folder mapping
            $monthFolders = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];

            // Extract month from source path (e.g., Z:\PROD\REPORT PCS\csv\maret\ or Z:\PROD\REPORT PCS\excel\maret\)
            $monthExtracted = null;
            foreach ($monthFolders as $month) {
                if (preg_match('/' . preg_quote($month, '/') . '[\\\\\/]/', $sourceDir)) {
                    $monthExtracted = $month;
                    break;
                }
            }

            // Fallback to current month if not found
            $currentMonth = $monthExtracted ?? $monthFolders[(int) date('n') - 1];

            // Create backup path: storage/app/reports/{type}/{month}
            $backupDir = storage_path('app' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $currentMonth);

            // For PDF with customer subdirectories, preserve structure
            if ($type === 'pdf' && strpos($sourceDir, 'IC') !== false) {
                // Extract path like: .../pdf/maret/IC/2026-03-05 -> backup to storage/app/reports/pdf/maret/IC/2026-03-05
                if (preg_match('/IC[\\\\\/](\d{4}-\d{2}-\d{2})$/', $sourceDir, $matches)) {
                    $dateFolder = $matches[1];
                    $backupDir = storage_path('app' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $currentMonth . DIRECTORY_SEPARATOR . 'IC' . DIRECTORY_SEPARATOR . $dateFolder);
                }
            }

            // Ensure backup directory exists
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
                chmod($backupDir, 0777);
            }

            $backupPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

            // Copy file
            if (!copy($sourcePath, $backupPath)) {
                Log::warning('Failed to copy backup file', ['source' => $sourcePath, 'backup' => $backupPath]);
                return false;
            }

            // Fix permissions
            chmod($backupPath, 0777);

            Log::info('File backed up successfully', [
                'type' => $type,
                'month' => $currentMonth,
                'source' => $sourcePath,
                'backup' => $backupPath,
                'filename' => $filename
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Backup failed', [
                'type' => $type,
                'source' => $sourcePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
