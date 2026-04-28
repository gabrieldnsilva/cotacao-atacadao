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
                WHERE merc LIKE :query OR descricao LIKE :query 
                LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['query' => "%$query%"]);
        
        return $stmt->fetchAll();
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
