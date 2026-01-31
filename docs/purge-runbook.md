# Purge Runbook — Remove document-service/venv from history

Purpose
- Provide a clear, repeatable checklist and communication plan before running the irreversible history rewrite that removes `document-service/venv`.

Prerequisites (must be completed before dispatch):
1. All CI and Full repository checks on `main` are green.
2. `ADMIN_TOKEN` repo secret exists and is a fine-grained PAT with repo:contents & actions permissions (already set as `ADMIN_TOKEN`).
3. Backup plan agreed: the purge workflow creates a backup tag `backup/pre-history-rewrite-YYYYMMDD-HHMMSS` and pushes it — ensure team knows how to find and restore if necessary.
4. Team coordination: announce on Slack/Teams + create an issue using the `Purge history` issue template for people to acknowledge.
5. Avoid any pushes during the rewrite window.

Checklist — pre-run
- [ ] Open a coordination issue (use `.github/ISSUE_TEMPLATE/purge-history.md`) and assign required approvers.
- [ ] Confirm `ADMIN_TOKEN` secret is set and accessible to Actions.
- [ ] Ensure all contributors are informed and will not push during the operation.
- [ ] Confirm backup tag strategy and verify available remote disk & permissions.
- [ ] Team agrees on a time window to perform the rewrite.

How to run (manual UI)
1. Go to GitHub → Actions → **Purge document-service/venv from history**.
2. Click **Run workflow** → select `main` → Set input `confirm` = `CONFIRM` → Run workflow.

How to run (PowerShell)
- Use a PAT with Actions dispatch permission and run (the command will prompt for PAT):
```powershell
$token = Read-Host -AsSecureString "Masukkan GitHub PAT (Actions/workflow dispatch permission)"
$plain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($token))
Invoke-RestMethod -Method POST -Uri "https://api.github.com/repos/${{ github.repository }}/actions/workflows/purge-venv-history.yml/dispatches" -Headers @{ Authorization = "token $plain"; "User-Agent" = "purge-dispatch" } -Body (@{ ref = "main"; inputs = @{ confirm = "CONFIRM" } } | ConvertTo-Json)
```

Verification (during/after run)
- Confirm backup tag `backup/pre-history-rewrite-*` exists in repository tags.
- Verify the run logs show `document-service/venv removal verified` in the `Verify removal` step.
- Confirm the force-push step completed successfully (`git push --force --all` and `git push --force --tags`).
- Watch for the issue created by the workflow to report completion.

Post-purge steps (must be performed by all contributors)
1. Re-clone repository: `git clone https://github.com/<org>/<repo>.git` (old clones will have the old history and will cause confusion).
2. Rotate secrets and PATs if any previously exposed secret tokens might exist in history. Also rotate any tokens that were exposed earlier (if you found any credentials during audit).
3. Verify any downstream CI or deployment configs that rely on SHA references and update as necessary.

Rollback plan
- If a problem occurs, we have backup tags `backup/pre-history-rewrite-*` — use git or a mirror to restore the repository to the state pre-rewrite (contact repo admin for manual restore).

Communication templates
- Announcement pre-purge (Slack):
> We will run a repository history rewrite to remove `document-service/venv` on <date/time UTC>. DO NOT push during the operation. A backup tag will be created and pushed. After completion, please re-clone the repository and rotate secrets.

- Completion message (automated by workflow will open an issue): check the issue for the backup tag name and follow the post-purge steps.

Contact / Owners
- Repository admins: @putrihati-cmd
- On-call engineer: <add names here>

Notes
- This runbook is a living document; update it if steps change.
