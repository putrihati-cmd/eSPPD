#!/usr/bin/env python3
import sys

print("Checking Python dependencies...\n")

dependencies = {
    'pandas': 'Data manipulation',
    'openpyxl': 'Excel file handling',
    'bcrypt': 'Password hashing',
    'tqdm': 'Progress bars'
}

installed = []
missing = []

for pkg, desc in dependencies.items():
    try:
        __import__(pkg)
        print(f"✅ {pkg:<12} - {desc}")
        installed.append(pkg)
    except ImportError:
        print(f"❌ {pkg:<12} - {desc} [NOT FOUND]")
        missing.append(pkg)

print(f"\nSummary: {len(installed)}/{len(dependencies)} packages installed")

if missing:
    print(f"\nMissing packages: {', '.join(missing)}")
    print("\nTo install, run:")
    print(f"  pip3 install {' '.join(missing)}")
    sys.exit(1)
else:
    print("\n✅ All dependencies available!")
    sys.exit(0)
