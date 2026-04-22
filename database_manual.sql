-- Script SQL Manual untuk Database Bel Sekolah
-- Jalankan script ini di PostgreSQL container atau pgAdmin

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Table: users (dari Laravel Breeze)
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table: password_reset_tokens
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- Table: sessions
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id UUID NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions(last_activity);

-- Table: cache
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS cache_expiration_index ON cache(expiration);

-- Table: cache_locks
CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- Table: jobs
CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS jobs_queue_index ON jobs(queue);

-- Table: job_batches
CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

-- Table: failed_jobs
CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: audio_libraries
CREATE TABLE IF NOT EXISTS audio_libraries (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table: bell_types
CREATE TABLE IF NOT EXISTS bell_types (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    active_days JSONB NULL,
    is_automatic BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table: bell_schedules
CREATE TABLE IF NOT EXISTS bell_schedules (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    bell_type_id UUID NOT NULL,
    day VARCHAR(50) NOT NULL CHECK (day IN ('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')),
    time TIME NOT NULL,
    audio_library_id UUID NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (bell_type_id) REFERENCES bell_types(id) ON DELETE CASCADE,
    FOREIGN KEY (audio_library_id) REFERENCES audio_libraries(id) ON DELETE CASCADE
);

-- Table: settings
CREATE TABLE IF NOT EXISTS settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table: migrations (tracking)
CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- Insert migration records
INSERT INTO migrations (migration, batch) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2026_04_02_031701_create_audio_libraries_table', 1),
('2026_04_02_031758_create_bell_types_table', 1),
('2026_04_02_031849_create_bell_schedules_table', 1),
('2026_04_02_031942_create_settings_table', 1)
ON CONFLICT DO NOTHING;

-- Insert default admin user (password: password)
INSERT INTO users (id, name, email, password, created_at, updated_at) VALUES
(uuid_generate_v4(), 'Admin', 'admin@mtsn2kotamalang.sch.id', '$2y$12$LQv3c1yycH5aJlYkUjzLb.W1pv./QwqU5WI3/OmUgJF6G0QQqCfkS', NOW(), NOW())
ON CONFLICT (email) DO NOTHING;

-- Insert default settings
INSERT INTO settings (id, key, value, created_at, updated_at) VALUES
(uuid_generate_v4(), 'app_name', 'Bel Sekolah MTsN 2 Kota Malang', NOW(), NOW())
ON CONFLICT (key) DO NOTHING;

-- Success message
SELECT 'Database berhasil dibuat! Login dengan email: admin@mtsn2kotamalang.sch.id dan password: password' AS status;
