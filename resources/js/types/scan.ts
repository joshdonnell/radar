export interface PackageRecord {
  id: string
  ecosystem: string
  name: string
  installed_version: string
  dependency_type: string
  is_direct: boolean
  source_url: string | null
  required_by?: string[]
}

export interface VulnerabilityRecord {
  id: string
  ecosystem: string
  package_name: string
  installed_version: string
  severity: string
  advisory_id: string
  cve: string | null
  affected_versions: string | null
  patched_version: string | null
  advisory_url: string | null
  is_direct: boolean
  recommendation: string | null
  suggested_command: string | null
  required_by: string[]
}

export interface OutdatedPackageRecord {
  id: string
  ecosystem: string
  package_name: string
  current_version: string
  latest_version: string
  update_type: string
  dependency_type: string
  is_direct: boolean
  suggested_command: string | null
}

export interface AbandonedPackageRecord {
  id: string
  ecosystem: string
  package_name: string
  installed_version: string
  dependency_type: string
  is_direct: boolean
  replacement_package: string | null
  recommendation: string | null
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
  created_at: string | null
  created_at_human: string | null
}
