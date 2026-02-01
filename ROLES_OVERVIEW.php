<?php

/**
 * Role Hierarchy Overview
 */

$roles = [
    [
        'name' => 'superadmin',
        'label' => 'Super Administrator',
        'level' => 99,
        'description' => 'Full system access, manage all users and settings'
    ],
    [
        'name' => 'admin',
        'label' => 'Admin Kepegawaian',
        'level' => 98,
        'description' => 'Manage employees, import data, system settings'
    ],
    [
        'name' => 'rektor',
        'label' => 'Rektor',
        'level' => 6,
        'description' => 'Final approval for all SPD, institution-wide view'
    ],
    [
        'name' => 'warek',
        'label' => 'Wakil Rektor',
        'level' => 5,
        'description' => 'Approve SPD luar negeri, institution-wide view'
    ],
    [
        'name' => 'dekan',
        'label' => 'Dekan',
        'level' => 4,
        'description' => 'Approve SPD faculty, override cancel, executive view'
    ],
    [
        'name' => 'wadek',
        'label' => 'Wakil Dekan',
        'level' => 3,
        'description' => 'Approve SPD dalam kota, delegation, faculty view'
    ],
    [
        'name' => 'kabag',
        'label' => 'Kepala Bagian / Kaprodi',
        'level' => 2,
        'description' => 'Approve subordinate SPD, forward to Wadek'
    ],
    [
        'name' => 'dosen',
        'label' => 'Dosen / Pegawai',
        'level' => 1,
        'description' => 'Submit SPD, view own history, download documents'
    ],
];

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                          ROLE HIERARCHY SYSTEM (8 Roles)                       ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "┌─ HIERARCHY TREE ────────────────────────────────────────────────────────────────┐\n";
echo "│                                                                                  │\n";
echo "│  Level 99  → Super Administrator (Full Access)                                  │\n";
echo "│  Level 98  → Admin Kepegawaian                                                  │\n";
echo "│  ─────────────────────────────────────────                                      │\n";
echo "│  Level 6   → Rektor (Final Approval)                                            │\n";
echo "│    ├─ Level 5 → Wakil Rektor                                                    │\n";
echo "│    │  ├─ Level 4 → Dekan (Per Faculty)                                          │\n";
echo "│    │  │  ├─ Level 3 → Wakil Dekan                                               │\n";
echo "│    │  │  │  └─ Level 2 → Kaprodi / Kepala Bagian                                │\n";
echo "│    │  │  │     └─ Level 1 → Dosen / Pegawai (Submitter)                         │\n";
echo "│                                                                                  │\n";
echo "└──────────────────────────────────────────────────────────────────────────────────┘\n\n";

echo "ROLE DETAIL:\n\n";
foreach ($roles as $i => $role) {
    $num = $i + 1;
    echo "[$num] {$role['label']} (Level: {$role['level']})\n";
    echo "    Name: {$role['name']}\n";
    echo "    Access: {$role['description']}\n\n";
}

echo "═══════════════════════════════════════════════════════════════════════════════════\n\n";
echo "PRODUCTION ACCOUNTS TESTING:\n\n";
echo "✓ Mawi Khusni      → level:98 (Admin Kepegawaian)\n";
echo "✓ Ansori           → level:4  (Dekan)\n";
echo "✓ Ahmad Fauzi      → level:1  (Dosen)\n";
echo "✓ Siti Nurhaliza   → level:1  (Dosen)\n";
echo "✓ Budi Santoso     → level:1  (Dosen)\n\n";

echo "MISSING ACCOUNTS FOR COMPLETE TESTING:\n";
echo "? Super Administrator  (level:99)\n";
echo "? Rektor               (level:6)\n";
echo "? Wakil Rektor         (level:5)\n";
echo "? Wakil Dekan          (level:3)\n";
echo "? Kaprodi / Kepala Bag (level:2)\n\n";
