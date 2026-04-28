-- Initial Migration: Schema for Cotação Online
-- Date: 2026-04-28

-- Table for imported catalog data
CREATE TABLE IF NOT EXISTS `catalog` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `merc` INT NOT NULL COMMENT 'Product code',
    `digito` INT NOT NULL COMMENT 'Verification digit',
    `descricao` VARCHAR(255) NOT NULL,
    `embalagem` VARCHAR(100),
    `estoq_emb1` INT DEFAULT 0,
    `estoq_emb9` INT DEFAULT 0,
    `preco_venda` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_merc` (`merc`),
    INDEX `idx_descricao` (`descricao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for administrative users
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial seed: Default admin user
-- Password is 'admin123' hashed with password_hash()
INSERT INTO `admin_users` (`username`, `password`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE `username` = `username`;
