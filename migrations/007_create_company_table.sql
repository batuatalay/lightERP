DROP TABLE IF EXISTS companies;
CREATE TABLE companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id INT NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    tax_number VARCHAR(50),
    tax_office VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    discount DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(organization_id) ON DELETE CASCADE,
    
    INDEX idx_organization_id (organization_id),
    INDEX idx_code (code),
    INDEX idx_name (name),
    INDEX idx_status (status),
    INDEX idx_tax_number (tax_number),
    
    INDEX idx_org_status (organization_id, status),
    INDEX idx_org_name (organization_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;