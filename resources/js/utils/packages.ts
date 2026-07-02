import type { PackageRecord } from '~/types/scan'

export type PackageBreakdownRow = {
  label: string
  value: number
}

const ecosystemLabels: Record<string, string> = {
  composer: 'Composer',
  npm: 'Node',
}

function ecosystemLabel(ecosystem: string): string {
  return (
    ecosystemLabels[ecosystem] ??
    ecosystem.charAt(0).toUpperCase() + ecosystem.slice(1)
  )
}

export function buildPackageBreakdown(
  packages: PackageRecord[],
): PackageBreakdownRow[] {
  const groups = new Map<string, { direct: number; transitive: number }>()

  for (const pkg of packages) {
    const group = groups.get(pkg.ecosystem) ?? { direct: 0, transitive: 0 }

    if (pkg.is_direct) {
      group.direct++
    } else {
      group.transitive++
    }

    groups.set(pkg.ecosystem, group)
  }

  const rows: PackageBreakdownRow[] = []

  for (const [ecosystem, counts] of groups) {
    const label = ecosystemLabel(ecosystem)

    if (counts.direct > 0) {
      rows.push({ label: `${label} Direct`, value: counts.direct })
    }

    if (counts.transitive > 0) {
      rows.push({ label: `${label} Transitive`, value: counts.transitive })
    }
  }

  return rows
}
