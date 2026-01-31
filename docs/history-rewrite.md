# History Rewrite: removing `document-service/venv`

This document explains the safe, auditable process we use to permanently remove `document-service/venv` from the repository history.

Why: A committed Python virtual environment bloats the repo and may contain accidental credentials or binary artifacts. Rewriting history removes it from all commits.

Important notes before running the purge:
- This operation is **destructive** (rewrites history) and requires coordination with all contributors.
- We create a backup tag first (prefixed `backup/pre-history-rewrite-`) so the state can be restored if necessary.
- After the push, **all contributors must re-clone** the repository.

Options to perform the purge:

1) Recommended (safe, automated) — Use the `Purge document-service/venv from history` workflow:
   - Add a repository secret `ADMIN_TOKEN` (personal access token with `repo` + admin privileges).
   - Trigger the workflow manually from the Actions tab, and type `CONFIRM` when prompted (this prevents accidental runs).
   - The workflow will:
     - Mirror-clone the repo, create a backup tag, run `git-filter-repo --invert-paths --path document-service/venv`, verify removal, and force-push the rewritten history.
     - Open an issue summarizing the action and the backup tag name.

2) Manual (local) — If you prefer to run locally:
   - Install `git-filter-repo` per https://github.com/newren/git-filter-repo (pip or package manager).
   - Create a backup and run the filter:
     ```bash
     # from a clone (non-bare) or create a mirror clone
     git tag backup/pre-history-rewrite-$(date -u +'%Y%m%d-%H%M%S')
     git push origin --tags

     # remove folder from all history
     git filter-repo --invert-paths --path document-service/venv

     # cleanup and push
     git reflog expire --expire=now --all
     git gc --prune=now --aggressive
     git push --force --all
     git push --force --tags
     ```

Post-rewrite tasks (must be communicated to all contributors):
- All devs should re-clone the repository (do not attempt to pull & fix local branches).
- Rotate any secrets that might have been exposed historically.
- Monitor CI and be prepared to re-run any failing jobs.

If you want me to run the automated purge using the GitHub Action, add `ADMIN_TOKEN` (repo admin PAT) and tell me to trigger the workflow on `main`. If you'd rather proceed manually, I can provide a checklist and help run/follow up.
