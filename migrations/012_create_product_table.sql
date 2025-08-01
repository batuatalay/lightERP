CREATE TABLE inventory_products (
    product_id VARCHAR(36) PRIMARY KEY,
    organization_id VARCHAR(36) NOT NULL,
    category_id VARCHAR(36) NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    cost_price DECIMAL(15,4) DEFAULT 0.00,
    sale_price DECIMAL(15,4) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(36),
    
    INDEX idx_organization_id (organization_id),
    INDEX idx_category_id (category_id),
    INDEX idx_code (code),
    INDEX idx_name (name),
    INDEX idx_status (status),
    INDEX idx_updated_by (updated_by),
    
    FOREIGN KEY (organization_id) REFERENCES organizations(organization_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;