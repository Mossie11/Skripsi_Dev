import os

project_dir = r"d:\Source Code\wr2school\wr2school"
for root, dirs, files in os.walk(project_dir):
    if "vendor" in root or "node_modules" in root or ".git" in root:
        continue
    for file in files:
        if file.endswith(('.php', '.html', '.js', '.css')):
            path = os.path.join(root, file)
            try:
                with open(path, 'r', encoding='utf-8') as f:
                    content = f.read()
                    if 'KKTP' in content or 'Ketercapaian' in content:
                        print(f"Found in: {path}")
            except Exception:
                pass
