# PURGE document-service/venv FROM HISTORY
# ⚠️ WARNING: ONE-TIME EXECUTION - NO ROLLBACK POSSIBLE
# ⚠️ All collaborators MUST re-clone after this
# ⚠️ Force-push will rewrite history - irreversible
# ⚠️ Backup tag created automatically (can revert to this if needed)

# ============================================
# STEP 1: BACKUP REPO (DI PC1)
# ============================================
cd ~/eSPPD
git tag backup-before-purge-$(date +%Y%m%d-%H%M%S)
git push origin --tags
# Verify:
git tag | grep backup-before-purge

# ============================================
# STEP 2: PURGE VENV DARI HISTORY
# ============================================
pip install git-filter-repo
git filter-repo --path document-service/venv --invert-paths --force

# ============================================
# STEP 3: FORCE PUSH (IRREVERSIBLE)
# ============================================
git push origin --force --all
git push origin --force --tags

# ============================================
# STEP 4: VERIFY
# ============================================
git log --all --full-history -- document-service/venv
# MUST be empty - if shows results, something went wrong

ls -la document-service/ | grep venv
# MUST be empty - if venv folder exists, purge failed

# ============================================
# SETUP SSH - PC1
# ============================================
ssh-keygen -t ed25519 -C "pc1@espdd"
# Enter 3x (no passphrase)
cat ~/.ssh/id_ed25519.pub
# Copy output, paste ke GitHub Settings > SSH Keys

git remote set-url origin git@github.com:putrihati-cmd/eSPPD.git

# ============================================
# SETUP SSH - PC2
# ============================================
ssh-keygen -t ed25519 -C "pc2@espdd"
cat ~/.ssh/id_ed25519.pub
# Copy output, paste ke GitHub Settings > SSH Keys (key baru)

git clone git@github.com:putrihati-cmd/eSPPD.git
cd eSPPD

# ============================================
# WORKFLOW NOT NEEDED - USE MANUAL STEPS
# ============================================
# Manual execution gives you full control & visibility
# Workflows can timeout/fail - manual is more reliable


# ============================================
# POST-PURGE CHECKLIST
# ============================================

# 1. Verify backup tag created
git tag | grep backup-before-purge
# Expected: backup-before-purge-YYYYMMDD-HHMMSS

# 2. Verify venv removed from history
git log --all --full-history -- document-service/venv | wc -l
# Expected: 0 (completely empty)

# 3. Verify folder gone
ls -la document-service/ | grep venv
# Expected: no output (folder doesn't exist)

# 4. Check repo size reduced
git count-objects -v

# 5. PC2: Fresh clone
cd ~/temp && git clone git@github.com:putrihati-cmd/eSPPD.git eSPPD-test
cd eSPPD-test && git log --oneline | head -3

# 6. PC1 <-> PC2 test
# PC1: Create & push
echo "test" > TEST.txt
git add TEST.txt
git commit -m "test sync"
git push origin main

# PC2: Pull
git pull origin main
ls TEST.txt  # Should exist

# ✅ ALL PASSED = PURGE SUCCESSFUL
git pull

# DONE
