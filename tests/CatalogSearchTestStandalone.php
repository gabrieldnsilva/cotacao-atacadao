<?php
/**
 * Standalone Test for Catalog Search
 * Pattern based on HealthCheckTest.php
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\CatalogModel;
use App\Core\Database;

function testCatalogSearch() {
    $db = Database::getInstance();
    $model = new CatalogModel($db);

    // Setup Test Data
    $db->exec("DELETE FROM catalog");
    $db->exec("INSERT INTO catalog (merc, digito, descricao, embalagem, preco_venda) 
               VALUES (99999, 1, 'PRODUTO TESTE SEARCH', 'UN', 10.00)");

    // Test Case 1: Search by MERC
    $results = $model->search('99999');
    if (count($results) !== 1 || $results[0]['descricao'] !== 'PRODUTO TESTE SEARCH') {
        echo "FAIL: Search by MERC failed\n";
        print_r($results);
        exit(1);
    }

    // Test Case 2: Search by Description (Partial)
    $results = $model->search('SEARCH');
    if (count($results) !== 1 || $results[0]['merc'] != 99999) {
        echo "FAIL: Search by Description failed\n";
        print_r($results);
        exit(1);
    }

    // Test Case 3: Case Insensitivity (if applicable)
    $results = $model->search('search');
    if (count($results) !== 1) {
        echo "FAIL: Case insensitive search failed\n";
        exit(1);
    }

    echo "PASS: Catalog search tests passed!\n";
}

try {
    testCatalogSearch();
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
