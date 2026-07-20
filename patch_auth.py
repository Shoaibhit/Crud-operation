import os
import re
root = r'C:\wamp64\www\login'
exclude = {'register.php', 'logout.php', 'connection.php', 'auth.php', 'tmp_db.php', 'tmp_journal_alter.php', 'tmp_journal_table.php', 'tmp_schema.php', 'test.php'}
for fname in sorted(os.listdir(root)):
    if not fname.endswith('.php') or fname in exclude:
        continue
    path = os.path.join(root, fname)
    with open(path, 'r', encoding='utf-8') as f:
        text = f.read()
    if "require 'auth.php'" in text or 'include \'auth.php\'' in text or 'require \"auth.php\"' in text:
        continue
    new_text = text
    m = re.match(r'^(\s*<\?php\s*\n)(session_start\(\);\s*\n)', text)
    if m:
        new_text = m.group(1) + "require 'auth.php';\n" + m.group(2) + text[m.end():]
    else:
        new_text = re.sub(
            r'^(\s*<\?php\s*\n)(header\([^\n]*\);\s*\n)?(include|require)\s+(["\'])connection\.php\4;\s*\n',
            lambda mo: mo.group(1) + (mo.group(2) or '') + "require 'auth.php';\n" + mo.group(3) + ' ' + mo.group(4) + 'connection.php' + mo.group(4) + ";\n",
            text,
            count=1,
        )
    if new_text != text:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(new_text)
        print('Patched', fname)
