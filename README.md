# SECTF2026 — "You Play the CTF. We Play Defense"

**Event:** XPRO @ Talent Arena Barcelona, March 2 2026
**Organizer:** Schneider Electric
**Team:** 0x06
**Final score:** 12,600+ pts across 4 phases

Full technical log with commands and results: [NOTES.md](NOTES.md)

---

## The Challenge

21 teams, each assigned an identical WordPress site hosted on AWS. 90 minutes, 4 phases. Each phase builds on the previous one — you need what you found earlier to proceed.

### Phase 1 — Find the vulnerability (6,600 pts)

The site has a known security flaw. Exploit it to extract data from the database.

| Submit | Points |
|--------|--------|
| The admin's password hash | 2,500 |
| A bonus flag hidden in plain sight on the site | 100 |
| A secret flag stored in the database | 4,000 |

### Phase 2 — Get code execution (2,000 pts)

Using the password from Phase 1, log into the admin panel. Find a way to run your own code on the server and read a flag file from the `/opt` directory.

| Submit | Points |
|--------|--------|
| Contents of the flag file in `/opt` | 2,000 |

### Phase 3 — Pivot to the cloud (4,000 pts)

The site runs on AWS. From the server access gained in Phase 2, find cloud credentials, discover private storage buckets, and extract a flag hidden inside a PDF.

| Submit | Points |
|--------|--------|
| Flag hidden in a PDF in a private S3 bucket | 4,000 |

### Phase 4 — Attack other teams (dynamic scoring)

All 21 teams share the same AWS account. Using the cloud access from Phase 3, find other teams' infrastructure and tag it with your team identifier. Other teams can tag yours. Points accumulate until the game ends.

| Submit | Points |
|--------|--------|
| Tag other teams' AWS resources with your team ID | Dynamic |

---

## Repo structure

```
.
├── NOTES.md                    # Detailed writeup — phases, commands, flags, AWS details
│
├── exploits/                   # Attack scripts
│   ├── inject.py               # Webshell payload generator (plugin editor format)
│   ├── inject2.py              # Same, takes nonce as CLI arg (AJAX editor format)
│   ├── tag_all.py              # Phase 4: mass-tag all teams' App Runner services
│   └── tag_all_v2.py           # Same, alternate tag key format
│
├── artifacts/                  # Things captured from the target
│   ├── dpkg.pdf                # PDF from S3 bucket (Phase 3 flag hidden inside)
│   ├── hash.txt                # Extracted admin password hash ($P$ phpass)
│   ├── sqli_request.txt        # Sample SQLi HTTP request
│   ├── postdata.txt            # URL-encoded webshell upload payload
│   ├── cookies.txt             # WP auth cookies (session 1)
│   ├── cookies2.txt            # WP auth cookies (session 2)
│   ├── jar.txt                 # curl cookie jar used throughout
│   ├── timeline_raw.txt        # Raw session log excerpt (tool calls + timestamps)
│   └── html/                   # Saved WordPress admin pages
│       ├── ed.html             # Theme editor page
│       ├── editor2.html        # Plugin editor page
│       └── plugin_editor.html  # Plugin editor (hello.php)
│
├── wordlists/                  # Password lists used for cracking
│   ├── rockyou.txt             # Classic leaked password list (14M entries)
│   ├── top10k.txt              # Top 10k common passwords
│   ├── passwords.txt           # Small custom list (common defaults)
│   └── quick.txt               # CTF-targeted wordlist (Schneider, sectf, etc.)
│
├── tools/                      # Third-party tools
│   ├── sqlmap/                 # sqlmap — SQL injection automation
│   ├── ffuf                    # Web fuzzer binary
│   ├── common.txt              # ffuf wordlist for directory brute-forcing
│   ├── wdf/                    # web-directory-free v1.6.9 (vulnerable, CVE-2024-3552)
│   ├── wdf170/                 # web-directory-free v1.7.0 (patched)
│   ├── wdf.zip                 # Plugin ZIP archives
│   └── wdf170.zip
│
└── vendor/                     # Another copy of the vulnerable plugin source
    └── web-directory-free-1.6.9/
```

## Tools used

- **sqlmap** — SQL injection (explored but manual injection was faster)
- **john the ripper** — Password hash cracking (rockyou.txt → `SIMONE` in <1 sec)
- **ffuf** — Directory/endpoint fuzzing
- **curl** — HTTP requests, cookie management, webshell interaction
- **AWS CLI** — S3 enumeration, App Runner tagging (run from inside the compromised container)
- **pi** (Claude Code) — AI coding agent that wrote the exploits, automated enumeration, and ran the attack chain interactively

## Key CVE

**CVE-2024-3552** — Web Directory Free ≤ 1.6.9, unauthenticated SQL injection via `locations_ids[]` in the `w2dc_get_map_marker_info` AJAX action. User input concatenated raw into SQL. WordPress `addslashes` bypassed using hex-encoded strings (`0x414243` instead of `'ABC'`).
