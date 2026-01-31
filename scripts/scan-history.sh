#!/usr/bin/env bash
set -euo pipefail
REPO_DIR="$(pwd)"
if command -v docker >/dev/null 2>&1; then
  echo "Running gitleaks via Docker"
  docker run --rm -v "$REPO_DIR":/repo zricethezav/gitleaks detect --repo-path /repo --report-path /repo/gitleaks-report.json --redact || true
else
  echo "Downloading gitleaks binary"
  LATEST="$(curl -sI https://github.com/zricethezav/gitleaks/releases/latest | grep -i location | awk -F'/' '{print $NF}' | tr -d '\r')"
  curl -sL "https://github.com/zricethezav/gitleaks/releases/latest/download/gitleaks_$(uname -s)_$(uname -m).tar.gz" -o gitleaks.tgz || true
  mkdir -p bin || true
  tar -xzf gitleaks.tgz -C bin || true
  ./bin/gitleaks detect --repo-path . --report-path gitleaks-report.json --redact || true
fi

echo "gitleaks report: gitleaks-report.json"
