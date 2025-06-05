CREATE TABLE organizations (
    organization_id INT AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(255) NOT NULL,
    organization_slug VARCHAR(100) UNIQUE NOT NULL, -- URL-friendly isim
    status ENUM('active', 'inactive', 'suspended', 'trial') DEFAULT 'trial',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL, -- Soft delete için
    
    -- İndeksler
    INDEX idx_organization_slug (organization_slug),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);