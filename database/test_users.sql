/*
===========================================================
 Church Management and Information System
 Test Users for Role-Based Access Testing

 This file creates test users for each role to test different views:
 1. Administrator - Full system access
 2. Pastor - Spiritual leader access
 3. Ministry Leader - Ministry-specific access
 4. Clerk - Administrative tasks
 5. Member - Basic read-only access

 All passwords are: test123
===========================================================
*/

USE church_system_database;

-- Password hash for 'test123' (using PHP password_hash)
-- $2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyY5Y5Y5Y5Y5Y

-- Clear existing test users (optional - comment out if you want to keep existing data)
-- DELETE FROM Users WHERE username LIKE 'test_%';

-- Insert test users for each role
-- Password for all: test123
INSERT IGNORE INTO Users (username, password, role, status) VALUES
-- Administrator (role_id = 1)
('test_admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyY5Y5Y5Y5Y5Y', 1, 'Active'),

-- Pastor (role_id = 2)
('test_pastor', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyY5Y5Y5Y5Y5Y', 2, 'Active'),

-- Ministry Leader (role_id = 3)
('test_leader', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyY5Y5Y5Y5Y5Y', 3, 'Active'),

-- Clerk (role_id = 4)
('test_clerk', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyY5Y5Y5Y5Y5Y', 4, 'Active'),

-- Member (role_id = 5)
('test_member', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyY5Y5Y5Y5Y5Y', 5, 'Active');

-- Verify the users were created
SELECT 
    u.id,
    u.username,
    r.role_name,
    u.status,
    'test123' as password_hint
FROM Users u
JOIN Roles r ON u.role = r.id
WHERE u.username LIKE 'test_%'
ORDER BY r.id;

