<?php

use PHPUnit\Framework\TestCase;
use App\Services\CsvImportService;
use App\Core\Database;

class CsvImportServiceTest extends TestCase {
    private $db;
    private $csvService;
    private $tempCsv;

    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->csvService = new CsvImportService($this->db);
        
        // Setup table
        $this->db->exec("TRUNCATE TABLE catalog");

        // Create a temporary CSV file for testing
        $this->tempCsv = tempnam(sys_get_temp_dir(), 'csv_test');
        $content = "FILIAL;MERC;DIGITO;DESCRICAO;EMBALAGEM;ESTOQ EMB1;ESTOQ EMB9;PRECO VENDA;OTHER\n";
        $content .= "945;13263;172;PRODUTO TESTE;CXA 1 X 1;10;5;6,90;ignore\n";
        $content .= "945;544;135;OUTRO PRODUTO;UND 1 X 1;2;0;15,50;ignore\n";
        file_put_contents($this->tempCsv, $content);
    }

    protected function tearDown(): void {
        if (file_exists($this->tempCsv)) {
            unlink($this->tempCsv);
        }
    }

    public function testImportSuccess() {
        $result = $this->csvService->import($this->tempCsv);
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['count']);

        // Verify data in DB
        $stmt = $this->db->query("SELECT * FROM catalog WHERE merc = 13263");
        $item = $stmt->fetch();
        $this->assertEquals(6.90, $item['preco_venda']);
        $this->assertEquals('PRODUTO TESTE', $item['descricao']);
    }

    public function testImportFailsWithMissingColumns() {
        $invalidCsv = tempnam(sys_get_temp_dir(), 'csv_invalid');
        file_put_contents($invalidCsv, "WRONG;COLUMNS\n1;2");
        
        $result = $this->csvService->import($invalidCsv);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Missing columns', $result['message']);
        
        unlink($invalidCsv);
    }
}
