<?php

namespace App\Controllers;

use App\Services\CsvImportService;
use App\Services\AuthService;
use App\Models\CatalogModel;

class CatalogController {
    private CsvImportService $csvService;
    private AuthService $authService;
    private CatalogModel $catalogModel;

    public function __construct(CsvImportService $csvService, AuthService $authService, CatalogModel $catalogModel) {
        $this->csvService = $csvService;
        $this->authService = $authService;
        $this->catalogModel = $catalogModel;
    }

    /**
     * Search products in the catalog.
     */
    public function search() {
        $query = $_GET['search'] ?? '';
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        $results = $this->catalogModel->search($query);
        echo json_encode($results);
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
            echo json_encode([
                'success' => false, 
                'message' => 'Nenhum arquivo enviado ou limite de tamanho excedido.',
                'debug' => [
                    'post_size' => $_SERVER['CONTENT_LENGTH'] ?? 'unknown',
                    'upload_max' => ini_get('upload_max_filesize'),
                    'post_max' => ini_get('post_max_size')
                ]
            ]);
            return;
        }

        $file = $_FILES['catalog_csv'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            $errorMsg = $this->getUploadErrorMessage($file['error']);
            echo json_encode(['success' => false, 'message' => 'Erro no upload: ' . $errorMsg]);
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

    private function getUploadErrorMessage($code) {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE: return 'O arquivo excede o limite definido no php.ini (upload_max_filesize).';
            case UPLOAD_ERR_FORM_SIZE: return 'O arquivo excede o limite definido no formulário HTML.';
            case UPLOAD_ERR_PARTIAL: return 'O upload foi feito apenas parcialmente.';
            case UPLOAD_ERR_NO_FILE: return 'Nenhum arquivo foi enviado.';
            case UPLOAD_ERR_NO_TMP_DIR: return 'Pasta temporária ausente.';
            case UPLOAD_ERR_CANT_WRITE: return 'Falha ao escrever o arquivo no disco.';
            case UPLOAD_ERR_EXTENSION: return 'Uma extensão do PHP interrompeu o upload.';
            default: return 'Erro desconhecido.';
        }
    }
}
