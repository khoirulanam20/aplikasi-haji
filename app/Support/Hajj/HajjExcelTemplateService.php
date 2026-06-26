<?php

namespace App\Support\Hajj;

use App\Support\Wilayah\KemendagriWilayahRepository;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HajjExcelTemplateService
{
    private const DATA_ROW_LIMIT = 500;

    private const REFERENSI_SHEET = 'Referensi Wilayah';

    public function __construct(private readonly KemendagriWilayahRepository $wilayah) {}

    /**
     * @return list<string>
     */
    public function headers(): array
    {
        return ['NO', 'KD_PORSI', 'NAMA', 'ALAMAT', 'KECAMATAN', 'DESA', 'NO.TELP/HP', 'KLOTER', 'ROMB', 'REGU'];
    }

    public function writeTo(string $path): void
    {
        $spreadsheet = new Spreadsheet;
        $dataSheet = $spreadsheet->getActiveSheet();
        $dataSheet->setTitle('Data');

        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle(self::REFERENSI_SHEET);

        $tree = $this->wilayah->tree();
        $refLastRow = $this->fillReferensiSheet($refSheet, $tree);
        $this->fillDataSheet($dataSheet, $tree);
        $this->applyWilayahValidations($dataSheet, $refLastRow, count($tree['kecamatan']));

        $spreadsheet->setActiveSheetIndex(0);

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    public function download(): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'hajj-tpl');
        $xlsxPath = $path.'.xlsx';
        rename($path, $xlsxPath);
        $this->writeTo($xlsxPath);

        return response()->download($xlsxPath, 'template-peserta-haji.xlsx')->deleteFileAfterSend();
    }

    /**
     * @param  array{kecamatan: list<array{kode: string, nama: string, desa: list<array{kode: string, nama: string}>}>}  $tree
     */
    private function fillReferensiSheet(Worksheet $sheet, array $tree): int
    {
        $sheet->setCellValue('A1', 'KECAMATAN');
        $sheet->setCellValue('B1', 'DESA/KELURAHAN');
        $sheet->setCellValue('D1', 'KECAMATAN');

        $row = 2;
        foreach ($tree['kecamatan'] as $kecamatan) {
            foreach ($kecamatan['desa'] as $desa) {
                $sheet->setCellValue('A'.$row, $kecamatan['nama']);
                $sheet->setCellValue('B'.$row, $desa['nama']);
                $row++;
            }
        }

        $uniqueRow = 2;
        foreach ($tree['kecamatan'] as $kecamatan) {
            $sheet->setCellValue('D'.$uniqueRow, $kecamatan['nama']);
            $uniqueRow++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        return $row - 1;
    }

    /**
     * @param  array{kecamatan: list<array{nama: string, desa: list<array{nama: string}>}>}  $tree
     */
    private function fillDataSheet(Worksheet $sheet, array $tree): void
    {
        foreach ($this->headers() as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'1', $header);
        }

        $sampleKecamatan = $tree['kecamatan'][0]['nama'] ?? '';
        $sampleDesa = $tree['kecamatan'][0]['desa'][0]['nama'] ?? '';

        $sample = [
            '1',
            '1100000001',
            'CONTOH NAMA',
            'Jl. Contoh RT01 RW01',
            $sampleKecamatan,
            $sampleDesa,
            '08123456789',
            '',
            '',
            '',
        ];

        foreach ($sample as $index => $value) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'2', $value);
        }

        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(22);
    }

    private function applyWilayahValidations(Worksheet $dataSheet, int $refLastRow, int $kecamatanCount): void
    {
        $ref = "'".self::REFERENSI_SHEET."'";
        $kecamatanRange = $ref.'!$D$2:$D$'.($kecamatanCount + 1);
        $desaFormula = sprintf(
            'OFFSET(%s!$B$1,MATCH($E2,%s!$A$2:$A$%d,0),0,COUNTIF(%s!$A$2:$A$%d,$E2),1)',
            $ref,
            $ref,
            $refLastRow,
            $ref,
            $refLastRow,
        );

        $this->applyListValidation($dataSheet, 'E2:E'.self::DATA_ROW_LIMIT, $kecamatanRange, 'Pilih kecamatan dari daftar Kemendagri.');
        $this->applyListValidation($dataSheet, 'F2:F'.self::DATA_ROW_LIMIT, $desaFormula, 'Pilih kecamatan dulu, lalu pilih desa/kelurahan.');
    }

    private function applyListValidation(
        Worksheet $sheet,
        string $cellRange,
        string $formula,
        string $prompt,
    ): void {
        $validation = new DataValidation;
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Wilayah');
        $validation->setPrompt($prompt);
        $validation->setFormula1($formula);

        $sheet->setDataValidation($cellRange, $validation);
    }
}
