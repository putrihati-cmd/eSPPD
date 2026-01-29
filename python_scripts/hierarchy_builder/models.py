"""
Data models for Smart Hierarchy Builder
"""

from dataclasses import dataclass, field
from typing import Optional, List
from datetime import datetime


@dataclass
class EmployeeNode:
    """Represents an employee in the hierarchy tree"""
    nip: str
    name: str
    position: str  # Jabatan fungsional (Lektor, Profesor, etc)
    structural_role: str = ""  # Jabatan struktural (Dekan, Kaprodi, etc)
    faculty: str = ""
    level: int = 1  # Default to staff level
    superior_nip: Optional[str] = None
    approval_limit: int = 0
    birth_date: Optional[str] = None
    email: Optional[str] = None
    phone: Optional[str] = None
    golongan: str = ""
    category: str = "Dosen"  # Dosen/Pegawai
    
    # Derived fields
    password_hash: str = ""
    password_plain: str = ""
    
    def __post_init__(self):
        # Clean up NIP
        self.nip = str(self.nip).strip().replace(" ", "")
        
    @property
    def display_name(self) -> str:
        """Returns name with structural role if any"""
        if self.structural_role:
            return f"{self.name} ({self.structural_role})"
        return self.name
    
    @property
    def level_name(self) -> str:
        """Returns human-readable level name"""
        level_names = {
            6: "Rektor",
            5: "Wakil Rektor",
            4: "Dekan",
            3: "Wakil Dekan",
            2: "Manager",
            1: "Staff"
        }
        return level_names.get(self.level, "Unknown")
    
    def to_dict(self) -> dict:
        """Convert to dictionary for JSON export"""
        return {
            "nip": self.nip,
            "name": self.name,
            "position": self.position,
            "structural_role": self.structural_role,
            "faculty": self.faculty,
            "level": self.level,
            "level_name": self.level_name,
            "superior_nip": self.superior_nip,
            "approval_limit": self.approval_limit,
            "category": self.category
        }


@dataclass
class HierarchyReport:
    """Summary report of hierarchy generation"""
    total_processed: int = 0
    level_counts: dict = field(default_factory=dict)
    warnings: List[str] = field(default_factory=list)
    errors: List[str] = field(default_factory=list)
    generated_at: datetime = field(default_factory=datetime.now)
    
    def add_warning(self, message: str):
        self.warnings.append(message)
        
    def add_error(self, message: str):
        self.errors.append(message)
        
    def to_dict(self) -> dict:
        return {
            "total_processed": self.total_processed,
            "level_counts": self.level_counts,
            "warnings_count": len(self.warnings),
            "warnings": self.warnings[:20],  # Limit to first 20
            "errors_count": len(self.errors),
            "errors": self.errors[:20],
            "generated_at": self.generated_at.isoformat()
        }
