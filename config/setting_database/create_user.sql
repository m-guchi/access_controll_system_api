CREATE USER 'test_user'@'localhost' IDENTIFIED BY 'DYVO0rCEcIkyX8kmrfzgvmOg';
GRANT SELECT, INSERT, UPDATE, DELETE ON app_access_controll_system.* to test_user@localhost;

COMMIT;