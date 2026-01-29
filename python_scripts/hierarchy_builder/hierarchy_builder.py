#!/usr/bin/env python3
"""
Smart Hierarchy Builder for e-SPPD
Automatically generates approval hierarchy from Excel data

Usage:
    python hierarchy_builder.py --input storage/data_pegawai.xlsx
"""

import argparse
import json
import os
import re
import sys
from datetime import datetime, timedelta
from pathlib import Path
from typing import Dict, List, Optional, Tuple

try:
    import pandas as pd
    import bcrypt
    from tqdm import tqdm
except ImportError as e:
    print(f"Missing dependency: {e}")
    print("Install with: pip install pandas openpyxl bcrypt tqdm")
    sys.exit(1)

from config import LEVELS, LEVEL_PATTERNS, FACULTY_KEYWORDS, SQL_UPDATE_EMPLOYEE, SQL_UPSERT_USER_PG
from models import EmployeeNode, HierarchyReport


class SmartHierarchyBuilder:
    """
    Builds approval hierarchy from Excel employee data
    """
    
    def __init__(self, excel_path: str, output_dir: str = "output"):
        self.excel_path = excel_path
        self.output_dir = Path(output_dir)
        self.employees: Dict[str, EmployeeNode] = {}
        self.report = HierarchyReport()
        
        # Create output directories
        (self.output_dir / "sql").mkdir(parents=True, exist_ok=True)
        (self.output_dir / "logs").mkdir(parents=True, exist_ok=True)
        
    def load_excel(self) -> pd.DataFrame:
        """Load and validate Excel file"""
        print(f"📂 Loading: {self.excel_path}")
        
        df = pd.read_excel(self.excel_path, dtype={'NIP': str, 'NIP Spaceless': str})
        
        # Try to find NIP column
        nip_col = None
        for col in ['NIP Spaceless', 'NIP', 'nip']:
            if col in df.columns:
                nip_col = col
                break
                
        if not nip_col:
            raise ValueError("Excel must have 'NIP' or 'NIP Spaceless' column")
            
        # Rename columns to standard names
        column_mapping = {
            nip_col: 'nip',
            'Nama dengan Gelar': 'name',
            'Nama': 'name',
            'Jabatan': 'position',
            'Tugas Tambahan': 'structural_role',
            'Golongan': 'golongan',
            'Kategori': 'category',
            'Tanggal Lahir': 'birth_date',
            'Email': 'email',
            'Telepon': 'phone'
        }
        
        df = df.rename(columns={k: v for k, v in column_mapping.items() if k in df.columns})
        
        print(f"✅ Loaded {len(df)} rows")
        return df
        
    def classify_role(self, position: str, structural_role: str) -> Tuple[int, str]:
        """
        Classify employee to a level based on their roles
        Returns (level, role_slug)
        """
        combined = f"{position} {structural_role}".strip()
        
        # Check patterns from highest to lowest level
        for level in sorted(LEVEL_PATTERNS.keys(), reverse=True):
            pattern = LEVEL_PATTERNS[level]
            if pattern.search(combined):
                role_slug = LEVELS[level]['name'].lower().replace(' ', '_')
                return level, role_slug
                
        # Default to staff
        return 1, 'staff'
        
    def extract_faculty(self, text: str) -> str:
        """Extract faculty from text using keywords"""
        text_lower = text.lower() if text else ""
        
        for faculty_code, keywords in FACULTY_KEYWORDS.items():
            for keyword in keywords:
                if keyword in text_lower:
                    return faculty_code
                    
        return "UMUM"  # Default for non-faculty staff
        
    def generate_password(self, birth_date) -> Tuple[str, str]:
        """
        Generate password from birth date (DDMMYYYY)
        Returns (hash, plain)
        """
        try:
            # Handle various date formats
            if pd.isna(birth_date):
                password_plain = "12345678"
            elif isinstance(birth_date, (int, float)):
                # Excel serial date
                dt = datetime(1899, 12, 30) + timedelta(days=int(birth_date))
                password_plain = dt.strftime('%d%m%Y')
            elif isinstance(birth_date, datetime):
                password_plain = birth_date.strftime('%d%m%Y')
            else:
                # Try parsing string
                for fmt in ['%d/%m/%Y', '%Y-%m-%d', '%d-%m-%Y']:
                    try:
                        dt = datetime.strptime(str(birth_date), fmt)
                        password_plain = dt.strftime('%d%m%Y')
                        break
                    except:
                        continue
                else:
                    password_plain = "12345678"
                    
            # Hash with bcrypt
            password_hash = bcrypt.hashpw(password_plain.encode(), bcrypt.gensalt(rounds=10))
            return password_hash.decode(), password_plain
            
        except Exception as e:
            self.report.add_warning(f"Password generation error: {e}")
            fallback = bcrypt.hashpw(b'12345678', bcrypt.gensalt(rounds=10))
            return fallback.decode(), '12345678'
            
    def find_superior(self, employee: EmployeeNode) -> Optional[str]:
        """
        Find superior NIP for an employee based on faculty and level
        """
        if employee.level >= 6:  # Rektor has no superior
            return None
            
        target_level = employee.level + 1
        
        # Search for superior in same faculty, then any faculty
        for faculty_filter in [employee.faculty, None]:
            for nip, emp in self.employees.items():
                if emp.level == target_level:
                    if faculty_filter is None or emp.faculty == faculty_filter:
                        return emp.nip
                        
            # If not found, try one level higher
            target_level += 1
            if target_level > 6:
                break
                
        # Fallback: assign to Rektor
        for nip, emp in self.employees.items():
            if emp.level == 6:
                return emp.nip
                
        return None
        
    def build_hierarchy(self):
        """Main method to build the complete hierarchy"""
        print("\n🔨 Building Hierarchy...\n")
        
        df = self.load_excel()
        
        # Phase 1: Create all employee nodes
        print("Phase 1: Classifying employees...")
        for _, row in tqdm(df.iterrows(), total=len(df), desc="Classifying"):
            nip = str(row.get('nip', '')).strip()
            
            if not nip or len(nip) < 10:
                self.report.add_warning(f"Invalid NIP: {nip}")
                continue
                
            # Check for duplicates
            if nip in self.employees:
                self.report.add_warning(f"Duplicate NIP: {nip}")
                continue
                
            # Create employee node
            emp = EmployeeNode(
                nip=nip,
                name=str(row.get('name', 'Unknown')),
                position=str(row.get('position', '')),
                structural_role=str(row.get('structural_role', '')),
                golongan=str(row.get('golongan', '')),
                category=str(row.get('category', 'Dosen')),
                birth_date=row.get('birth_date'),
                email=str(row.get('email', '')) if pd.notna(row.get('email')) else None,
                phone=str(row.get('phone', '')) if pd.notna(row.get('phone')) else None
            )
            
            # Classify level
            emp.level, emp.structural_role = self.classify_role(
                emp.position, 
                emp.structural_role
            )
            
            # Extract faculty
            emp.faculty = self.extract_faculty(f"{emp.structural_role} {emp.position}")
            
            # Set approval limit
            emp.approval_limit = LEVELS.get(emp.level, {}).get('budget', 0)
            
            # Generate password
            emp.password_hash, emp.password_plain = self.generate_password(emp.birth_date)
            
            self.employees[nip] = emp
            
        # Phase 2: Link superiors
        print("\nPhase 2: Linking superiors...")
        for emp in tqdm(self.employees.values(), desc="Linking"):
            emp.superior_nip = self.find_superior(emp)
            
            if emp.superior_nip is None and emp.level < 6:
                self.report.add_warning(f"No superior found for: {emp.name} (Level {emp.level})")
                
        # Generate report
        self.report.total_processed = len(self.employees)
        for emp in self.employees.values():
            level_name = emp.level_name
            self.report.level_counts[level_name] = self.report.level_counts.get(level_name, 0) + 1
            
        print(f"\n✅ Processed {self.report.total_processed} employees")
        
    def generate_sql(self):
        """Generate SQL files for database updates"""
        print("\n📝 Generating SQL files...")
        
        # SQL for updating employees table
        employee_sql = ["-- Auto-generated by Hierarchy Builder", "-- " + datetime.now().isoformat(), "", "BEGIN;", ""]
        
        # SQL for upserting users table
        users_sql = ["-- Auto-generated by Hierarchy Builder", "-- " + datetime.now().isoformat(), "", "BEGIN;", ""]
        
        for emp in self.employees.values():
            sup_nip = f"'{emp.superior_nip}'" if emp.superior_nip else "NULL"
            
            # Employee update
            employee_sql.append(SQL_UPDATE_EMPLOYEE.format(
                level=emp.level,
                role=emp.structural_role or emp.position,
                faculty=emp.faculty,
                superior_nip=sup_nip,
                limit=emp.approval_limit,
                nip=emp.nip
            ))
            
            # User upsert
            email = emp.email or f"{emp.nip}@uinsaizu.ac.id"
            users_sql.append(SQL_UPSERT_USER_PG.format(
                name=emp.name.replace("'", "''"),
                nip=emp.nip,
                email=email,
                password_hash=emp.password_hash,
                role=emp.level_name.lower().replace(' ', '_'),
                superior_nip=sup_nip
            ))
            
        employee_sql.extend(["", "COMMIT;"])
        users_sql.extend(["", "COMMIT;"])
        
        # Write files
        with open(self.output_dir / "sql" / "01_update_employees.sql", "w") as f:
            f.write("\n".join(employee_sql))
            
        with open(self.output_dir / "sql" / "02_upsert_users.sql", "w") as f:
            f.write("\n".join(users_sql))
            
        print(f"✅ Generated: {self.output_dir}/sql/01_update_employees.sql")
        print(f"✅ Generated: {self.output_dir}/sql/02_upsert_users.sql")
        
    def export_report(self):
        """Export hierarchy mapping report"""
        print("\n📊 Exporting reports...")
        
        # JSON report
        hierarchy_data = {
            "summary": self.report.to_dict(),
            "employees": [emp.to_dict() for emp in self.employees.values()]
        }
        
        with open(self.output_dir / "logs" / "hierarchy_report.json", "w") as f:
            json.dump(hierarchy_data, f, indent=2, ensure_ascii=False)
            
        # Warnings file
        with open(self.output_dir / "logs" / "warnings.txt", "w") as f:
            f.write(f"Generated: {datetime.now().isoformat()}\n")
            f.write(f"Total warnings: {len(self.report.warnings)}\n\n")
            for warning in self.report.warnings:
                f.write(f"⚠️ {warning}\n")
                
        print(f"✅ Generated: {self.output_dir}/logs/hierarchy_report.json")
        print(f"✅ Generated: {self.output_dir}/logs/warnings.txt")
        
    def print_summary(self):
        """Print summary to console"""
        print("\n" + "="*50)
        print("✅ Hierarchy Generated Successfully!")
        print("="*50)
        print(f"\n📊 Summary:")
        print(f"   Total Processed: {self.report.total_processed}")
        for level_name, count in sorted(self.report.level_counts.items()):
            print(f"   - {level_name}: {count}")
            
        if self.report.warnings:
            print(f"\n⚠️ Warnings: {len(self.report.warnings)}")
            for warning in self.report.warnings[:5]:
                print(f"   - {warning}")
            if len(self.report.warnings) > 5:
                print(f"   ... and {len(self.report.warnings) - 5} more")
                
        print(f"\n📁 Files generated in: {self.output_dir}/")
        print("   - sql/01_update_employees.sql")
        print("   - sql/02_upsert_users.sql")
        print("   - logs/hierarchy_report.json")
        print("   - logs/warnings.txt")
        print("\n" + "="*50)


def main():
    parser = argparse.ArgumentParser(description="Smart Hierarchy Builder for e-SPPD")
    parser.add_argument("--input", "-i", required=True, help="Path to Excel file")
    parser.add_argument("--output", "-o", default="output", help="Output directory")
    args = parser.parse_args()
    
    if not os.path.exists(args.input):
        print(f"❌ Error: File not found: {args.input}")
        sys.exit(1)
        
    builder = SmartHierarchyBuilder(args.input, args.output)
    builder.build_hierarchy()
    builder.generate_sql()
    builder.export_report()
    builder.print_summary()


if __name__ == "__main__":
    main()
