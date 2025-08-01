DROP TABLE IF EXISTS organizations;
CREATE TABLE organizations (
    organization_id VARCHAR(36) PRIMARY KEY,
    organization_name VARCHAR(255) NOT NULL,
    organization_slug VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('active', 'inactive', 'suspended', 'trial') DEFAULT 'trial',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_organization_slug (organization_slug),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;