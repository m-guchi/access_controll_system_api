CREATE USER 'e_mgmt_user'@'localhost' IDENTIFIED BY 'DYVO0rCEcIkyX8kmrfzgvmOg';
GRANT SELECT, INSERT, UPDATE, DELETE ON e_mgmt.* to e_mgmt_user@localhost;

COMMIT;