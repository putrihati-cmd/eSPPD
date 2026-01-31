# Panduan Kontribusi (GitHub-centric) — eSPPD

Ini panduan singkat agar semua kontributor selalu sinkron *real-time* melalui GitHub (dua PC atau lebih bekerja bersamaan).

⚠️ Prinsip utama: jangan push langsung ke `main` / branch terlindungi. Gunakan branch fitur + Pull Request (PR).

Langkah singkat (recommended workflow):

1. Sinkron sebelum kerja
   - git fetch origin
   - git checkout main
   - git pull --rebase origin main

2. Buat branch fitur
   - git checkout -b feat/your-short-description

3. Kerja dan commit secara kecil (frekuen)
   - Ikuti format commit project
   - Jalankan pre-commit hooks: `composer pre-commit-install && pre-commit run --all-files` (atau lihat bagian Hooks di bawah)

4. Push ke remote dan buat PR
   - git push -u origin feat/your-short-description
   - Buat Pull Request via GitHub (web atau `gh pr create`)

5. Tunggu checks & review
   - Pastikan semua checks (CI, Full repository checks, CodeQL, gitleaks) lulus
   - Diperlukan 1 persetujuan dan `CODEOWNERS` review jika berlaku

6. Merge
   - Gunakan merge via GitHub (squash/merge atau merge commit sesuai kebijakan)

7. Setelah merge
   - Rebase atau pull lalu hapus branch lokal dan remote
     - git checkout main
     - git pull --rebase origin main
     - git branch -D feat/...
     - git push origin --delete feat/...

Hooks dan otomasi lokal
- Kami menggunakan `pre-commit` untuk checks lokal. Untuk meng-install hooks:
  - `composer pre-commit-install`
  - atau `pre-commit install` (jika pre-commit sudah terpasang)

Tips untuk dua PC yang dikerjakan bersamaan
- Selalu `git pull --rebase` sebelum mulai bekerja pada setiap PC
- Buat branch per fitur/bug agar konflik mudah diatasi
- Jika ada konflik, selesaikan di local, lalu `git rebase --continue` dan push

Jika ada yang belum jelas, buka issue atau mention tim di GitHub. Untuk langkah yang mengubah history (mis. purge venv), akan ada dokumentasi khusus dan koordinasi tim.
