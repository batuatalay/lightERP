DROP TABLE IF EXISTS contacts;
CREATE TABLE contacts (
    contact_id VARCHAR(36) PRIMARY KEY,
    company_id VARCHAR(36) NOT NULL,                            
    organization_id VARCHAR(36) NOT NULL,                       
    name VARCHAR(255) NOT NULL,                        
    phone VARCHAR(20),                                  
    mail VARCHAR(255),                                  
    address TEXT,                                       
    title VARCHAR(100),                                 
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(organization_id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_company_id (company_id),
    INDEX idx_organization_id (organization_id),
    INDEX idx_name (name),
    INDEX idx_phone (phone),
    INDEX idx_mail (mail),
    INDEX idx_status (status),
    
    -- Composite indexes
    INDEX idx_company_status (company_id, status),
    INDEX idx_org_status (organization_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;