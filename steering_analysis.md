# CTF Steering Analysis: How Human Interventions Changed the AI's Course

## The Full Story Across All Branches

The session had **4 branches** — the human rewound the conversation 4 times, each time at a phase boundary. This means much of the actual work happened in branches that were then discarded, with only NOTES.md carrying state forward.

```
Main trunk ──┬── Branch 1 (10:21-10:48): Setup + Phase 1 first attempt (abandoned)
             ├── Branch 2 (10:48-11:07): Phase 1 completion (SQLi → hash → flags)
             ├── Branch 3 (11:07-11:20): Phase 2 (hash crack → webshell → /opt flag)
             ├── Branch 4 (11:20-11:30): Phase 3 (AWS creds → S3 → PDF flag) + Phase 4 plan
             └── Current  (11:30→):     Phase 4 execution (tag all teams) + writeup
```

---

## Detailed Timeline with Impact Analysis

### Branch 1: Setup + Phase 1 First Attempt (10:18 – 10:48)

| Time | AI was doing | Human said | Impact |
|------|-------------|------------|--------|
| 10:18 | Nothing — no context | Provided CTF description, rules | 🟢 **ESSENTIAL** — AI had zero info |
| 10:21 | Started recon on `[TEAM_ID].target-a.com` (wrong domain) | "team code is [TEAM_ID]" | 🟢 **ESSENTIAL** |
| 10:21-10:29 | Trying DNS/nmap/curl on wrong domain. Spinning. | — | AI stuck |
| 10:29 | Trying HTTP variants of wrong domain | "it is not https they said" | ⚪ Irrelevant — the problem was `target-a` vs `target-b`, not the protocol |
| 10:30-10:33 | More wrong domain attempts, checking WiFi | — | Still stuck |
| 10:34 | Would have kept trying wrong domain | **"it resolves now https://[TEAM_ID].target-b.com/"** | 🟢 **CRITICAL** — Only a human in the room could provide the correct URL. AI would never have guessed zero vs O. |
| 10:34-10:36 | Productive recon: WP 6.9.1, web-directory-free 1.6.9, robots.txt base64, user enumeration | — | AI doing well |
| 10:36 | Generic enumeration, about to spider more | Pasted challenge description with specific objectives | 🟢 **VALUABLE** — focused AI on "exploit vuln, get admin hash, find DB flag" |
| 10:36-10:37 | Launched sqlmap with 300s timeout | — | Overkill |
| 10:37 | Waiting for sqlmap... | **"why such a long timeout?"** | 🟢 **VALUABLE** — AI killed sqlmap, switched to faster manual testing |
| 10:38-10:41 | Shotgunning: order_by SQLi (escaped), xmlrpc brute force (failed), page enumeration, AJAX endpoint testing | — | Flailing across many approaches |
| 10:41 | About to try more random endpoints | **"can we do a recap?"** | 🟢 **VALUABLE** — forced AI to stop and assess. Summary revealed: had target + plugin + user, but no SQLi vector yet |
| 10:42 | Explaining SQLi plan | "why do we need the SQLi vector?" | 🔴 **MISDIRECTION** — AI pivoted to brute force, which was worse |
| 10:43-10:44 | Running hydra brute force, trying 60 common passwords | — | Dead end |
| 10:44 | About to try more brute force passwords | **"assume this is designed for humans... there must be some trick"** | 🟢 **CRITICAL** — AI immediately thought "Google the CVE" and found CVE-2024-3552. This single reframe was the Phase 1 turning point. |
| 10:45-10:47 | Found CVE, searching for PoC | Screenshot of DB upgrade page | 🔴 Caused confusion — AI thought human was already logged in |
| 10:47 | Confused about auth state | "back to admin login" | ⚪ Correction to previous confusion |
| **10:48** | — | **BRANCH REWIND #1** | 🟢 **VALUABLE META** — conversation was tangled, clean restart |

### Branch 2: Phase 1 Completion (10:48 – 11:07)

| Time | AI was doing | Human said | Impact |
|------|-------------|------------|--------|
| 10:48 | Reading branch summary, planning next steps | "what would an expert hacker do?" | ⚪ NEUTRAL — AI already had the CVE |
| 10:48-10:50 | Downloaded plugin source, tested AJAX actions, searched for PoC repo | — | Methodical |
| 10:50 | Already doing SQLi, mid-tool-execution | **"it should be a sql injection thing they just said"** | 🔴 **HARMFUL** — AI was already doing exactly this. Message interrupted running tool calls: "Skipped due to queued user message" |
| 10:50-10:52 | Found SQL debug output in responses, trying parameters | — | Progress |
| 10:52 | Running sqlmap with 60s timeout | **"use short timeouts"** | 🟢 **VALUABLE** — same pattern as before, kept AI time-conscious |
| 10:52-10:57 | Downloaded both plugin versions, diffed them, found exact vuln: `get_map_marker_info` + unsanitized `locations_ids` | — | **BREAKTHROUGH** — source code analysis was the right call |
| 10:57-10:58 | UNION injection working, EXTRACTVALUE extracting hash | — | Executing |
| 10:58 | Just extracted hash, about to start cracking | "what is the hash?" then "hash of what?" | ⚪ NEUTRAL — explanation requests |
| 10:58-11:00 | Trying john with no rockyou.txt, trying small wordlists, enumerating DB tables | — | Hash cracking failing (no good wordlist found yet) |
| 11:00 | About to download rockyou.txt and keep cracking | **"I don't think bruteforcing is a good idea"** | 🟡 **ACCIDENTALLY VALUABLE** — technically wrong ([PASSWORD] cracks in <1s with rockyou), BUT pushed AI to try extracting flag directly from DB via SQLi, which worked faster |
| 11:00 | — | "update notes for another agent" | 🟢 **VALUABLE META** — ensured state persistence |
| 11:01 | Tried hex-encoded LIKE queries on wp_options | — | Found `[FLAG_1]` directly in DB! |
| 11:02 | Celebrating Phase 1 flag | Screenshot showing 3 sub-challenges | 🟢 **VALUABLE** — revealed there were 3 flags not 1. Hash itself was worth 2500pts. |
| 11:03 | — | "summarize for new session" + Phase 2 screenshot | 🟢 **VALUABLE META** — phase transition |

### Branch 3: Phase 2 – The Hash Cracking (11:07 – 11:20)

**This is where the hash cracking actually happened.**

| Time | AI was doing | Human said | Impact |
|------|-------------|------------|--------|
| 11:07 | Read NOTES.md, found rockyou.txt already downloaded | "so what now" | ⚪ NEUTRAL |
| 11:08 | Planning Phase 2 approach | "explain approaches first" | ⚪ NEUTRAL |
| 11:09-11:11 | Outlined 3 approaches: (A) crack hash, (B) forge session token via SQLi, (C) SQLi UPDATE password | — | Planning |
| 11:11 | About to try approach B (forge session) | **"cracking hash is complicated... leverage intelligence"** | 🟡 **NEUTRAL/WRONG** — AI pivoted away from cracking. But cracking was trivially easy. |
| 11:11-11:13 | Searched for hash online, tried grep for plugin write paths, tried looking up activation keys, tested AJAX actions for nonce-free write endpoints | — | Overcomplicating it |
| 11:13-11:14 | About to try more complex SQLi tricks | — | Spiraling |
| 11:14 | Exploring plugin source for write capabilities | "why can't we just use DB access? file should be in /opt" | 🟡 **GOOD QUESTION but wrong** — SQL can't read OS files without FILE privilege. Led AI to try LOAD_FILE (didn't work). |
| 11:14 | Stuck on complex approaches | **"they just told me the hash can be cracked in less than a minute"** | 🟢 **CRITICAL** — Real-world intel from organizers! AI checked john's cache and discovered... |
| 11:14 | Checked john --show | — | **Password was `[PASSWORD]` — already cracked!** John had found it earlier from the small wordlist but AI didn't notice. |
| 11:15-11:17 | Logged into wp-admin with [PASSWORD], found Theme Editor + Plugin Editor, injected webshell into hello.php via AJAX plugin editor | — | Rapid execution |
| 11:17 | Read /opt/data.nan, base64-decoded | — | **Flag: `[FLAG_2]`** |
| 11:18-11:19 | Done, updated notes | "is all in notes.md? going to rewind" | 🟢 **VALUABLE META** |

**Key irony of Branch 3:** The AI had already cracked the hash ([PASSWORD] was in a small wordlist it tried) but DIDN'T CHECK the result. It took the human relaying "can be cracked in <1 min" from the organizers for the AI to run `john --show` and discover it was already done.

### Branch 4: Phase 3 – AWS Cloud (11:20 – 11:28)

| Time | AI was doing | Human said | Impact |
|------|-------------|------------|--------|
| 11:20 | Read notes, full context from previous phases | "let's work on phase 3" | ⚪ GREEN LIGHT |
| 11:21 | Queried IMDS (failed), found ECS Fargate env vars, hit container creds endpoint | — | Got IAM creds in 20 seconds |
| 11:21 | `aws s3 ls` → found all team buckets + infected-bucket | — | Found target bucket |
| 11:21-11:22 | Downloaded PDF from infected-bucket, extracted text, found base64 flag | — | **Phase 3 done in ~2 minutes with zero human input** |
| 11:26 | — | "how did you find the flag?" | ⚪ Explanation request |
| 11:28 | — | "summarize, here's Phase 4" + screenshot | 🟢 **VALUABLE META** — Phase transition + rewind |

**Branch 4 insight:** Once the webshell existed and the path was clear (AWS creds → S3), the AI needed zero human steering. The human's only contribution was the phase transition screenshot.

### Current Branch: Phase 4 – PvP (11:30 – 11:40)

| Time | AI was doing | Human said | Impact |
|------|-------------|------------|--------|
| 11:30 | Read notes | "work on it" | ⚪ GREEN LIGHT |
| 11:31-11:33 | Refreshed IAM creds, listed App Runner services, wrote tag_all.py, tagged all 20 teams | — | **Phase 4 done in ~3 minutes with zero human steering** |

---

## Summary: When Human Steering Actually Mattered

### The 4 moments that changed everything

1. **10:34 — Correct URL** (`target-b` not `target-a`)
   - AI was stuck for 5+ minutes. Only someone physically at the venue could fix this.
   - Category: **physical world bridge**

2. **10:44 — "Designed for humans... there must be a trick"**
   - AI was blind-fuzzing. Human reframed the problem. AI immediately Googled the CVE.
   - Category: **strategic reframe**

3. **11:14 — "They said it can be cracked in <1 min"**
   - AI had already cracked it but didn't check! Organizer intel made AI look at john's output.
   - Category: **physical world bridge** (organizer intel)

4. **Branch rewinds (4x)**
   - Kept context manageable, prevented conversation from ballooning.
   - Category: **meta-skill / tool mastery**

### When the human was counterproductive

1. **10:50 — "it should be SQLi"** — Interrupted running tools. AI was already doing SQLi.
2. **10:42 — "why do we need SQLi?"** — Briefly derailed AI away from the correct approach.
3. **11:11 — "cracking is complicated"** — Wrong intuition ([PASSWORD] cracks instantly), but accidentally led to a good outcome (direct DB flag extraction).

### The pattern

```
Phase 1: Heavy human steering needed (correct URL, CVE reframe, timeout pushback)
         ~45 minutes, many interventions, 2 critical saves

Phase 2: Moderate steering (organizer intel about hash cracking)
         ~13 minutes, 1 critical intervention

Phase 3: Zero steering needed
         ~2 minutes, AI solo

Phase 4: Zero steering needed
         ~3 minutes, AI solo
```

**As the AI accumulated tools and context (webshell, AWS creds, working patterns), it needed less and less human input.** The human was most valuable at the beginning when the AI had no context about the physical environment, and when strategic reframes were needed. By Phase 3-4, the AI had momentum and the human's role was reduced to "go" and "rewind."

### Lessons for AI-assisted CTFs (and AI collaboration in general)

1. **The human is the sensor, the AI is the executor.** The most valuable interventions were things only a human in the room could provide: correct URLs, organizer hints, screenshots of the scoreboard. The AI excels at rapid execution once pointed in the right direction.

2. **Strategic reframes beat tactical corrections.** "Designed for humans, there must be a trick" was worth more than any specific technical suggestion. It changed the AI's *framing* of the problem, not just its next step.

3. **Interrupting a working AI is costly.** Typing "it should be SQLi" while the AI was already doing SQLi killed running tool calls. In a time-pressured CTF, letting the AI finish its current action before redirecting is almost always better.

4. **Wrong intuitions can lead to right outcomes.** "Brute force is a bad idea" was technically wrong ([PASSWORD] cracks in <1 sec) but accidentally pushed the AI to extract the flag directly from the DB — a faster path. Happy accidents are real.

5. **Branch rewinds are a superpower.** The ability to let the AI explore, then rewind to a clean state with notes summarized, is the single most powerful meta-skill in tree-based AI collaboration. It's like save-scumming in a video game, but for problem-solving.

6. **AI autonomy scales with accumulated context.** Phase 1 needed heavy steering (45 min). Phase 3-4 needed zero (5 min combined). As the AI built up tools (webshell), credentials (AWS), and patterns (how this CTF works), it became increasingly self-sufficient.

7. **Check your own output.** The AI cracked the hash but didn't notice for 15 minutes. Humans should periodically ask "did anything already succeed that we missed?" rather than only looking forward.

### The most surprising finding

The AI cracked the hash but didn't notice. At 10:59, john ran against a small wordlist and found [PASSWORD] — but the AI's output parsing missed it (john's output format, "0g" vs "1g"). It took 15 minutes and an organizer hint before anyone realized. This is the quintessential human-AI failure mode: **the answer was already there, but neither party recognized it.**
