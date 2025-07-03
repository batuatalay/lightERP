DROP TABLE IF EXISTS organization_properties;
CREATE TABLE organization_properties (
    org_property_id VARCHAR(36) PRIMARY KEY,
    organization_id VARCHAR(36) NOT NULL,
    property_key VARCHAR(100) NOT NULL,
    property_value TEXT,
    property_type ENUM('string', 'integer', 'boolean', 'date', 'json') DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_organization_id (organization_id),
    INDEX idx_property_key (property_key),
    UNIQUE KEY uk_org_property (organization_id, property_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE organization_properties 
ADD CONSTRAINT fk_org_properties_organization 
FOREIGN KEY (organization_id) REFERENCES organizations(organization_id) ON DELETE CASCADE;