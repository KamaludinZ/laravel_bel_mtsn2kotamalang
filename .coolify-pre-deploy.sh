#!/bin/bash
# Coolify Pre-Deployment Fix Script
# This script runs BEFORE deployment to fix the .env directory issue

set -e

echo "=== Coolify Pre-Deployment Fix ==="
echo "Checking for corrupt .env directory..."

# Get the Coolify application UUID from environment
APP_UUID="${COOLIFY_APP_UUID:-p6ca3bpfyvdzx45qufqqjxfi}"
ENV_PATH="/data/coolify/applications/${APP_UUID}/.env"

# Check if .env exists as a directory
if [ -d "$ENV_PATH" ]; then
    echo "⚠️  WARNING: .env is a DIRECTORY (this causes deployment failure)"
    echo "📁 Path: $ENV_PATH"

    # Backup directory contents (if any)
    if [ "$(ls -A $ENV_PATH)" ]; then
        echo "📦 Backing up .env directory contents..."
        mkdir -p "/tmp/env_backup_$(date +%s)"
        cp -r "$ENV_PATH"/* "/tmp/env_backup_$(date +%s)/" || true
    fi

    # Remove the directory
    echo "🗑️  Removing corrupt .env directory..."
    rm -rf "$ENV_PATH"
    echo "✅ Removed corrupt .env directory"
else
    echo "✅ .env is not a directory (OK)"
fi

# Ensure parent directory exists and has correct permissions
PARENT_DIR="/data/coolify/applications/${APP_UUID}"
if [ -d "$PARENT_DIR" ]; then
    echo "🔧 Setting correct permissions on $PARENT_DIR"
    chmod 755 "$PARENT_DIR"
fi

echo "=== Pre-Deployment Fix Complete ==="
exit 0
