#!/bin/bash

# MODX CLI Integration Testing Setup Script
# This script helps set up the integration testing environment

set -e

echo "=========================================="
echo "MODX CLI Integration Testing Setup"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check for Docker
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed${NC}"
    echo "Please install Docker from https://docs.docker.com/get-docker/"
    exit 1
fi

# Check for Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Error: Docker Compose is not installed${NC}"
    echo "Please install Docker Compose from https://docs.docker.com/compose/install/"
    exit 1
fi

echo -e "${GREEN}✓ Docker and Docker Compose are installed${NC}"
echo ""

# Navigate to Integration directory
cd "$(dirname "$0")"
INTEGRATION_DIR=$(pwd)
PROJECT_ROOT="$(cd ../.. && pwd)"

echo "Integration tests directory: $INTEGRATION_DIR"
echo "Project root: $PROJECT_ROOT"
echo ""

# Load .env if present
if [ -f "$INTEGRATION_DIR/.env" ]; then
    echo "Loading integration environment from .env..."
    set -a
    # shellcheck disable=SC1091
    . "$INTEGRATION_DIR/.env"
    set +a
    echo -e "${GREEN}✓ Loaded .env${NC}"
    echo ""
fi

# Export required defaults if not set
export MODX_INTEGRATION_TESTS="${MODX_INTEGRATION_TESTS:-1}"
export MODX_TEST_INSTANCE_ALIAS="${MODX_TEST_INSTANCE_ALIAS:-test}"
export MODX_TEST_DB_HOST="${MODX_TEST_DB_HOST:-localhost}"
export MODX_TEST_DB_NAME="${MODX_TEST_DB_NAME:-modx_test}"
export MODX_TEST_DB_USER="${MODX_TEST_DB_USER:-root}"
export MODX_TEST_DB_PASS="${MODX_TEST_DB_PASS:-testpass}"

# Start Docker environment
echo "Starting Docker environment..."
docker-compose up -d

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
sleep 10

MAX_RETRIES=30
RETRY_COUNT=0

while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if docker-compose exec -T mysql mysqladmin ping -h localhost -u root -ptestpass &> /dev/null; then
        echo -e "${GREEN}✓ MySQL is ready${NC}"
        break
    fi
    
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "Waiting for MySQL... ($RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo -e "${RED}Error: MySQL failed to start${NC}"
    docker-compose logs mysql
    exit 1
fi

# Validate MODX instance path if provided
if [ -n "$MODX_TEST_INSTANCE_PATH" ]; then
    if [ ! -d "$MODX_TEST_INSTANCE_PATH" ]; then
        echo -e "${YELLOW}Warning: MODX_TEST_INSTANCE_PATH does not exist: $MODX_TEST_INSTANCE_PATH${NC}"
    elif [ ! -f "$MODX_TEST_INSTANCE_PATH/config.core.php" ]; then
        echo -e "${YELLOW}Warning: config.core.php not found in $MODX_TEST_INSTANCE_PATH${NC}"
    else
        echo -e "${GREEN}✓ MODX instance path looks valid${NC}"
    fi
    echo ""
fi

# Validate DB connectivity
if command -v mysqladmin &> /dev/null; then
    if mysqladmin ping -h "$MODX_TEST_DB_HOST" -u "$MODX_TEST_DB_USER" -p"$MODX_TEST_DB_PASS" &> /dev/null; then
        echo -e "${GREEN}✓ Database connection verified (${MODX_TEST_DB_HOST})${NC}"
    else
        echo -e "${YELLOW}Warning: Unable to verify database connection to ${MODX_TEST_DB_HOST}${NC}"
    fi
    echo ""
fi

echo ""
echo "=========================================="
echo "Environment Setup Complete!"
echo "=========================================="
echo ""
echo "Docker containers are running:"
docker-compose ps
echo ""
echo "Next steps:"
echo "1. Ensure you have a MODX 3.x test instance"
echo "2. Set environment variables:"
echo "   export MODX_INTEGRATION_TESTS=1"
echo "   export MODX_TEST_INSTANCE_PATH=/path/to/modx"
echo "   export MODX_TEST_DB_HOST=localhost"
echo "   export MODX_TEST_DB_NAME=modx_test"
echo "   export MODX_TEST_DB_USER=root"
echo "   export MODX_TEST_DB_PASS=testpass"
echo ""
echo "3. Run integration tests:"
echo "   cd $PROJECT_ROOT"
echo "   vendor/bin/phpunit --testsuite=Integration"
echo ""
echo "To stop the environment:"
echo "   cd $INTEGRATION_DIR"
echo "   docker-compose down"
echo ""
echo "To clean up everything including volumes:"
echo "   docker-compose down -v"
echo ""
