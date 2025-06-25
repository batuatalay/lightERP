DROP TABLE IF EXISTS organization_user_permissions;
CREATE TABLE organization_user_permissions (
    organization_id INT NOT NULL,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    level ENUM('0', '1', '2', '3') DEFAULT '0', 
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (organization_id, user_id, permission_id),
    FOREIGN KEY (organization_id, user_id) REFERENCES organization_user(organization_id, user_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE,
    
    INDEX idx_org_user (organization_id, user_id),
    INDEX idx_permission (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;