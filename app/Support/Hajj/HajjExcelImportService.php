<?php

namespace App\Support\Hajj;

use App\Models\HajjParticipant;
use App\Support\Settings\IntegrationConfigService;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class HajjExcelImportService
{
    private const PREVIEW_ROW_LIMIT = 100;

    public function __construct(private readonly HajjParticipantUserProvisioner $provisioner) {}

    /**
     * @return array{
     *     tahun_haji: int,
     *     sheet: string,
     *     header_row: int,
     *     mapped_columns: array<string, string>,
     *     stats: array{total: int, ready: int, duplicate: int, duplicate_database: int, duplicate_file: int, empty: int},
     *     rows: list<array{row: int, status: string, message: string|null, duplicate_source: string|null, data: array<string, string|null>}>
     * }
     */
    public function preview(string $filePath, int $tahunHaji): array
    {
        $context = $this->resolveContext($filePath, $tahunHaji);
        $parsed = $this->parseRows($context, $tahunHaji);

        return [
            'tahun_haji' => $tahunHaji,
            'sheet' => $context['sheet_name'],
            'header_row' => $context['header_row'],
            'mapped_columns' => $context['column_map'],
            'stats' => $parsed['stats'],
            'rows' => array_slice($parsed['rows'], 0, self::PREVIEW_ROW_LIMIT),
        ];
    }

    /**
     * @return array{imported: int, replaced: int, skipped: int, errors: list<string>}
     */
    public function import(
        string $filePath,
        int $tahunHaji,
        ?int $createdBy,
        string $duplicateAction = 'skip',
        ?callable $onProgress = null,
    ): array {
        $context = $this->resolveContext($filePath, $tahunHaji);
        $parsed = $this->parseRows($context, $tahunHaji);
        $imported = 0;
        $replaced = 0;
        $skipped = 0;
        $errors = [];
        $replaceDuplicates = $duplicateAction === 'replace';
        $total = $this->countProcessableRows($parsed['rows'], $replaceDuplicates);
        $handled = 0;

        if ($onProgress) {
            $onProgress(0, $total);
        }

        foreach ($parsed['rows'] as $entry) {
            if ($entry['status'] === 'duplicate') {
                if ($replaceDuplicates && ($entry['duplicate_source'] ?? '') === 'database') {
                    try {
                        $this->replaceExisting($tahunHaji, $entry['data'], $createdBy);
                        $replaced++;
                    } catch (\Throwable $e) {
                        $errors[] = "Baris {$entry['row']}: ".$this->formatImportError($e);
                    }

                    $handled++;
                    if ($onProgress) {
                        $onProgress($handled, $total);
                    }

                    continue;
                }

                $skipped++;

                continue;
            }

            if ($entry['status'] !== 'ready') {
                continue;
            }

            $rowData = $entry['data'];
            $row = $entry['row'];

            try {
                DB::transaction(function () use ($rowData, $tahunHaji, $createdBy) {
                    $participant = HajjParticipant::create([
                        'tahun_haji' => $tahunHaji,
                        'nomor_porsi' => $rowData['nomor_porsi'],
                        'nama' => $rowData['nama'],
                        'alamat' => $rowData['alamat'],
                        'desa' => $rowData['desa'],
                        'kecamatan' => $rowData['kecamatan'],
                        'telepon' => $rowData['telepon'],
                        'kloter' => $rowData['kloter'],
                        'rombongan' => $rowData['rombongan'],
                        'regu' => $rowData['regu'],
                        'created_by' => $createdBy,
                    ]);

                    $this->provisioner->provision($participant);
                });

                $imported++;
            } catch (\Throwable $e) {
                if ($this->isDuplicateKeyViolation($e)) {
                    if ($replaceDuplicates) {
                        try {
                            $this->replaceExisting($tahunHaji, $rowData, $createdBy);
                            $replaced++;
                        } catch (\Throwable $replaceError) {
                            $errors[] = "Baris {$row}: ".$this->formatImportError($replaceError);
                        }
                    } else {
                        $skipped++;
                    }

                    $handled++;
                    if ($onProgress) {
                        $onProgress($handled, $total);
                    }

                    continue;
                }

                $errors[] = "Baris {$row}: ".$this->formatImportError($e);
            }

            $handled++;
            if ($onProgress) {
                $onProgress($handled, $total);
            }
        }

        return compact('imported', 'replaced', 'skipped', 'errors');
    }

    /**
     * @return array{
     *     sheet: Worksheet,
     *     sheet_name: string,
     *     header_row: int,
     *     column_map: array<string, string>,
     *     highest_row: int
     * }
     */
    private function resolveContext(string $filePath, int $tahunHaji): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheetName = $this->sheetNameForYear($tahunHaji);
        $sheet = $sheetName ? $spreadsheet->getSheetByName($sheetName) : $spreadsheet->getSheet(0);

        if (! $sheet) {
            throw new RuntimeException("Sheet tidak ditemukan untuk tahun {$tahunHaji}.");
        }

        $headerRow = $this->findHeaderRow($sheet, $tahunHaji);
        if ($headerRow === null) {
            throw new RuntimeException('Baris header tidak ditemukan di file Excel.');
        }

        $columnMap = $this->buildColumnMap($sheet, $headerRow, $tahunHaji);
        if (! isset($columnMap['nama'])) {
            throw new RuntimeException('Kolom NAMA tidak ditemukan di file Excel.');
        }

        return [
            'sheet' => $sheet,
            'sheet_name' => $sheet->getTitle(),
            'header_row' => $headerRow,
            'column_map' => $columnMap,
            'highest_row' => $sheet->getHighestRow(),
        ];
    }

    /**
     * @param  array{sheet: Worksheet, header_row: int, column_map: array<string, string>, highest_row: int}  $context
     * @return array{
     *     stats: array{total: int, ready: int, duplicate: int, duplicate_database: int, duplicate_file: int, empty: int},
     *     rows: list<array{row: int, status: string, message: string|null, duplicate_source: string|null, data: array<string, string|null>}>
     * }
     */
    private function parseRows(array $context, int $tahunHaji): array
    {
        $stats = ['total' => 0, 'ready' => 0, 'duplicate' => 0, 'duplicate_database' => 0, 'duplicate_file' => 0, 'empty' => 0];
        $rows = [];
        $seenInFile = [];

        for ($row = $context['header_row'] + 1; $row <= $context['highest_row']; $row++) {
            if ($this->isInlineHeaderRow($context['sheet'], $row, $tahunHaji)) {
                $newMap = $this->buildColumnMap($context['sheet'], $row, $tahunHaji);
                if (isset($newMap['nama'])) {
                    $context['column_map'] = array_merge($context['column_map'], $newMap);
                }

                continue;
            }

            $rowData = $this->extractRow($context['sheet'], $row, $context['column_map']);

            if ($rowData === null) {
                $stats['empty']++;

                continue;
            }

            $stats['total']++;

            $duplicateInfo = $this->duplicateInfo($tahunHaji, $rowData, $seenInFile, $row);

            if ($duplicateInfo !== null) {
                $rows[] = [
                    'row' => $row,
                    'status' => 'duplicate',
                    'message' => $duplicateInfo['message'],
                    'duplicate_source' => $duplicateInfo['source'],
                    'data' => $rowData,
                ];
                $stats['duplicate']++;
                $stats[$duplicateInfo['source'] === 'database' ? 'duplicate_database' : 'duplicate_file']++;

                continue;
            }

            $identityKey = $this->rowIdentityKey($rowData);
            if ($identityKey !== null) {
                $seenInFile[$identityKey] = $row;
            }

            $rows[] = [
                'row' => $row,
                'status' => 'ready',
                'message' => null,
                'duplicate_source' => null,
                'data' => $rowData,
            ];
            $stats['ready']++;
        }

        return compact('stats', 'rows');
    }

    private function sheetNameForYear(int $tahunHaji): ?string
    {
        return match ($tahunHaji) {
            2023 => 'Sheet2',
            default => null,
        };
    }

    private function findHeaderRow(Worksheet $sheet, int $tahunHaji): ?int
    {
        $markers = $this->headerMarkersForYear($tahunHaji);

        for ($row = 1; $row <= 10; $row++) {
            $headers = $this->rowHeaders($sheet, $row);
            $matches = 0;

            foreach ($markers as $marker) {
                foreach ($headers as $header) {
                    if ($this->headerMatches($header, $marker)) {
                        $matches++;
                        break;
                    }
                }
            }

            if ($matches >= min(2, count($markers))) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function headerMarkersForYear(int $tahunHaji): array
    {
        $porsiMarkers = ['kd_porsi', 'no porsi'];

        return match ($tahunHaji) {
            2020 => ['nama', 'alamat'],
            2022 => array_merge($porsiMarkers, ['nama']),
            2023 => array_merge($porsiMarkers, ['nama', 'alamat']),
            2024 => array_merge($porsiMarkers, ['nama']),
            2025 => ['nama', 'kloter'],
            default => array_merge(['nama'], $porsiMarkers),
        };
    }

    /**
     * @return array<string, string>
     */
    private function rowHeaders(Worksheet $sheet, int $row): array
    {
        $headers = [];
        $highestColumn = $sheet->getHighestColumn();
        $colIndex = 1;

        while ($colIndex <= Coordinate::columnIndexFromString($highestColumn)) {
            $letter = Coordinate::stringFromColumnIndex($colIndex);
            $value = trim((string) $sheet->getCell($letter.$row)->getValue());
            if ($value !== '') {
                $headers[$letter] = $this->normalizeHeader($value);
            }
            $colIndex++;
        }

        return $headers;
    }

    /**
     * @return array<string, string>
     */
    private function buildColumnMap(Worksheet $sheet, int $headerRow, int $tahunHaji): array
    {
        $headers = $this->rowHeaders($sheet, $headerRow);
        $mapping = $this->fieldHeaderAliases($tahunHaji);
        $columnMap = [];

        foreach ($mapping as $field => $aliases) {
            foreach ($headers as $col => $header) {
                foreach ($aliases as $alias) {
                    if ($this->headerMatchesField($field, $header, $alias)) {
                        $columnMap[$field] = $col;
                        break 2;
                    }
                }
            }
        }

        return $columnMap;
    }

    /**
     * @return array<string, list<string>>
     */
    private function fieldHeaderAliases(int $tahunHaji): array
    {
        $common = [
            'nama' => ['nama', 'name'],
            'nomor_porsi' => ['kd_porsi', 'kd porsi', 'no porsi', 'noporsi', 'nomor porsi'],
            'alamat' => ['alamat', 'address'],
            'desa' => ['desa', 'nm_desa', 'nm desa', 'desa/kel', 'desa kel'],
            'kecamatan' => ['kecamatan', 'kabupaten'],
            'telepon' => ['no.telp/hp', 'no telp/hp', 'no telp', 'telepon', 'hp', 'telp'],
            'kloter' => ['kloter'],
            'rombongan' => ['romb', 'rombongan'],
            'regu' => ['regu'],
        ];

        if ($tahunHaji === 2025) {
            unset($common['nomor_porsi'], $common['alamat'], $common['desa'], $common['kecamatan'], $common['telepon']);
        }

        return $common;
    }

    /**
     * @param  array<string, string>  $columnMap
     * @return array<string, string|null>|null
     */
    private function extractRow(Worksheet $sheet, int $row, array $columnMap): ?array
    {
        $get = function (string $field) use ($sheet, $row, $columnMap): ?string {
            if (! isset($columnMap[$field])) {
                return null;
            }

            $value = trim((string) $sheet->getCell($columnMap[$field].$row)->getValue());

            return $value !== '' ? $value : null;
        };

        $nama = $get('nama');
        if ($nama === null) {
            return null;
        }

        if (preg_match('/^(no|n0|nama|alamat|kd[_ ]?porsi|no porsi)$/i', $nama)) {
            return null;
        }

        return [
            'nomor_porsi' => $this->sanitizeNomorPorsi($get('nomor_porsi')),
            'nama' => $nama,
            'alamat' => $get('alamat'),
            'desa' => $get('desa'),
            'kecamatan' => $get('kecamatan'),
            'telepon' => $get('telepon'),
            'kloter' => $get('kloter'),
            'rombongan' => $get('rombongan'),
            'regu' => $get('regu'),
        ];
    }

    /**
     * @param  array<string, int>  $seenInFile
     * @param  array<string, string|null>  $rowData
     * @return array{message: string, source: 'database'|'file'}|null
     */
    private function duplicateInfo(int $tahunHaji, array $rowData, array $seenInFile, int $row): ?array
    {
        if ($this->existsInDatabase($tahunHaji, $rowData)) {
            return [
                'message' => 'Sudah ada di database',
                'source' => 'database',
            ];
        }

        $identityKey = $this->rowIdentityKey($rowData);
        if ($identityKey !== null && isset($seenInFile[$identityKey])) {
            return [
                'message' => "Duplikat dalam file (sama dengan baris {$seenInFile[$identityKey]})",
                'source' => 'file',
            ];
        }

        return null;
    }

    /**
     * @param  list<array{status: string, duplicate_source?: string|null}>  $rows
     */
    private function countProcessableRows(array $rows, bool $replaceDuplicates): int
    {
        $count = 0;

        foreach ($rows as $entry) {
            if ($entry['status'] === 'ready') {
                $count++;

                continue;
            }

            if ($entry['status'] === 'duplicate' && $replaceDuplicates && ($entry['duplicate_source'] ?? '') === 'database') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<string, string|null>  $rowData
     */
    private function replaceExisting(int $tahunHaji, array $rowData, ?int $createdBy): void
    {
        $participant = $this->findExisting($tahunHaji, $rowData);

        if ($participant === null) {
            throw new RuntimeException('Peserta duplikat tidak ditemukan untuk diganti.');
        }

        DB::transaction(function () use ($participant, $rowData, $createdBy) {
            $participant->update([
                'nama' => $rowData['nama'],
                'alamat' => $rowData['alamat'],
                'desa' => $rowData['desa'],
                'kecamatan' => $rowData['kecamatan'],
                'telepon' => $rowData['telepon'],
                'kloter' => $rowData['kloter'],
                'rombongan' => $rowData['rombongan'],
                'regu' => $rowData['regu'],
                'created_by' => $createdBy ?? $participant->created_by,
            ]);

            if ($participant->user) {
                $domain = app(IntegrationConfigService::class)->hajjParticipantEmailDomain();
                $username = (string) $participant->user->username;

                $participant->user->update([
                    'name' => $rowData['nama'],
                    'is_active' => true,
                    'email' => $username !== '' ? $username.'@'.$domain : $participant->user->email,
                ]);
            } else {
                $this->provisioner->provision($participant->fresh());
            }
        });
    }

    /**
     * @param  array<string, string|null>  $rowData
     */
    private function findExisting(int $tahunHaji, array $rowData): ?HajjParticipant
    {
        $query = HajjParticipant::query()->with('user');

        if ($rowData['nomor_porsi']) {
            return $query
                ->where('tahun_haji', $tahunHaji)
                ->where('nomor_porsi', $rowData['nomor_porsi'])
                ->first();
        }

        return $query
            ->where('tahun_haji', $tahunHaji)
            ->where('nama', $rowData['nama'])
            ->where('alamat', $rowData['alamat'])
            ->first();
    }

    /**
     * @param  array<string, string|null>  $rowData
     */
    private function rowIdentityKey(array $rowData): ?string
    {
        if ($rowData['nomor_porsi']) {
            return 'porsi:'.$rowData['nomor_porsi'];
        }

        if ($rowData['nama'] && $rowData['alamat']) {
            return 'nama:'.$rowData['nama'].'|'.$rowData['alamat'];
        }

        return null;
    }

    /**
     * @param  array<string, string|null>  $rowData
     */
    private function existsInDatabase(int $tahunHaji, array $rowData): bool
    {
        if ($rowData['nomor_porsi']) {
            return HajjParticipant::query()
                ->where('tahun_haji', $tahunHaji)
                ->where('nomor_porsi', $rowData['nomor_porsi'])
                ->exists();
        }

        return HajjParticipant::query()
            ->where('tahun_haji', $tahunHaji)
            ->where('nama', $rowData['nama'])
            ->where('alamat', $rowData['alamat'])
            ->exists();
    }

    private function isInlineHeaderRow(Worksheet $sheet, int $row, int $tahunHaji): bool
    {
        $markers = $this->headerMarkersForYear($tahunHaji);
        $headers = array_values($this->rowHeaders($sheet, $row));
        $matches = 0;

        foreach ($markers as $marker) {
            foreach ($headers as $header) {
                if ($this->headerMatches($header, $marker)) {
                    $matches++;
                    break;
                }
            }
        }

        return $matches >= min(2, count($markers));
    }

    private function sanitizeNomorPorsi(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // ponytail: porsi haji biasanya >= 8 digit; 1–5 digit kemungkinan nomor urut baris Excel
        if (preg_match('/^\d{1,5}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function isDuplicateKeyViolation(\Throwable $e): bool
    {
        while ($e !== null) {
            $message = $e->getMessage();

            if (str_contains($message, '1062')
                || str_contains($message, 'Duplicate entry')
                || str_contains($message, 'UNIQUE constraint failed')) {
                return true;
            }

            $e = $e->getPrevious();
        }

        return false;
    }

    private function formatImportError(\Throwable $e): string
    {
        if ($this->isDuplicateKeyViolation($e)) {
            return 'Duplikat nomor porsi pada tahun ini';
        }

        $message = $e->getMessage();

        if (preg_match('/SQLSTATE\[\w+\]:\s*([^(]+)/', $message, $matches)) {
            return trim($matches[1]);
        }

        return strlen($message) > 160 ? substr($message, 0, 160).'…' : $message;
    }

    private function normalizeHeader(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    }

    private function headerMatches(string $header, string $alias): bool
    {
        $header = $this->normalizeHeader($header);
        $alias = $this->normalizeHeader($alias);

        if ($header === $alias) {
            return true;
        }

        // ponytail: cegah kolom "No" (urutan) tertangkap sebagai "no porsi"
        if (strlen($header) < 3 || strlen($alias) < 3) {
            return false;
        }

        return str_contains($header, $alias) || str_contains($alias, $header);
    }

    private function headerMatchesField(string $field, string $header, string $alias): bool
    {
        if (! $this->headerMatches($header, $alias)) {
            return false;
        }

        $normalized = $this->normalizeHeader($header);

        if ($field === 'nomor_porsi' && ! str_contains($normalized, 'porsi')) {
            return false;
        }

        if ($field === 'telepon' && ! preg_match('/telp|telepon|hp/', $normalized)) {
            return false;
        }

        return true;
    }
}
