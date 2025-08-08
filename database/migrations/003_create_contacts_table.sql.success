CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    type VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NULL,
    contact_date DATETIME NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'completed',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer_id (customer_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_contact_date (contact_date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 