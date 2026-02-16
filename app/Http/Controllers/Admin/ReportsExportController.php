<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicineRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsExportController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->user()?->can('view_reports'), 403);

        $format = strtolower((string) $request->query('format', 'csv'));
        $rows = $this->filteredQuery($request)
            ->with(['patient', 'hospital', 'currentPharmacy'])
            ->orderByDesc('created_at')
            ->get();

        return match ($format) {
            'xlsx' => $this->downloadExcelXml($rows),
            'pdf' => $this->downloadSimplePdf($rows),
            default => $this->downloadCsv($rows),
        };
    }

    private function filteredQuery(Request $request): Builder
    {
        return MedicineRequest::query()
            ->when($request->filled('dateFrom'), fn (Builder $q) => $q->whereDate('created_at', '>=', $request->string('dateFrom')->toString()))
            ->when($request->filled('dateTo'), fn (Builder $q) => $q->whereDate('created_at', '<=', $request->string('dateTo')->toString()))
            ->when($request->filled('hospitalId'), fn (Builder $q) => $q->where('hospital_id', $request->string('hospitalId')->toString()))
            ->when(
                $request->filled('pharmacyId'),
                fn (Builder $q) => $q->where(function (Builder $sub) use ($request): void {
                    $pharmacyId = $request->string('pharmacyId')->toString();
                    $sub->where('current_pharmacy_id', $pharmacyId)
                        ->orWhereHas('assignments', fn (Builder $a) => $a->where('pharmacy_id', $pharmacyId));
                })
            )
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->toString()));
    }

    private function exportRows($rows): array
    {
        return $rows->map(function (MedicineRequest $request): array {
            $phone = (string) ($request->patient->phone ?? '');
            $maskedPhone = strlen($phone) > 4 ? str_repeat('*', max(strlen($phone) - 4, 0)) . substr($phone, -4) : $phone;

            return [
                'request_id' => $request->id,
                'patient_name' => $request->patient->name ?? '',
                'patient_contact' => $maskedPhone,
                'hospital' => $request->hospital->name ?? '',
                'pharmacy' => $request->currentPharmacy->name ?? '',
                'status' => $request->status,
                'created_at' => optional($request->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($request->updated_at)->format('Y-m-d H:i:s'),
            ];
        })->all();
    }

    private function filename(string $extension): string
    {
        return 'reports_' . Carbon::now()->format('Ymd_His') . '.' . $extension;
    }

    private function downloadCsv($rows): StreamedResponse
    {
        $data = $this->exportRows($rows);
        $filename = $this->filename('csv');

        return response()->streamDownload(function () use ($data): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Request ID', 'Patient Name', 'Patient Contact', 'Hospital', 'Pharmacy', 'Status', 'Created At', 'Updated At']);
            foreach ($data as $row) {
                fputcsv($out, array_values($row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function downloadExcelXml($rows): StreamedResponse
    {
        $data = $this->exportRows($rows);
        $filename = $this->filename('xlsx');

        return response()->streamDownload(function () use ($data): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Request ID', 'Patient Name', 'Patient Contact', 'Hospital', 'Pharmacy', 'Status', 'Created At', 'Updated At']);
            foreach ($data as $row) {
                fputcsv($out, array_values($row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function downloadSimplePdf($rows)
    {
        $data = $this->exportRows($rows);
        $filename = $this->filename('pdf');

        $lines = ['TumaMed Reports Export', ''];
        foreach (array_slice($data, 0, 120) as $row) {
            $lines[] = sprintf(
                '%s | %s | %s | %s | %s | %s',
                $row['request_id'],
                $row['patient_name'],
                $row['hospital'],
                $row['pharmacy'],
                $row['status'],
                $row['created_at']
            );
        }

        $pdf = $this->buildSimplePdf($lines);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildSimplePdf(array $lines): string
    {
        $escape = static function (string $text): string {
            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        };

        $streamLines = ["BT", "/F1 10 Tf", "50 770 Td"];
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $streamLines[] = "0 -12 Td";
            }
            $streamLines[] = '(' . $escape($line) . ') Tj';
        }
        $streamLines[] = "ET";
        $stream = implode("\n", $streamLines) . "\n";

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj";
        $objects[] = "4 0 obj << /Length " . strlen($stream) . " >> stream\n{$stream}endstream endobj";
        $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}

