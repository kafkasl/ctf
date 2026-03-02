# Appendix: Every human intervention, rated

| Phase | Time | What I said | What AI was doing | Impact |
|-------|------|-------------|-------------------|--------|
| 1 | 10:18 | Pasted CTF rules & description | Nothing — no context | 🟢 **Essential** — AI had zero info |
| 1 | 10:21 | "Team code is 0x06" | Started recon on wrong domain | 🟢 **Essential** |
| 1 | 10:29 | "It's not HTTPS they said" | Stuck on wrong domain | ⚪ **Irrelevant** — problem was `offsec` vs `0ffsec`, not the protocol |
| 1 | 10:34 | "It resolves now: https://0x06.0ffsec.com/" | Would have kept trying wrong domain forever | 🟢 **Critical** — only a human in the room could know it was a zero, not an O |
| 1 | 10:36 | Pasted challenge objectives | Generic enumeration | 🟢 **Valuable** — focused AI on specific goals |
| 1 | 10:37 | "Why such a long timeout?" | Waiting on sqlmap with 300s timeout | 🟢 **Valuable** — AI killed it, switched to faster manual testing |
| 1 | 10:41 | "Can we do a recap?" | Shotgunning random endpoints | 🟢 **Valuable** — forced AI to stop and assess what it actually had |
| 1 | 10:42 | "Why do we need the SQLi vector?" | Explaining SQLi plan | 🔴 **Harmful** — AI pivoted to brute force, which was worse |
| 1 | 10:44 | "Assume this is designed for humans… there must be some trick" | About to try more brute force | 🟢 **Critical** — AI immediately Googled the CVE. Phase 1 turning point. |
| 1 | 10:45 | Screenshot of DB upgrade page | Found CVE, searching for PoC | 🔴 **Harmful** — confused AI into thinking I was already logged in |
| 1 | 10:48 | Branch rewind #1 | — | 🟢 **Valuable** — conversation was tangled, clean restart |
| 1 | 10:48 | "What would an expert hacker do?" | Reading branch summary | ⚪ **Neutral** — AI already had the CVE |
| 1 | 10:50 | "It should be a SQL injection thing, they just said" | Already doing SQLi, mid-execution | 🔴 **Harmful** — interrupted running tools. AI was already doing exactly this. |
| 1 | 10:52 | "Use short timeouts" | Running sqlmap | 🟢 **Valuable** — kept AI time-conscious |
| 1 | 10:58 | "What is the hash?" / "Hash of what?" | Just extracted hash | ⚪ **Neutral** — explanation request |
| 1 | 11:00 | "I don't think bruteforcing is a good idea" | About to download rockyou.txt | 🟡 **Accidentally valuable** — wrong (cracks in <1s), but pushed AI to extract flag directly from DB instead |
| 1 | 11:00 | "Update notes for another agent" | — | 🟢 **Valuable** — ensured state persistence across rewinds |
| 1 | 11:02 | Screenshot showing 3 sub-challenges | Celebrating Phase 1 flag | 🟢 **Valuable** — revealed hash itself was worth 2500pts |
| 2 | 11:07 | "So what now" | Read notes | ⚪ **Neutral** |
| 2 | 11:08 | "Explain approaches first" | Planning | ⚪ **Neutral** |
| 2 | 11:11 | "Cracking hash is complicated… leverage intelligence" | About to try hash cracking | 🔴 **Harmful** — cracking was trivially easy. Sent AI down rabbit holes. |
| 2 | 11:14 | "Why can't we just use DB access? File should be in /opt" | Exploring plugin source | 🟡 **Wrong** — SQL can't read OS files. Led to failed LOAD_FILE attempt. |
| 2 | 11:14 | "They just told me the hash can be cracked in less than a minute" | Stuck on complex approaches | 🟢 **Critical** — organizer intel. AI checked john's cache → password was already cracked! |
| 2 | 11:19 | "Is all in notes? Going to rewind" | Done with Phase 2 | 🟢 **Valuable** — phase transition |
| 3 | 11:20 | "Let's work on Phase 3" | Read notes | ⚪ **Green light** |
| 3 | 11:26 | "How did you find the flag?" | Phase 3 already done | ⚪ **Explanation request** — AI had already finished |
| 3 | 11:28 | Phase 4 screenshot + "summarize" | — | 🟢 **Valuable** — phase transition |
| 4 | 11:30 | "Work on it" | Read notes | ⚪ **Green light** |

**Summary: 🟢 3 critical, 7 valuable, 8 neutral, 4 harmful, 2 accidentally right for wrong reasons**
