INSERT INTO organization_properties (organization_id, property_key, property_value, property_type, created_at) VALUES
-- TechCorp properties
(1, 'company_address', 'Teknokent Mahallesi, Ankara', 'string', NOW()),
(1, 'employee_count', '150', 'integer', NOW()),
(1, 'founded_year', '2015', 'integer', NOW()),
(1, 'is_premium', 'true', 'boolean', NOW()),

-- DataFlow properties
(2, 'company_address', 'Bilkent Plaza, Ankara', 'string', NOW()),
(2, 'employee_count', '85', 'integer', NOW()),
(2, 'founded_year', '2018', 'integer', NOW()),
(2, 'is_premium', 'true', 'boolean', NOW()),

-- CloudBridge properties
(3, 'company_address', 'Çankaya Business Center, Ankara', 'string', NOW()),
(3, 'employee_count', '25', 'integer', NOW()),
(3, 'founded_year', '2022', 'integer', NOW()),
(3, 'is_premium', 'false', 'boolean', NOW()),
(3, 'trial_end_date', '2025-07-25', 'date', NOW());