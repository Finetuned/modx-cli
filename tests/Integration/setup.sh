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
