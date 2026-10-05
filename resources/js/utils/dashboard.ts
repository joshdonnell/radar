import type {
  Ecosystem,
  PackageRecord,
  Severity,
  UpdateType,
  VulnerabilityRecord,
} from '~/types/scan'

export const severities: Severity[] = [
  'critical',
  'high',
  'medium',
  'low',
  'unknown',
]

const severityClasses: Record<Severity, string> = {
  critical: 'bg-danger/15 text-danger ring-danger/40',
  high: 'bg-danger/10 text-danger ring-danger/25',
  medium: 'bg-warning/10 text-warning ring-warning/25',
  low: 'bg-success/10 text-success ring-success/25',
  unknown: 'bg-surface-2 text-muted ring-border-strong',
}

const updateClasses: Record<UpdateType, string> = {
  major: 'bg-danger/10 text-danger ring-danger/25',
  minor: 'bg-warning/10 text-warning ring-warning/25',
  patch: 'bg-success/10 text-success ring-success/25',
  unknown: 'bg-surface-2 text-muted ring-border-strong',
}

export function severityColor(severity: Severity): string {
  return severityClasses[severity]
}

export function updateColor(type: UpdateType): string {
  return updateClasses[type]
}

export function relationColor(isDirect: boolean): string {
  return isDirect
    ? 'bg-success/10 text-success ring-success/25'
    : 'bg-surface-2 text-muted ring-border-strong'
}

export function sortBySeverity(
  vulnerabilities: VulnerabilityRecord[],
): VulnerabilityRecord[] {
  return [...vulnerabilities].sort(
    (first, second) =>
      severities.indexOf(first.severity) - severities.indexOf(second.severity),
  )
}

export function countBy<TItem, TKey extends string>(
  items: TItem[],
  keys: readonly TKey[],
  keyOf: (item: TItem) => TKey,
): Record<TKey, number> {
  const counts = Object.fromEntries(keys.map((key) => [key, 0])) as Record<
    TKey,
    number
  >

  for (const item of items) {
    counts[keyOf(item)]++
  }

  return counts
}

export function packageKey(ecosystem: Ecosystem, name: string): string {
  return `${ecosystem}:${name}`
}

export function cveUrl(cve: string): string {
  return `https://www.cve.org/CVERecord?id=${encodeURIComponent(cve)}`
}

/**
 * npm lock files record the tarball URL as the source, which is not a useful
 * page to open, so Node packages link to the registry instead.
 */
export function packageUrl(pkg: PackageRecord): string {
  if (pkg.ecosystem === 'npm') {
    return `https://www.npmjs.com/package/${pkg.name}`
  }

  if (pkg.source_url?.startsWith('https://')) {
    return pkg.source_url
  }

  return `https://packagist.org/packages/${pkg.name}`
}

export function ecosystemLabel(ecosystem: Ecosystem): string {
  return ecosystem === 'composer' ? 'Composer' : 'Node'
}
