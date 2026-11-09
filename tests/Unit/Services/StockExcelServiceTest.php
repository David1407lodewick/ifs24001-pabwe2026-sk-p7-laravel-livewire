<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockExcelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockExcelService(new StockRepository());
    }

    public function test_build_spreadsheet_and_export_response(): void
    {
        $user = User::factory()->create();
        $stocks = Stock::factory()->count(2)->create(['user_id' => $user->id]);

        $spreadsheet = $this->service->buildSpreadsheet($stocks, 'Custom Stocks');
        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals('Custom Stocks', $spreadsheet->getActiveSheet()->getTitle());

        $response = $this->service->exportToResponse($stocks, 'test.xlsx');
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        ob_start();
        $response->sendContent();
        $outputContent = ob_get_clean();
        $this->assertNotEmpty($outputContent);
    }

    public function test_import_spreadsheet(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'SKU');
        $sheet->setCellValue('D1', 'Description');
        $sheet->setCellValue('E1', 'Quantity');
        $sheet->setCellValue('F1', 'Unit');
        $sheet->setCellValue('G1', 'Price');
        $sheet->setCellValue('H1', 'Min Stock');
        $sheet->setCellValue('I1', 'Status');

        // Row 2: Standard stock
        $sheet->setCellValue('A2', '1');
        $sheet->setCellValue('B2', 'Imported Stock 1');
        $sheet->setCellValue('C2', 'SKU-01');
        $sheet->setCellValue('D2', 'Desc 1');
        $sheet->setCellValue('E2', '15');
        $sheet->setCellValue('F2', 'box');
        $sheet->setCellValue('G2', '50000');
        $sheet->setCellValue('H2', '5');
        $sheet->setCellValue('I2', 'available');

        // Row 3: Status invalid fallback to fromQuantity, dash values
        $sheet->setCellValue('A3', '2');
        $sheet->setCellValue('B3', 'Imported Stock 2');
        $sheet->setCellValue('C3', '-');
        $sheet->setCellValue('D3', '-');
        $sheet->setCellValue('E3', '0');
        $sheet->setCellValue('F3', '');
        $sheet->setCellValue('G3', '-');
        $sheet->setCellValue('H3', 'not-a-number');
        $sheet->setCellValue('I3', 'unknown_status');

        // Row 4: Column B empty, fallback to Column A
        $sheet->setCellValue('A4', 'Stock from Col A');
        $sheet->setCellValue('B4', '');
        $sheet->setCellValue('C4', 'SKU-A');
        $sheet->setCellValue('D4', 'Col A Desc');
        $sheet->setCellValue('E4', '3');
        $sheet->setCellValue('F4', 'pcs');
        $sheet->setCellValue('G4', '10000');
        $sheet->setCellValue('H4', '5');
        $sheet->setCellValue('I4', 'low_stock');

        // Row 5: Duplicate header 'No' / 'Name' (ignored)
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Name');
        $sheet->setCellValue('C5', 'Skip this');

        $tempPath = storage_path('app/test_excel_'.uniqid().'.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $result = $this->service->import($tempPath, $user->id);
        @unlink($tempPath);

        $this->assertEquals(3, $result['imported']);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Imported Stock 1',
            'sku' => 'SKU-01',
            'quantity' => 15,
            'unit' => 'box',
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Imported Stock 2',
            'sku' => null,
            'description' => null,
            'quantity' => 0,
            'unit' => 'pcs',
            'price' => null,
            'status' => 'out_of_stock',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Stock from Col A',
            'status' => 'low_stock',
        ]);
    }
}

