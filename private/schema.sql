CREATE TABLE IF NOT EXISTS pastes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    title TEXT NOT NULL,
    time_to_live INTEGER NOT NULL,
    content MEDIUMTEXT NOT NULL,
    ip_address VARCHAR(45) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fast listing / pagination by date
CREATE INDEX IF NOT EXISTS idx_pastes_created_at
    ON pastes (created_at);

-- Expiry cleanup: find non-permanent pastes whose TTL has elapsed
CREATE INDEX IF NOT EXISTS idx_pastes_expiry
    ON pastes (created_at, time_to_live);

CREATE TABLE IF NOT EXISTS mods (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    username TEXT NOT NULL UNIQUE,
    role VARCHAR(10) NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);