---
name: 'Purge history: remove document-service/venv'
about: Coordination checklist for running the irreversible history rewrite that removes `document-service/venv`.
labels: purge, security
---

## Purpose
Coordinate the history rewrite to remove `document-service/venv` from repository history.

## Checklist
- [ ] Confirm date/time for the operation (UTC): ______________________
- [ ] All CI / Full-checks on `main` are green
- [ ] `ADMIN_TOKEN` repo secret is present and valid
- [ ] Team announced and no pushes expected during the operation
- [ ] Backup and restore plan reviewed and documented
- [ ] Approver(s): @________________

## Notes / Risks
- This change is irreversible to history; backups will be created as tags prior to the rewrite.

## Post-action responsibilities
- [ ] Re-clone repository after completion
- [ ] Rotate secrets & PATs if appropriate
- [ ] Confirm application CI/CD jobs still work and SHA references updated

Please comment below when ready to proceed. Once the team confirms, the workflow will be run with `confirm=CONFIRM` input.
