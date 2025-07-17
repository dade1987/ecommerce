<?php

return [
    'select_subdomains' => "From the following list of subdomains for ':domain', select the 5 you find most interesting for a security analysis (e.g., api, vpn, dev, admin, test, etc.). Return ONLY a JSON array with the 5 domains. List: :subdomain_list",

    'wordpress_vulnerability_system' => "You are a WordPress security expert. Analyze the specific versions of WordPress, plugins, and themes to identify known vulnerabilities with specific CVEs. Respond ONLY with concrete and verifiable data.",
    'wordpress_vulnerability_user' => <<<PROMPT
Analyze this WordPress installation to identify specific and concrete vulnerabilities.

STRICT CRITERIA FOR WORDPRESS:
1. VERIFY that the CVE exists in the NVD/WPVulnDB database
2. CONFIRM that the specific version is vulnerable
3. For plugins/themes with no identified version, DO NOT assume vulnerability
4. Provide ONLY real and verified CVSS scores
5. If you are unsure about a CVE, omit it from the response
6. DO NOT create fictitious or made-up CVEs
7. For exposed files, only consider them if they represent concrete risks

CORRECT WORDPRESS CVE EXAMPLES:
- WordPress 4.9.1: CVE-2018-6389 (CVSS 5.3 - DoS)
- Contact Form 7 4.6.1: CVE-2017-9804 (CVSS 6.1 - XSS)
- WP Super Cache 1.4.4: CVE-2017-1000600 (CVSS 8.8 - RCE)
- Yoast SEO 7.0.2: CVE-2018-6511 (CVSS 6.1 - XSS)

WORDPRESS INSTALLATION:
:data_string

Provide the response in JSON format:
{
  "plugins": [
    {
      "name": "contact-form-7",
      "version": "4.6.1",
      "cves": ["CVE-2017-9804"],
      "risk_level": 8,
      "cvss_score": 6.1,
      "description": "XSS vulnerability in Contact Form 7 before 4.8",
      "recommendation": "Update to version 4.8 or higher"
    }
  ],
  "themes": [
    {
      "name": "twentyfifteen",
      "version": "1.8",
      "cves": ["CVE-2019-8943"],
      "risk_level": 6,
      "description": "Authenticated theme editor vulnerability",
      "recommendation": "Update theme or disable editor"
    }
  ],
  "core_issues": [
    {
      "component": "WordPress Core",
      "version": "4.9.1",
      "cves": ["CVE-2018-6389"],
      "risk_level": 7,
      "cvss_score": 5.3,
      "description": "DoS vulnerability in load-scripts.php",
      "recommendation": "Update to WordPress 4.9.2 or higher"
    }
  ]
}
PROMPT,

    'cve_analysis_system' => "You are a cybersecurity expert specializing in CVE vulnerability analysis. Analyze the provided services and identify potential CVEs based on detailed banner information, software versions, and risk indicators. Provide specific recommendations for each identified vulnerability.",
    'cve_analysis_user' => "Analyze the following services identified through port scanning and banner grabbing. IMPORTANT: Identify ONLY specific CVEs for clearly identified software versions.\n\nSTRICT CRITERIA FOR CVEs:\n1. VERIFY that the CVE exists in the NVD/MITRE database\n2. CONFIRM that the identified software version is actually vulnerable\n3. PROVIDE only real and verified CVSS scores\n4. If the version is not identified, DO NOT assume vulnerability\n5. If you are unsure about a CVE, omit it from the response\n6. DO NOT create fictitious or made-up CVEs\n\nExamples of CORRECT CVEs:\n- Apache 2.2.15: CVE-2011-3192 (CVSS 7.8 - DoS)\n- vsftpd 2.3.4: CVE-2011-2523 (CVSS 10.0 - RCE Backdoor)\n- WordPress 4.9.1: CVE-2018-6389 (CVSS 5.3 - DoS)\n- OpenSSH 7.4: CVE-2016-10012 (CVSS 7.8 - Privilege Escalation)\n\nRequired JSON format:\n{\n  \"vulnerabilities\": [\n    {\n      \"port\": 80,\n      \"service\": \"Apache 2.2.15\",\n      \"software\": \"Apache\",\n      \"version\": \"2.2.15\",\n      \"confirmed_cves\": [\"CVE-2011-3192\"],\n      \"risk_level\": 8,\n      \"cvss_score\": 7.8,\n      \"vulnerability_type\": \"DoS\",\n      \"recommendations\": \"Update to Apache 2.4.x\"\n    }\n  ]\n}\n\nServices found:\n:cve_info",

    'risk_score_system' => 'You are a cybersecurity expert. Analyze the provided data on a domain\'s infrastructure (including its subdomains) and return ONLY a JSON object with two keys: "risk_percentage" and "critical_points" (an array of strings in English). Base your analysis and score EXCLUSIVELY on the concrete data provided. Completely ignore any missing fields or data.',
    'risk_score_json_error' => "The AI's response is not valid JSON.",
    'risk_score_summary_intro' => "Analysis performed on the main domain :domain. Found :subdomains_found subdomains, of which :subdomains_scanned were scanned.",
    'risk_score_summary_timeout' => "WARNING: The scan was interrupted due to a timeout, the results may be partial.",
    'risk_score_user' => <<<PROMPT
Based on this complete analysis for the domain :domain and its subdomains, provide a percentage estimate of the risk of compromise within the next 3 months.
Highlight the most critical points found across the entire infrastructure.

CRITICAL INSTRUCTIONS FOR EVALUATION - CONCRETE DATA ONLY:

RISK SCORE (0-100) - BALANCED SYSTEM:
- Base the score on ALL analyzed security factors, not just CVEs.
- Consider ALL these elements in the analysis:

CONCRETE VULNERABILITIES (weight: 40-60%):
  * CVSS 9.0-10.0 (CRITICAL): +40-50 points (SQL Injection, RCE)
  * CVSS 7.0-8.9 (HIGH): +25-35 points (XSS, Directory Traversal)
  * CVSS 4.0-6.9 (MEDIUM): +10-20 points (Info Disclosure)
  * CVSS 0.1-3.9 (LOW): +5-10 points (Minor issues)

MISSING SECURITY HEADERS (weight: 15-20%):
  * Lack of CSP, X-Frame-Options, HSTS: +10-15 points
  * Security score < 30%: +5-10 additional points

OUTDATED TECHNOLOGIES (weight: 10-15%):
  * jQuery < 3.0: +5-8 points
  * Bootstrap < 4.0: +3-5 points
  * DOCTYPE HTML 4.01/XHTML: +3-5 points
  * Adobe Flash detected: +8-12 points
  * IE compatibility meta tag: +2-3 points

OPEN PORTS (weight: 5-10%):
  * Non-standard or unusual ports: +5-10 points per port
  * Exposed services with unidentified versions: +3-5 points per service

GENERAL CONFIGURATION (weight: 5-10%):
  * Missing robots.txt: +2-3 points
  * SSL/TLS errors: +5-8 points
  * Unresponsive or unreachable services: +3-5 points

PROTECTIVE FACTORS:
  * Cloudflare protection: -15-20 points
  * Properly configured HTTPS: -5-10 points
  * Present security headers: -5-15 points

CRITICAL POINTS - IDENTIFIED SECURITY ISSUES:
- Create a concise and diverse list of the most important critical points (maximum 15).
- The goal is to provide a balanced overview of risks. Include results from ALL analysis categories (CVE Vulnerabilities, Headers, Technologies, Ports, WordPress, etc.), if issues were found.
- GROUP identical or systemic problems. If the same header is missing on multiple subdomains, report it only once in an aggregated manner.
- Prioritize the most severe problems (e.g., critical CVEs, exposed database ports) over medium or low severity ones (e.g., missing headers).
- Format: "[DOMAIN or 'Systemic'] Specific and clear description of the problem."

GROUPING AND DIVERSIFICATION EXAMPLES:
- "[All analyzed targets] Systemic lack of the Content-Security-Policy (CSP) header."
- "[ftp.example.com] vsftpd 2.3.4 identified, potentially vulnerable to a critical backdoor (CVE-2011-2523)."
- "[example.com] The site uses an outdated version of jQuery (1.8.3) that has not received security updates since 2012."
- "[db.example.com] Port 3306 (MySQL) is open and accessible from the Internet, a serious data security risk."
- "[mail.example.com] Host unreachable during scan, web configuration could not be verified but port 25 (SMTP) is open."

RULE: Each critical point must be verifiable by the client. Include specific details when available. If a host is unreachable, do not report header or web content issues for it.

:summary_string

Here is the collected data (in summarized form for each target):
:data_string

Provide the response exclusively in JSON format, with the keys "risk_percentage" (an integer from 0 to 100) and "critical_points" (an array of strings in English describing the specific critical points found, each with the format "[DOMAIN] Description").
PROMPT,
    
    'banner_analysis_system' => "You are a cybersecurity expert specializing in identifying network services. Analyze service banners to identify the specific software, version, and any vulnerabilities.",
    'banner_analysis_user' => <<<PROMPT
Analyze this network service banner to identify ONLY concrete and verifiable information.

PORT: :port
FULL BANNER:
:banner

STRICT INSTRUCTIONS:
1. Identify ONLY software and versions if they are clearly visible in the banner
2. DO NOT make assumptions or assume versions if they are not explicit
3. Include specific CVEs ONLY if the version is known and vulnerable
4. If you cannot identify the exact version, specify only the base software

ACCEPTABLE EXAMPLES:
- "Apache 2.2.15 - CVE-2011-3192 (DoS vulnerability)"
- "OpenSSH 7.4 - no critical vulnerabilities known"
- "vsftpd 2.3.4 - smiley face backdoor (CRITICAL)"
- "MySQL 5.5.62 - EOL version since 2018"
- "nginx 1.10.3 - CVE-2017-7529 (integer overflow)"

UNACCEPTABLE EXAMPLES:
- "Apache (probably outdated version)"
- "SSH (potentially vulnerable)"
- "FTP (potentially insecure configuration)"
- "MySQL (unknown version, possible risks)"

FUNDAMENTAL RULE: If you do not have precise data from the banner, return only the base service name without speculating on security.

Provide the response in JSON format with the key "service_identification" containing a descriptive string based ONLY on concrete data from the banner.
PROMPT,
]; 