<?php

namespace App\Controllers;

use App\Services\CsvImportService;
use App\Services\AuthService;

class CatalogController {
    private CsvImportService $csvService;
    private AuthService $authService;

    public function __construct(CsvImportService $csvService, AuthService $authService) {
        $this->csvService = $csvService;
        $this->authService = $authService;
    }

    /**
     * Handle CSV upload for catalog update.
     */
    public function upload() {
        if (!$this->authService->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Não autorizado.']);
            return;
        }

        if (!isset($_FILES['catalog_csv'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado.']);
            return;
        }

        $file = $_FILES['catalog_csv'];

        // Basic validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo.']);
            return;
        }

        $result = $this->csvService->import($file['tmp_name']);

        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(422);
            echo json_encode($result);
        }
    }
}
