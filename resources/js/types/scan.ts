export type Ecosystem = 'composer' | 'npm'
export type Severity = 'critical' | 'high' | 'medium' | 'low' | 'unknown'
export type UpdateType = 'major' | 'minor' | 'patch' | 'unknown'
export type DependencyType = 'production' | 'development' | 'peer'
export type ScanCheck = 'inventory' | 'vulnerabilities' | 'outdated'

export interface PackageRecord {
  id: string
  ecosystem: Ecosystem
  name: string
  installed_version: string
  dependency_type: DependencyType
  is_direct: boolean
  source_url: string | null
  required_by: string[]
}

export interface VulnerabilityRecord {
  id: string
  ecosystem: Ecosystem
  package_name: string
  installed_version: string
  severity: Severity
  advisory_id: string
  title: string | null
  cve: string | null
  affected_versions: string | null
  patched_version: string | null
  advisory_url: string | null
  is_direct: boolean
  recommendation: string | null
  suggested_command: string | null
  alternative_commands: string[]
  required_by: string[]
}

export interface OutdatedPackageRecord {
  id: string
  ecosystem: Ecosystem
  package_name: string
  current_version: string
  latest_version: string
  update_type: UpdateType
  dependency_type: DependencyType
  is_direct: boolean
  suggested_command: string | null
}

export interface AbandonedPackageRecord {
  id: string
  ecosystem: Ecosystem
  package_name: string
  installed_version: string
  dependency_type: DependencyType
  is_direct: boolean
  replacement_package: string | null
  recommendation: string | null
}

export interface ScanWarning {
  ecosystem: Ecosystem
  check: ScanCheck
  message: string
}

export interface Scan {
  id: string
  score: number | null
  package_count: number
  vulnerability_count: number
  packages: PackageRecord[]
  vulnerabilities: VulnerabilityRecord[]
  outdated: OutdatedPackageRecord[]
  abandoned: AbandonedPackageRecord[]
  warnings: ScanWarning[]
  created_at: string | null
}

export interface ScanStatus {
  pending: boolean
  queued_at: string | null
  error: string | null
}
