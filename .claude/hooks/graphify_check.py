import json, sys, os, re

try:
    d = json.load(sys.stdin)
except Exception:
    sys.exit(0)

tool_name = d.get('tool_name', '')
t = d.get('tool_input', d)

if not os.path.exists('graphify-out/graph.json'):
    sys.exit(0)

should_warn = False

if tool_name == 'Bash':
    cmd = t.get('command', '')
    if re.search(r'grep|rg\s|ripgrep|find\s|fd\s|ack\s|ag\s', cmd):
        should_warn = True

elif tool_name in ('Read', 'Glob'):
    exts = (
        '.py', '.js', '.ts', '.tsx', '.jsx', '.astro', '.vue', '.svelte',
        '.go', '.rs', '.java', '.rb', '.c', '.h', '.cpp', '.hpp', '.cc',
        '.cs', '.kt', '.swift', '.php', '.scala', '.lua', '.sh',
        '.md', '.rst', '.txt', '.mdx',
    )
    vals = [
        str(t.get('file_path') or ''),
        str(t.get('pattern') or ''),
        str(t.get('path') or ''),
    ]
    for v in vals:
        norm = v.replace('\\', '/')
        if v and 'graphify-out' not in norm:
            if any(v.lower().endswith(e) for e in exts):
                should_warn = True
                break

if should_warn:
    print(json.dumps({
        "hookSpecificOutput": {
            "hookEventName": "PreToolUse",
            "additionalContext": (
                "MANDATORY: graphify-out/graph.json exists. "
                "Use graphify before reading/grepping source files: "
                "`graphify query \"<question>\"`, "
                "`graphify explain \"<concept>\"`, or "
                "`graphify path \"<A>\" \"<B>\"`. "
                "Only read raw files to make specific edits after graphify has oriented you."
            )
        }
    }))
