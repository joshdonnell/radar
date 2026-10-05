import type { PackageRecord } from '~/types/scan'
import { ecosystemLabel } from '~/utils/dashboard'

export type PackageBreakdownRow = {
  label: string
  value: number
}

export function buildPackageBreakdown(
  packages: PackageRecord[],
): PackageBreakdownRow[] {
  const groups = new Map<
    PackageRecord['ecosystem'],
    { direct: number; transitive: number }
  >()

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
