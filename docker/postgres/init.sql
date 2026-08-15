-- PostgreSQL initialization script for Laravel Bell System
-- This script runs only once when the database is first created

-- Set timezone
SET timezone = 'Asia/Jakarta';

-- Create extensions if needed
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE :POSTGRES_DB TO :POSTGRES_USER;

-- Log initialization
DO $$
BEGIN
    RAISE NOTICE 'PostgreSQL database initialized successfully for Laravel Bell System';
    RAISE NOTICE 'Database: %', current_database();
    RAISE NOTICE 'Version: %', version();
END $$;
