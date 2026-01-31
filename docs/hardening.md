# GitHub hardening & branch protection

Steps to harden the repository and require protections:

1. Add required status checks: `Full repository checks` (from `.github/workflows/full-check.yml`) and `CI` (from `.github/workflows/ci.yml`).
2. Require code owner reviews (we added `.github/CODEOWNERS`).
3. Enable GitHub Secret Scanning and Push Protection in repository `Settings -> Security & analysis`.
4. Optionally run the `Branch protection (manual)` workflow. It requires an admin token stored as `ADMIN_TOKEN` in repository secrets with `repo` scope. This workflow will call GitHub API and set protection for `main`.

Note: Setting branch protection via API requires admin permissions and is a manual step to avoid accidental lockouts.
