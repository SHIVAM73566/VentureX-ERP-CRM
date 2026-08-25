-- =============================================================================
-- MyERP database least-privilege setup (MySQL 8)
--
-- Run as root / a user with CREATE USER + GRANT privileges:
--     mysql -u root -p < database/security/least_privilege.sql
--
-- This creates a dedicated application user with only the privileges the
-- app actually needs, and revokes anything broader.
-- =============================================================================

-- The application user must NEVER be root, and must NOT have FILE, SUPER,
-- GRANT OPTION, or global SELECT. Only the specific database is granted.

CREATE USER IF NOT EXISTS 'my_erp_app'@'localhost' IDENTIFIED BY 'REPLACE_WITH_A_STRONG_PASSWORD';
CREATE USER IF NOT EXISTS 'my_erp_app'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_A_STRONG_PASSWORD';

-- Core CRUD + transaction usage on the application database only.
GRANT SELECT, INSERT, UPDATE, DELETE
    ON `my_erp`.*
    TO 'my_erp_app'@'localhost', 'my_erp_app'@'127.0.0.1';

-- Schema changes are performed by an ADMIN/CI user, not the app user.
-- During deployments only, run migrations with a separate admin user, e.g.:
--   CREATE USER 'my_erp_admin'@'localhost' IDENTIFIED BY '<strong>';
--   GRANT ALL PRIVILEGES ON `my_erp`.* TO 'my_erp_admin'@'localhost';
--   (run: php artisan migrate)
--   REVOKE DDL, TRIGGER, EVENT, ALTER, CREATE, DROP FROM 'my_erp_admin'@'localhost';

-- Explicitly revoke dangerous global privileges (belt and braces).
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'my_erp_app'@'localhost';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'my_erp_app'@'127.0.0.1';

FLUSH PRIVILEGES;

-- Verify with:
--   SHOW GRANTS FOR 'my_erp_app'@'localhost';
-- Expected: only GRANT SELECT, INSERT, UPDATE, DELETE ON `my_erp`.*
