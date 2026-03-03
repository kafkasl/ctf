# I Brought an AI to a Hacking Contest (and Won)

Today I attended a catch-the-flag (CTF) competition with the intention to let my coding agent [Pi](https://github.com/badlogic/pi-mono) do everything because I have no idea about cybersecurity. Pi won the competition and smoked the second place team by a large margin.

After we won the competition I spent some time actually learning what the agent did and whether my contribution steering it was useful.


![win](win-pict.jpg)[^1]

[^1]: This is the proof we won.

## About Me

In order to frame what comes next, I want to state upfront that I have never worked in cybersecurity but I am a software engineer. I know what a DDoS attack is, what privilege scalation means and some basic security pratices. I have worked in many small startups where knowing some small security stuff is useful. To be honest I've always found cybersecurity uninteresting and dull. I can't imagine myself staring at the screen for hours trying to poke holes into someone's server. My attitude coming into the event was to push Pi to the max and see what it could do w/o me knowing anything and paying little attention.

## The Competition

The competition itself was a beginner-friendly offensive security CTF by Schneider Electric at XPRO Barcelona. 21 teams each get an identical WordPress site on AWS and 90 minutes to chain four attacks: exploit a SQL injection to extract credentials, use those to get code execution on the server, pivot into the cloud infrastructure to steal data from private storage, and finally race other teams to tag each other's AWS resources.

## The Solution

Pi solved ALL the puzzles (no other team did). You can read all the details in the repo but it basically:

1. Phase I SQL Injection — Identified the vulnerable plugin (CVE-2024-3552), crafted error-based injection
payloads with hex encoding to bypass input escaping, and extracted the password hash and hidden flag from
the database.
2. Phase II Code Execution — Cracked the admin password hash using a dictionary attack, logged into WordPress, and
overwrote an unused plugin file with a PHP webshell via the built-in editor.
3. Phase III Cloud Pivot — Dumped environment variables to discover it was running on AWS, fetched IAM credentials
from the container metadata endpoint, enumerated S3 buckets, and decoded a base64 flag hidden inside a
PDF.
4. Phase IV PvP Tagging — Enumerated all teams' App Runner services via the AWS API and wrote a Python script to
mass-tag all 20 other teams' resources through the webshell.

It is clear Pi did a great job. Now the question after finishing was, did I actually help? Did my steering help or did it just slow Pi down?

### Total Contributions

After going over all the history (something that's super easy in Pi because everything is stored as a huge jsonl file), this is more or less how much steering was needed.

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

As you can see, I spent almost 45 minutes w/o any results, I was thinking we would not be able to solve the first puzzle at all. To be honest, part of that was spent setting up the connection and setup of the competition. Once that was done Pi went away and started some initial generic recon (which already identified one of the flags required in Phase I).

I also spent some time trying to understand the problem. Initially I just copy pasted the instructions from the organizers and tell Pi to do it. But I didn't pass all the context because I hadn't properly opened the actual webpage challenge. I just took a picture of the physical instructions, transcribed it, and put it in a empty folder for Pi.


| Impact | Count | Examples |
|--------|------:|----------|
| 🟢 **Critical** | 3 | Correct URL (physical world), "must be a trick" (strategic reframe), organizer hash intel (physical world) |
| 🟢 **Valuable** | 7 | Timeout pushback, forced recaps, screenshots of scoreboard, state persistence across rewinds |
| ⚪ **Neutral** | 8 | "Go" signals, explanation requests, redundant encouragement |
| 🟡 **Accidentally right** | 2 | "Don't brute force" (wrong, but led to faster DB extraction), "use DB to read /opt" (wrong, but harmless) |
| 🔴 **Harmful** | 4 | Interrupted running tools, misdirected away from SQLi, steered away from trivial hash crack, confusing screenshot |

**Totals: 10 helpful, 8 neutral, 2 lucky, 4 counterproductive**

You can see the full table in the [appendix](https://github.com/kafkasl/ctf/blob/main/appendix_interventions.md).


### Critical Contributions

Let's begin with the critical contributions. 

The agent spent 13 minutes using the wrong URL, because I was not paying attention I was just burning tokens. This was wrong context, but it made me actually start reading what AI was doing.

The "must be a trick" it's easily the only really critical contribution I did. The AI had spent ~8 minutes shotgunning: blind SQLi on random parameters, xmlrpc brute force, hydra password attacks with 60 common passwords, AJAX endpoint enumeration. It was in "try everything" mode with no direction. My intuition was that other human teams could not afford to try so many things, there must be an easier way so I wrote:

`Assume this is designed for humans… there must be some trick`

This message reframed the problem and the AI immediately pivoted to searching in the internet for known critical Common Vulnerabilities and Exposures (CVEs). It found [CVE-2024-3552](https://www.cve.org/CVERecord?id=CVE-2024-3552) within seconds. The vulnerability was present in version <=1.6.9. The report tells you what's the vulnerability but not how to exploit it. To find it out Pi downloaded both 1.6.9 and 1.7.0 diffed them and found the issue right away. 

My final critical contribution is kind of embarrasing. The organizer told us the admin hash could be cracked. I had told the AI not to try to crack the hash because it seemed to me undoable. What happened is that the AI had already cracked it using `john` CLI tool, but because of a format problem the AI did not notice it had already cracked it and we had the admin password. When I told Pi that organizer said we could crack it, it just looked back and noticed that it had already done that. Not sure whether that counts as a contribution, but definitely shows how unsupervised agents can even miss obvious things.

### Valuable Contributions

I think that the valuable contributions are the most interesting and where my skill really mattered. Mostly I would stop the AI every time I saw it using very long timeouts like 200s (which it has a great tendency to do). It seems a small issue, but 200s keep compounding and can easily make the iterations slow enough not to finish the CTF in time. You need to have an intuition of how long each step should take and stop the AI is making the wrong assumptions.

The second one is context management. Mario Zerchner (Pi's creator) wrote that he rarely goes over 20% context. If the context gets past that, he produces a summary doc or branch summary and go back to the beginning. (that is possible because Pi has a fantastic conversation tree that you can easily navigate back and forth, same as git). I would make sure context did not run of the rails. At the same time at summary points, I'd have a cursory look at what had been done. I also used the documet to fire another Pi instance with chatgpt-5.2 instead of Opus in parallel but this did not yield any results. You can see how the full NOTES.md looked at the end of the competition. I never read it during the project nor modified it manually.

### Neutral Contributions

Here I was mostly trying to inform myself. It might not affect the AI work but it helped me be more in the loop and possibly steer it better. Hard to quantify.

### Accidentally Right Contributions

This is kind of weird. I told Pi not to crack the hash so Pi **did found** another way to do it. However that was not intended by the competition organizers. Very weird. I guess adding some human entropy sometimes works. I also suggested wrong stuff but Pi already knew what was doing so it mostly ignored me. 

### Harmful Contributions

The harmful contributions are mostly me interrupting the agent w/o checking what it was doing at all. I screenshot the WP admin page, which only confused it because it was working on another thing. 

At another time I just asked **why do we need the SQLi vector?** to try to figure out what was going on. As it often happens Pi and agents are quite trigger happy. Instead of answering me, it assumed that I was questioning his approach (which was correct). It then pivoted to password brute force with hydra, which was a dead end. Sometimes you have to be careful with what you ask for. It's great that Pi has a fantastic history management so you can just go back and do it differently (or recap the full branch).

## Conclusion

I participated in this contest to put Pi to the test in a topic I knew very little about and which I did not want to learn too much about it. The result was very pleasant and people were impressed. 

In retrospect, my main job was to manage Pi's context and occasionally speed up the process by reducing timeout, or cutting off branches that didn't seem to go nowhere (although sometimes they did go somewhere). I think that left to its own devices Pi might have found the solutions in terms of intelligence, but I'm pretty sure the context would have run out. Would Claude Code's subagents fix this? not sure, maybe. I feel that an agent without context limits or some very clever way to go around them might have cracked it all on its own. 

However, the best part of the experience was to reflect on the whole process. As I already said, all the conversations are saved as a jsonl file. It includes all the branches, tool calls, etc... the full context (not like CC & other harnesses which have hidden tokens). 

First of all, I made Pi walk me over the whole solution until I understood it in depth. I found it very very interesting and I'm told people get paid for finding vulnerabilities like these. I was not too interested in the topic to begin with, so I was very pleased when I found myself eagerly asking questions during the debrief.

Lastly, I used the jsonl file to review my contribution to the overall process. I rarely do this because other harnesses do not make it easy. In this case, Pi worked furiously for very long while I paid little attention. For reference, here is a summary of the whole process:

| | Human | Pi |
|---|------:|---:|
| Messages | 47 | 225 |
| Words written | ~1,500 | ~6,500 |
| Internal thinking | — | ~7,000 words |
| Tool calls | — | 342 |
| Bash commands run | — | 314 |
| Files read/written | — | 28 |
| Tool output processed | — | 500 KB |

Pi produced **~13,500 words** (visible + thinking) to my **~1,500**, a **9:1 ratio**, in addition to 500 KB of tool output it read and acted on. The 314 bash commands in ~75 minutes works out to roughly **one command every 14 seconds**, sustained.

Being able to ask Pi to just look at the whole conversation and help me summarize it into a lessons learned is super valuable. 

It is possible that a very skilled hacker could have done better, but at least there wasn't today. I find it fascinating how far agents are coming and specially how pleasant it is to work with Pi. With an harness w/o all the bloat from CC, full access to the logs, lightning fast performance, and superb context management tools.

Definitely recommended.

## Update:
First of all the model used throughout was Opus 4.6. Multiple people pointed out that the model itself, not Pi, was the one actually doing the work. In part I do agree, but I highlighted Pi specifically for the following reasons.

First, another partner of the team attempted similar stuff using ChatGPT 5.2 and had some preliminary success. My intuition is that both Opus, 5.2 and probably Gemini could have been the brain of the harness successfully.
Second reason is that I specifically attempted this challenge because I felt so comfortable with Pi ergonomics. I have not used Claude Code for at least a month and a half and I've never enjoyed it as much. I would not have enrolled in the competition with CC so in a way, the win is for Pi.

I'm pretty sure I could configure CC with yolo mode to be closer to Pi. CC has hidden tokens that might make it slower, but probably that would not be a blocker either. However, the fact is that I would go "out of the recommended & safeway" of building with CC to achieve these results. If I had not used Pi first, I would not know what it is that I'd like. 

Designing interfaces that are this smooth and pleasant to use is very hard. Unfortunately, the result is that often you don't notice that. The interface gets out of the way, so in a sense it becomes transparent and it is easy to discount this importance. 

In this regard, Pi is an extension of my will and capabilities as much as the model itself. Using a car metaphor, if Opus is the engine Pi would be the car tires. While you could argue that the engine is the one winning the carrace, I would definitely not have attempted to drive this race with different tires (i.e. harness like Codex or CC) but I would have definitely try it out with a different engine (i.e. 5.2, Gemini). That is what lead me to give all the merits to Pi rather than the actual model.

PS: if you want the full conversation tree for further analysis or out of curiosity write to me

Reach me on X: [https://x.com/pol_avec](https://x.com/pol_avec)

**[Full repo: github.com/kafkasl/ctf](https://github.com/kafkasl/ctf)**
- [`NOTES.md`](https://github.com/kafkasl/ctf/blob/main/NOTES.md) — full technical writeup with commands, flags, and AWS details
- [`steering_analysis.md`](https://github.com/kafkasl/ctf/blob/main/steering_analysis.md) — detailed timeline of every human intervention across all 4 branches
- [`appendix_interventions.md`](https://github.com/kafkasl/ctf/blob/main/appendix_interventions.md) — table rating all 28 human messages
- [`exploits/`](https://github.com/kafkasl/ctf/tree/main/exploits) — attack scripts Pi wrote: SQLi payloads, webshell injector, AWS mass-tagger
- [`artifacts/`](https://github.com/kafkasl/ctf/tree/main/artifacts) — captured items: admin hash, the S3 PDF with the hidden flag, WP admin pages
