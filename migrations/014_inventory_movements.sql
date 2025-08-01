CREATE TABLE inventory_movements (
    movement_id VARCHAR(36) PRIMARY KEY,
    product_id VARCHAR(36) NOT NULL,
    warehouse_id VARCHAR(36) NOT NULL,
    movement_type ENUM('IN', 'OUT', 'TRANSFER', 'ADJUSTMENT') NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unit_cost DECIMAL(15,4),
    reference_type ENUM('PURCHASE', 'SALE', 'PRODUCTION', 'ADJUSTMENT', 'TRANSFER', 'INITIAL') NOT NULL,
    reference_id VARCHAR(36),
    notes TEXT,
    organization_id VARCHAR(36) NOT NULL,
    created_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (warehouse_id) REFERENCES inventory_warehouse(warehouse_id),
    INDEX idx_product (product_id),
    INDEX idx_organization (organization_id),
    INDEX idx_reference (reference_type, reference_id)
);