CREATE DATABASE IF NOT EXISTS `pixdb` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `pixdb`;

CREATE TABLE IF NOT EXISTS `pix` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `external_id` VARCHAR(100) NOT NULL UNIQUE,
  `payer_email` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `type` ENUM('NORMAL','RECORRENTE') NOT NULL,
  `status` ENUM('PENDENTE','APROVADO','EXPIRADO') NOT NULL,
  `origin` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `approved_at` DATETIME NULL,
  `expired_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
