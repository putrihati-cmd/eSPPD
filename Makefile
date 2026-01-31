PHONY: ci test lint cs secrets setup-tools

ci:
	composer ci

test:
	composer test

lint:
	composer lint

cs:
	composer cs

secrets:
	gitleaks detect --source . --report-path gitleaks-report.json --redact || true

setup-tools:
	# Download gitleaks binary into ./bin/ for offline use (Linux/Mac)
	curl -sL "https://github.com/zricethezav/gitleaks/releases/latest/download/gitleaks_$(shell uname -s)_$(shell uname -m).tar.gz" -o gitleaks.tgz || true
	mkdir -p bin || true
	tar -xzf gitleaks.tgz -C bin || true
	echo "gitleaks downloaded to ./bin/gitleaks"

clean-venv:
	# Example: remove committed python virtualenv folder from history using BFG
	echo "See docs/security-fix-steps.md for BFG/git-filter-repo usage"
