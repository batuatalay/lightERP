CREATE TABLE organization_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id CHAR(36) NOT NULL, -- UUID format to match organizations table
    property_key VARCHAR(100) NOT NULL,
    property_value TEXT,
    property_type ENUM('string', 'integer', 'boolean', 'date', 'json') DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint with UUID
    FOREIGN KEY (organization_id) REFERENCES organizations(organization_id) ON DELETE CASCADE,
    
    -- Composite unique index - bir org için aynı key sadece bir kez
    UNIQUE KEY uk_org_property (organization_id, property_key),
    
    -- Performance indexes
    INDEX idx_organization_id (organization_id),
    INDEX idx_property_key (property_key)
);