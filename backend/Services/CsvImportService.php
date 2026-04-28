<?php

namespace App\Services;

use PDO;
use Exception;

class CsvImportService {
    private PDO $db;
    private array $requiredColumns = [
        'MERC', 'DIGITO', 'DESCRICAO', 'EMBALAGEM', 
        'ESTOQ EMB1', 'ESTOQ EMB9', 'PRECO VENDA'
    ];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Import catalog data from a CSV file.
     *
     * @param string $filePath
     * @return array
     */
    public function import(string $filePath): array {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File not found.'];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Could not open file.'];
        }

        // Read header
        $header = fgetcsv($handle, 0, ';');
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'Empty CSV file.'];
        }

        // Map column names to indices
        $header = array_map(function($h) { return trim($h, " \t\n\r\0\x0B"); }, $header);
        $colMap = [];
        foreach ($this->requiredColumns as $col) {
            $index = array_search(trim($col), $header);
            if ($index === false) {
                fclose($handle);
                return [
                    'success' => false, 
                    'message' => "Coluna obrigatória ausente no CSV: $col",
                    'debug_header' => $header
                ];
            }
            $colMap[$col] = $index;
        }

        try {
            $this->db->beginTransaction();
            $this->db->exec("DELETE FROM catalog");

            $sql = "INSERT INTO catalog (merc, digito, descricao, embalagem, estoq_emb1, estoq_emb9, preco_venda) 
                    VALUES (:merc, :digito, :descricao, :embalagem, :estoq_emb1, :estoq_emb9, :preco_venda)";
            $stmt = $this->db->prepare($sql);

            $count = 0;
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (empty($row[0])) continue; // Skip empty rows

                $stmt->execute([
                    'merc' => (int)$row[$colMap['MERC']],
                    'digito' => (int)$row[$colMap['DIGITO']],
                    'descricao' => trim($row[$colMap['DESCRICAO']]),
                    'embalagem' => trim($row[$colMap['EMBALAGEM']]),
                    'estoq_emb1' => (int)$row[$colMap['ESTOQ EMB1']],
                    'estoq_emb9' => (int)$row[$colMap['ESTOQ EMB9']],
                    'preco_venda' => $this->parsePrice($row[$colMap['PRECO VENDA']])
                ]);
                $count++;
            }

            $this->db->commit();
            fclose($handle);

            return ['success' => true, 'count' => $count];
        } catch (Exception $e) {
            $this->db->rollBack();
            fclose($handle);
            return ['success' => false, 'message' => 'Import error: ' . $e->getMessage()];
        }
    }

    /**
     * Parse PT-BR price string (e.g. "6,90") to float.
     */
    private function parsePrice(string $price): float {
        $price = str_replace(',', '.', trim($price));
        return (float)$price;
    }
}
