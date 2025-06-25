INSERT INTO organization_user_permissions (organization_id, user_id, permission_id, level, updated_at) VALUES

-- TechCorp Permissions
-- Ahmet (Admin) - Full permissions (level 3)
(1, 1, 1, 3, NOW()),  -- users: admin
(1, 1, 2, 3, NOW()),  -- organizations: admin
(1, 1, 3, 3, NOW()),  -- reports: admin
(1, 1, 4, 3, NOW()),  -- settings: admin
(1, 1, 5, 3, NOW()),  -- products: admin
(1, 1, 6, 3, NOW()),  -- customers: admin
(1, 1, 7, 3, NOW()),  -- orders: admin
(1, 1, 8, 3, NOW()),  -- invoices: admin
(1, 1, 9, 3, NOW()),  -- dashboard: admin
(1, 1, 10, 3, NOW()), -- analytics: admin

-- Elif (User) - Limited permissions
(1, 2, 1, 1, NOW()),  -- users: read
(1, 2, 3, 2, NOW()),  -- reports: write
(1, 2, 5, 2, NOW()),  -- products: write
(1, 2, 6, 2, NOW()),  -- customers: write
(1, 2, 7, 1, NOW()),  -- orders: read
(1, 2, 9, 1, NOW()),  -- dashboard: read

-- Mehmet (User) - Sales focused
(1, 3, 1, 1, NOW()),  -- users: read
(1, 3, 5, 1, NOW()),  -- products: read
(1, 3, 6, 3, NOW()),  -- customers: admin
(1, 3, 7, 3, NOW()),  -- orders: admin
(1, 3, 8, 2, NOW()),  -- invoices: write
(1, 3, 9, 1, NOW()),  -- dashboard: read

-- DataFlow Permissions
-- Zeynep (Admin) - Full permissions
(2, 4, 1, 3, NOW()),  -- users: admin
(2, 4, 2, 3, NOW()),  -- organizations: admin
(2, 4, 3, 3, NOW()),  -- reports: admin
(2, 4, 4, 3, NOW()),  -- settings: admin
(2, 4, 5, 3, NOW()),  -- products: admin
(2, 4, 6, 3, NOW()),  -- customers: admin
(2, 4, 7, 3, NOW()),  -- orders: admin
(2, 4, 8, 3, NOW()),  -- invoices: admin
(2, 4, 9, 3, NOW()),  -- dashboard: admin
(2, 4, 10, 3, NOW()), -- analytics: admin

-- Can (User) - Analytics focused
(2, 5, 3, 3, NOW()),  -- reports: admin
(2, 5, 9, 2, NOW()),  -- dashboard: write
(2, 5, 10, 3, NOW()), -- analytics: admin
(2, 5, 1, 1, NOW()),  -- users: read
(2, 5, 5, 1, NOW()),  -- products: read
(2, 5, 6, 1, NOW()),  -- customers: read

-- Selin (User) - Basic operations
(2, 6, 1, 1, NOW()),  -- users: read
(2, 6, 5, 2, NOW()),  -- products: write
(2, 6, 6, 2, NOW()),  -- customers: write
(2, 6, 7, 2, NOW()),  -- orders: write
(2, 6, 9, 1, NOW()),  -- dashboard: read

-- CloudBridge Permissions
-- Burak (Admin) - Full permissions
(3, 7, 1, 3, NOW()),  -- users: admin
(3, 7, 2, 3, NOW()),  -- organizations: admin
(3, 7, 3, 3, NOW()),  -- reports: admin
(3, 7, 4, 3, NOW()),  -- settings: admin
(3, 7, 5, 3, NOW()),  -- products: admin
(3, 7, 6, 3, NOW()),  -- customers: admin
(3, 7, 7, 3, NOW()),  -- orders: admin
(3, 7, 8, 3, NOW()),  -- invoices: admin
(3, 7, 9, 3, NOW()),  -- dashboard: admin
(3, 7, 10, 3, NOW()), -- analytics: admin

-- Ayşe (User) - Customer service focused
(3, 8, 1, 1, NOW()),  -- users: read
(3, 8, 6, 3, NOW()),  -- customers: admin
(3, 8, 7, 2, NOW()),  -- orders: write
(3, 8, 8, 1, NOW()),  -- invoices: read
(3, 8, 9, 1, NOW()),  -- dashboard: read

-- Emre (User) - Basic user
(3, 9, 1, 1, NOW()),  -- users: read
(3, 9, 5, 1, NOW()),  -- products: read
(3, 9, 6, 1, NOW()),  -- customers: read
(3, 9, 9, 1, NOW());  -- dashboard: read