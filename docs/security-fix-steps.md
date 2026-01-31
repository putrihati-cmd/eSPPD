# Steps to fix leaked secrets and clean repository history

If gitleaks (or other scanners) find secrets in either the working tree or git history, follow these steps carefully and coordinate with your team.

1. Revoke and rotate the leaked secret immediately (tokens, keys, passwords).

2. Create a backup branch before history rewrite:
   - `git branch backup-before-cleanup`

3. Use BFG or git-filter-repo to remove the secret from history.
   - Example using BFG:
     - `bfg --delete-text-strings 'EXPOSED_SECRET'`
     - `git reflog expire --expire=now --all && git gc --prune=now --aggressive`
     - `git push --force`
   - Example using git-filter-repo (recommended for complex cases):
     - `git filter-repo --replace-text replacements.txt`

4. Verify with gitleaks:
   - `gitleaks detect --repo-path . --report-path gitleaks-postclean.json --redact`

5. Rotate/Recreate any credentials that were exposed after history cleanup.

6. Enforce repository protections:
   - Enable GitHub secret scanning and push protection.
   - Require CI checks and code reviews on `main` via branch protection rules.

7. Communicate to the team about the rewrite and coordinate pulls/clones.

Note: History rewrite requires force-push and coordination. Do not force-push to shared branches without consent from collaborators.
