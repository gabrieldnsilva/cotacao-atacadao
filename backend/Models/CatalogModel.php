<?php

namespace App\Models;

use PDO;

class CatalogModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Search products by MERC or Description.
     *
     * @param string $query
     * @return array
     */
    public function search(string $query): array {
        $sql = "SELECT merc, digito, descricao, embalagem, preco_venda 
                FROM catalog 
                WHERE merc LIKE :query_merc OR descricao LIKE :query_desc 
                LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'query_merc' => "%$query%",
            'query_desc' => "%$query%"
        ]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get catalog statistics (total items and last update).
     */
    public function getStats(): array {
        $sql = "SELECT COUNT(*) as total, MAX(updated_at) as last_update FROM catalog";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    /**
     * Get a single product by MERC and Digito.
     */
    public function getByMerc(int $merc, int $digito): ?array {
        $sql = "SELECT merc, digito, descricao, embalagem, preco_venda 
                FROM catalog 
                WHERE merc = :merc AND digito = :digito";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['merc' => $merc, 'digito' => $digito]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
