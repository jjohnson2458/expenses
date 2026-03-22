CREATE TABLE IF NOT EXISTS `recurring_expenses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NULL,
    `type` ENUM('debit', 'credit') DEFAULT 'debit',
    `description` VARCHAR(500) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `vendor` VARCHAR(255) NULL,
    `day_of_month` INT DEFAULT 1,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_processed` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_recurring_expenses_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recurring_expenses_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
