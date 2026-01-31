# Automation & Hardening — Next Steps (one-click where possible)

This doc explains the final manual steps to make the repository *fully automated* and hardened.

1. Create the PR (automated)
   - The repo contains a workflow `.github/workflows/auto-create-pr.yml` that will automatically open a PR from `security/automation` → `main` whenever `security/automation` is pushed.

2. Dependabot auto-merge
   - Dependabot PRs will be labeled `automerge` and `dependencies` automatically by `.github/workflows/label-dependabot.yml`.
   - `.github/workflows/automerge-dependabot.yml` will merge PRs labeled `automerge` if required status checks pass.

3. Branch protection & secret scanning (admin steps)
   - Add a repository secret named `ADMIN_TOKEN` with a personal token that has `repo` scope (admin) in Settings → Secrets.
   - Run the `Branch protection (manual)` workflow in Actions (it will set required checks and code owner reviews for `main`).
   - Enable **Secret scanning** and **Push protection** in GitHub settings (Security & analysis).

4. Clean up repo history (if gitleaks or other scans find leaked secrets)
   - Use `scripts/remove-venv-history.sh` or `git-filter-repo` per `docs/security-fix-steps.md` to remove large / accidental commits from history, then force-push.
   - Rotate all revoked secrets after rewrite.

5. Final checks
   - Merge PR `security/automation` once CI & full-check pass.
   - Verify Dependabot PRs are merging automatically for non-breaking updates.

If you want, provide `ADMIN_TOKEN` as a repo secret and I will run the **Branch protection** workflow and try to enable repository Secret Scanning and Push Protection (I will show results and any errors).
