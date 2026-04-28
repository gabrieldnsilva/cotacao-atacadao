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
            return ['success' => false, 'message' => 'Arquivo físico não encontrado no servidor.'];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Não foi possível abrir o arquivo para leitura.'];
        }

        // Read header
        $header = fgetcsv($handle, 0, ';');
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'O arquivo CSV está vazio ou o delimitador (;) não foi reconhecido.'];
        }

        // Map column names to indices
        $header = array_map(function($h) { 
            return strtoupper(trim($h, " \t\n\r\0\x0B")); 
        }, $header);
        
        $colMap = [];
        foreach ($this->requiredColumns as $col) {
            $index = array_search(strtoupper(trim($col)), $header);
            if ($index === false) {
                fclose($handle);
                return [
                    'success' => false, 
                    'message' => "Coluna obrigatória ausente no CSV: $col",
                    'debug' => [
                        'received_headers' => $header,
                        'required_column' => $col
                    ]
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
            $lineNumber = 1;
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;
                // Skip if the row doesn't have enough columns or is empty
                if (count($row) < count($colMap)) continue;
                if (trim($row[$colMap['MERC']]) === '') continue;

                $stmt->execute([
                    'merc' => (int)trim($row[$colMap['MERC']]),
                    'digito' => (int)trim($row[$colMap['DIGITO']]),
                    'descricao' => trim($row[$colMap['DESCRICAO']]),
                    'embalagem' => trim($row[$colMap['EMBALAGEM']]),
                    'estoq_emb1' => (int)trim($row[$colMap['ESTOQ EMB1']]),
                    'estoq_emb9' => (int)trim($row[$colMap['ESTOQ EMB9']]),
                    'preco_venda' => $this->parsePrice($row[$colMap['PRECO VENDA']])
                ]);
                $count++;
            }

            if ($count === 0) {
                $this->db->rollBack();
                fclose($handle);
                return ['success' => false, 'message' => 'Nenhum dado válido foi processado do arquivo. Verifique o formato das linhas.'];
            }

            $this->db->commit();
            fclose($handle);

            return ['success' => true, 'count' => $count];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            fclose($handle);
            return ['success' => false, 'message' => 'Erro na inserção de dados: ' . $e->getMessage()];
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
