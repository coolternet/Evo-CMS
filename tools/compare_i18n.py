#!/usr/bin/env python3
"""Compare language file keys against fr reference."""
import re
import sys
from pathlib import Path

def extract_keys(path: Path) -> set:
    text = path.read_text(encoding="utf-8", errors="replace")
    return set(re.findall(r"^\s*'([^']+)'\s*=>", text, re.MULTILINE))

def main():
    root = Path(__file__).resolve().parent.parent
    base = root / "includes" / "languages"
    ref_lang = "fr"
    files = ["admin.php", "messages.php", "errors.php", "install.php", "mail.php", "index.php"]
    langs = ["en", "de", "du", "es", "it", "ru"]

    for fname in files:
        ref_path = base / ref_lang / fname
        if not ref_path.exists():
            continue
        ref_keys = extract_keys(ref_path)
        print(f"\n=== {fname} (ref fr: {len(ref_keys)} keys) ===")
        for lang in langs:
            p = base / lang / fname
            if not p.exists():
                print(f"  {lang}: FILE MISSING")
                continue
            k = extract_keys(p)
            missing = ref_keys - k
            extra = k - ref_keys
            if missing or extra:
                print(f"  {lang}: missing {len(missing)}, extra {len(extra)}")
                for x in sorted(missing)[:40]:
                    print(f"    - {x}")
                if len(missing) > 40:
                    print(f"    ... and {len(missing)-40} more")
                if extra:
                    samp = sorted(extra)[:12]
                    print(f"    extra sample: {samp}")

if __name__ == "__main__":
    main()
