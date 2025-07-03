DROP TABLE IF EXISTS permissions;
CREATE TABLE permissions (
    permission_id VARCHAR(36) PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_permission_type (type),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;