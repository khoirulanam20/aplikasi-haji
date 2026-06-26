<?php

namespace Tests\Unit\Hajj;

use App\Support\Hajj\HajjExcelTemplateService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class HajjExcelTemplateServiceTest extends TestCase
{
    public function test_template_contains_expected_headers_and_sample_row(): void
    {
        $service = app(HajjExcelTemplateService::class);
        $path = tempnam(sys_get_temp_dir(), 'hajj-tpl-test').'.xlsx';
        $service->writeTo($path);

        $workbook = IOFactory::load($path);
        $sheet = $workbook->getActiveSheet();

        $this->assertSame('KD_PORSI', $sheet->getCell('B1')->getValue());
        $this->assertSame('NAMA', $sheet->getCell('C1')->getValue());
        $this->assertSame('KECAMATAN', $sheet->getCell('E1')->getValue());
        $this->assertSame('DESA', $sheet->getCell('F1')->getValue());
        $this->assertSame('1100000001', (string) $sheet->getCell('B2')->getValue());
        $this->assertSame('CONTOH NAMA', $sheet->getCell('C2')->getValue());
        $this->assertSame('BANSARI', $sheet->getCell('E2')->getValue());
        $this->assertSame('BALESARI', $sheet->getCell('F2')->getValue());

        $refSheet = $workbook->getSheetByName('Referensi Wilayah');
        $this->assertNotNull($refSheet);
        $this->assertSame('BANSARI', $refSheet->getCell('A2')->getValue());
        $this->assertSame('BALESARI', $refSheet->getCell('B2')->getValue());
        $this->assertSame('BANSARI', $refSheet->getCell('D2')->getValue());

        unlink($path);
    }
}
