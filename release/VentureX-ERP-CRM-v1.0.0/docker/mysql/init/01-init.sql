-- VentureX ERP & CRM Database Initialization Script
-- This script runs automatically when the MySQL container starts for the first time

-- Create additional databases if needed
-- CREATE DATABASE IF NOT EXISTS venturex_test;

-- Create additional users if needed
-- CREATE USER IF NOT EXISTS 'venturex_readonly'@'%' IDENTIFIED BY 'readonly_password';
-- GRANT SELECT ON venturex_db.* TO 'venturex_readonly'@'%';

-- Set character set and collation
ALTER DATABASE venturex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant additional privileges (optional)
-- GRANT ALL PRIVILEGES ON venturex_db.* TO 'venturex_user'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Display completion message
SELECT 'VentureX database initialization completed successfully!' AS status;
