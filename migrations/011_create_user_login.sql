CREATE TABLE user_login (
    login_id VARCHAR(50) PRIMARY KEY,
    user_id VARCHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
    token TEXT NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    login_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_logins_user_id
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_user_logins_user_id ON user_logins(user_id);