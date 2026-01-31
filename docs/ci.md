# CI & Local automation

This repo now includes GitHub Actions for CI and security and local helpers to run checks.

## Local quick commands
- Install pre-commit and enable hooks:
  - `pip install pre-commit && pre-commit install`
  - or `composer pre-commit-install`
- Run full CI locally:
  - `composer ci`
- Run tests:
  - `composer test`
- Run linter/formatting checks:
  - `composer lint`  (uses Laravel Pint)
  - `composer cs`    (php-cs-fixer dry run)
- Secret scan (requires `gitleaks`):
  - `gitleaks detect --source .`

## GitHub
- `CI` workflow runs tests and builds on push/PR to `main`, `master`, `develop`.
- `Security` workflow runs CodeQL analysis and gitleaks weekly and on push.
- Dependabot will open weekly PRs for composer and npm updates.

## Exposed secrets
If you ever accidentally exposed tokens (like a GitHub PAT), revoke them immediately and rotate credentials. See GitHub documentation for removing leaked tokens and scanning repo history with `git filter-repo` or `bfg`.

## Local history scan helpers
- PowerShell (Windows): `scripts\scan-history.ps1` — uses Docker if available, otherwise downloads gitleaks.
- Bash (Linux/Mac): `scripts/scan-history.sh` — uses Docker if available, otherwise downloads gitleaks.

## Hardening & protections
- A `full-check` workflow runs extended checks (linters, tests, gitleaks repo scan).
- Add an `ADMIN_TOKEN` (with repo admin scope) secret and run the `Branch protection (manual)` workflow to set required status checks and require code owner reviews.
- A `CODEOWNERS` file was added to require reviews by the repo owner.
