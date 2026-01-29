"""
Configuration for Smart Hierarchy Builder
Auto-generate approval hierarchy for e-SPPD system
"""

import re

# Approval Levels
LEVELS = {
    6: {'name': 'Rektor', 'budget': 999_999_999},
    5: {'name': 'Wakil Rektor', 'budget': 100_000_000},
    4: {'name': 'Dekan', 'budget': 50_000_000},
    3: {'name': 'Wakil Dekan', 'budget': 20_000_000},
    2: {'name': 'Middle Manager', 'budget': 5_000_000},  # Kaprodi, Kabag, Kasek
    1: {'name': 'Staff/Dosen', 'budget': 0}
}

# Regex patterns for role classification (case insensitive)
LEVEL_PATTERNS = {
    6: re.compile(r'\brektor\b(?!.*wakil)', re.IGNORECASE),
    5: re.compile(r'wakil\s*rektor', re.IGNORECASE),
    4: re.compile(r'\bdekan\b(?!.*wakil)', re.IGNORECASE),
    3: re.compile(r'wakil\s*dekan', re.IGNORECASE),
    2: re.compile(r'(ketua|kepala|koordinator|kasek|kabag|kaprodi|kajur|sekretaris\s*jurusan)', re.IGNORECASE),
}

# Faculty extraction keywords
FACULTY_KEYWORDS = {
    'FTIK': ['ftik', 'tarbiyah', 'ilmu tarbiyah', 'pendidikan'],
    'FEBI': ['febi', 'ekonomi', 'bisnis islam'],
    'FAI': ['fai', 'agama islam', 'ushuluddin'],
    'FISIP': ['fisip', 'ilmu sosial', 'politik'],
    'FH': ['hukum', 'syariah'],
    'FDK': ['dakwah', 'komunikasi'],
    'FUAD': ['fuad', 'ushuluddin', 'adab'],
    'FASYA': ['fasya', 'syariah'],
    'REKTORAT': ['rektorat', 'bagian umum', 'pusat', 'lppm', 'lpm', 'upt']
}

# SQL Templates
SQL_UPDATE_EMPLOYEE = """UPDATE employees SET
    approval_level = {level},
    structural_role = '{role}',
    faculty = '{faculty}',
    superior_nip = {superior_nip},
    approval_limit = {limit},
    updated_at = NOW()
WHERE nip = '{nip}';"""

SQL_UPSERT_USER = """INSERT INTO users (name, nip, email, password, role, superior_nip, is_active, created_at, updated_at)
VALUES ('{name}', '{nip}', '{email}', '{password_hash}', '{role}', {superior_nip}, true, NOW(), NOW())
ON CONFLICT (nip) DO UPDATE SET
    role = '{role}',
    superior_nip = {superior_nip},
    updated_at = NOW();"""

# PostgreSQL compatible version
SQL_UPSERT_USER_PG = """INSERT INTO users (name, nip, email, password, role, superior_nip, is_active, created_at, updated_at)
VALUES ('{name}', '{nip}', '{email}', '{password_hash}', '{role}', {superior_nip}, true, NOW(), NOW())
ON CONFLICT (nip) DO UPDATE SET
    role = EXCLUDED.role,
    superior_nip = EXCLUDED.superior_nip,
    updated_at = NOW();"""
