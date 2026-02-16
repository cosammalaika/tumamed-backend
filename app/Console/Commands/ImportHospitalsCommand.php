<?php

namespace App\Console\Commands;

use App\Models\Hospital;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class ImportHospitalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tumamed:import-hospitals {--file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import hospitals from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle(AuditLogger $auditLogger): int
    {
        $file = $this->option('file');

        if (! $file || ! file_exists($file)) {
            $this->error('File not found.');
            return self::FAILURE;
        }

        $records = $this->readCsv($file);

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $records->chunk(500)->each(function (Collection $chunk) use (&$inserted, &$updated, &$skipped, &$errors) {
            DB::transaction(function () use ($chunk, &$inserted, &$updated, &$skipped, &$errors) {
                foreach ($chunk as $row) {
                    try {
                        $name = trim((string) ($row['name'] ?? ''));
                        $town = trim((string) ($row['town'] ?? ''));

                        if (! $name) {
                            $skipped++;
                            continue;
                        }

                        $data = [
                            'name' => $name,
                            'type' => $row['type'] ?? 'HOSPITAL',
                            'town' => $town,
                            'address' => $row['address'] ?? null,
                            'latitude' => $this->sanitizeCoordinate($row['lat'] ?? null, -90, 90),
                            'longitude' => $this->sanitizeCoordinate($row['lng'] ?? null, -180, 180),
                            'is_active' => $this->toBool($row['is_active'] ?? true),
                        ];

                        $hospital = Hospital::where('name', $name)
                            ->where('town', $town)
                            ->first();

                        if ($hospital) {
                            $hospital->update($data);
                            $updated++;
                        } else {
                            Hospital::create($data);
                            $inserted++;
                        }
                    } catch (\Throwable $e) {
                        $errors++;
                        report($e);
                    }
                }
            });
        });

        $auditLogger->log('ADMIN_IMPORT_HOSPITALS', [
            'file' => $file,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ], null, [
            'type' => 'SYSTEM',
            'name' => 'Console',
        ]);

        $this->info("Import finished. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}");

        return self::SUCCESS;
    }

    private function sanitizeCoordinate($value, float $min, float $max): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $numeric = round((float) $value, 7);

        return ($numeric >= $min && $numeric <= $max) ? $numeric : null;
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'y'], true);
    }

    private function readCsv(string $file): Collection
    {
        $handle = new SplFileObject($file);
        $handle->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        $headers = [];
        $rows = [];

        foreach ($handle as $index => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($index === 0) {
                $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $row);
                continue;
            }

            if (count($row) !== count($headers)) {
                continue;
            }

            $rows[] = array_combine($headers, $row);
        }

        return collect($rows);
    }
}
