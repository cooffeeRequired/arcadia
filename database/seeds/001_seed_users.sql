INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES
('Admin User', 'admin@arcadia.cz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE, NOW(), NOW()),
('Manager User', 'manager@arcadia.cz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', TRUE, NOW(), NOW()),
('Sales User', 'sales@arcadia.cz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', TRUE, NOW(), NOW()); 