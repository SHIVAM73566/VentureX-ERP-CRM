#!/usr/bin/env python3
"""Build VentureX ERP & CRM buyer-ready release ZIP (Acquire/Gumroad/SellAnyCode/Payhip)."""
from __future__ import annotations

import os
import shutil
import subprocess
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DIST = ROOT / "dist"
RELEASE = ROOT / "release"
OUT_ZIP = RELEASE / "venturex-erp-crm-production-v2.6-final.zip"
INNER = "VentureX-ERP-CRM"

EXCLUDE_DIRS = {".git", "node_modules", ".idea", ".vscode", "dist", "release"}


def run(cmd: list[str], cwd: Path) -> None:
    print("==>", " ".join(cmd))
    subprocess.run(cmd, cwd=str(cwd), check=True, shell=(os.name == "nt"))


def should_skip(rel: Path) -> bool:
    parts = rel.parts
    for p in parts:
        if p in EXCLUDE_DIRS:
            return True
    name = rel.name
    if name == ".env":
        return True
    if name.startswith(".env.") and name != ".env.example":
        return True
    if name.endswith(".sqlite") or name.endswith(".sqlite-journal"):
        return True
    # logs
    if len(parts) >= 2 and parts[0] == "storage" and parts[1] == "logs":
        if name != ".gitignore":
            return True
    # framework caches
    if len(parts) >= 3 and parts[0] == "storage" and parts[1] == "framework":
        if parts[2] in ("cache", "sessions", "views"):
            if name != ".gitignore":
                return True
    if len(parts) >= 2 and parts[0] == "bootstrap" and parts[1] == "cache":
        if name != ".gitignore":
            return True
    # sql dumps
    if name.lower().endswith(".sql") or name.lower().endswith(".sql.gz"):
        return True
    # test temp artifacts
    if name in (".phpunit.result.cache", "phpunit.xml.bak"):
        return True
    if name.startswith("build_v131_zips") and name.endswith(".py"):
        return True
    return False


def main() -> int:
    DIST.mkdir(exist_ok=True)
    RELEASE.mkdir(exist_ok=True)
    done_marker = DIST / ".build_done"
    if done_marker.exists():
        try:
            done_marker.unlink()
        except OSError:
            pass

    # Build frontend if package.json exists
    if (ROOT / "package.json").exists():
        if (ROOT / "package-lock.json").exists():
            run(["npm", "ci"], ROOT)
        else:
            run(["npm", "install"], ROOT)
        run(["npm", "run", "build"], ROOT)
    else:
        print("No package.json, skipping npm build.")

    manifest = ROOT / "public" / "build" / "manifest.json"
    if not manifest.is_file():
        print("ERROR: public/build/manifest.json missing after build.", file=sys.stderr)
        return 1

    if OUT_ZIP.exists():
        OUT_ZIP.unlink()

    print("==> Creating ZIP:", OUT_ZIP)
    with zipfile.ZipFile(OUT_ZIP, "w", zipfile.ZIP_DEFLATED, compresslevel=6) as zf:
        for dirpath, dirnames, filenames in os.walk(ROOT):
            dirnames[:] = [d for d in dirnames if d not in EXCLUDE_DIRS]
            for fn in filenames:
                fp = Path(dirpath) / fn
                rel = fp.relative_to(ROOT)
                if should_skip(rel):
                    continue
                arcname = f"{INNER}/{rel.as_posix()}"
                zf.write(fp, arcname)

    size_mb = OUT_ZIP.stat().st_size / (1024 * 1024)
    print(f"==> Done: {OUT_ZIP} ({size_mb:.2f} MB)")

    # Also create channel copies for requested marketplaces
    for name in [
        "VentureX-ERP-CRM-Acquire-Release-v2.6.zip",
        "VentureX-ERP-CRM-SellAnyCode-v2.6.zip",
        "VentureX-ERP-CRM-Payhip-v2.6.zip",
        "VentureX-ERP-CRM-Codester-v2.6.zip",
    ]:
        dest = RELEASE / name
        if dest != OUT_ZIP:
            shutil.copy2(OUT_ZIP, dest)
            print(f"==> Copied: {dest.name}")

    done_marker.write_text("ok", encoding="utf-8")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
