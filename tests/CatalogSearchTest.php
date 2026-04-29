<?php

use PHPUnit\Framework\TestCase;
use App\Models\CatalogModel;
use App\Core\Database;

class CatalogSearchTest extends TestCase {
    private $db;
    private $model;

    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->model = new CatalogModel($this->db);
        
        // Clean and seed for test
        $this->db->exec("DELETE FROM catalog");
        $this->db->exec("INSERT INTO catalog (merc, digito, descricao, embalagem, preco_venda) 
                         VALUES (12345, 6, 'PRODUTO TESTE', 'CX 10', 50.50)");
    }

    public function testSearchByMerc() {
        $results = $this->model->search('12345');
        $this->assertCount(1, $results);
        $this->assertEquals('PRODUTO TESTE', $results[0]['descricao']);
    }

    public function testSearchByDescription() {
        $results = $this->model->search('TESTE');
        $this->assertCount(1, $results);
        $this->assertEquals(12345, $results[0]['merc']);
    }

    public function testSearchNoResults() {
        $results = $this->model->search('INEXISTENTE');
        $this->assertCount(0, $results);
    }
}
