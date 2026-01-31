#!/usr/bin/env bash
set -euo pipefail

if ! command -v git-filter-repo >/dev/null 2>&1; then
  echo "git-filter-repo is required. Install from https://github.com/newren/git-filter-repo"
  exit 1
fi

echo "This will rewrite history to remove document-service/venv. Create a backup branch first."
read -p "Create backup branch now? (y/N) " yn
yn=${yn:-N}
if [[ "$yn" =~ ^[Yy]$ ]]; then
  git branch backup-before-venv-removal
  echo "Backup branch created: backup-before-venv-removal"
fi

# Remove the venv folder from history
git filter-repo --invert-paths --path document-service/venv

echo "History rewritten. Now run: git reflog expire --expire=now --all && git gc --prune=now --aggressive && git push --force"

echo "After push, rotate any secrets that might have been exposed."