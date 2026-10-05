import { computed, ref } from 'vue'
import type { Ref } from 'vue'
import type { Ecosystem, PackageRecord, Scan } from '~/types/scan'
import { packageKey } from '~/utils/dashboard'

export type PackageRelationFilter = 'all' | 'direct' | 'transitive'
export type PackageTypeFilter = 'all' | PackageRecord['dependency_type']
export type PackageEcosystemFilter = 'all' | Ecosystem
export type PackageSortKey = 'name' | 'version' | 'type' | 'status'
export type PackageSortDirection = 'asc' | 'desc'
export type PackageStatus = 'vulnerable' | 'abandoned' | 'outdated'

const statusOrder: PackageStatus[] = ['vulnerable', 'abandoned', 'outdated']

export function usePackageFilter(scan: Ref<Scan | null>) {
  const packageSearch = ref('')
  const packageRelationFilter = ref<PackageRelationFilter>('all')
  const packageTypeFilter = ref<PackageTypeFilter>('all')
  const packageEcosystemFilter = ref<PackageEcosystemFilter>('all')
  const sortKey = ref<PackageSortKey>('name')
  const sortDirection = ref<PackageSortDirection>('asc')
  const packagePageSize = 10
  const showAllPackages = ref(false)

  const packageStatuses = computed(() => {
    const statuses = new Map<string, Set<PackageStatus>>()

    const add = (ecosystem: Ecosystem, name: string, status: PackageStatus) => {
      const key = packageKey(ecosystem, name)
      const existing = statuses.get(key) ?? new Set<PackageStatus>()

      existing.add(status)
      statuses.set(key, existing)
    }

    for (const finding of scan.value?.vulnerabilities ?? []) {
      add(finding.ecosystem, finding.package_name, 'vulnerable')
    }

    for (const finding of scan.value?.abandoned ?? []) {
      add(finding.ecosystem, finding.package_name, 'abandoned')
    }

    for (const finding of scan.value?.outdated ?? []) {
      add(finding.ecosystem, finding.package_name, 'outdated')
    }

    return statuses
  })

  const statusesFor = (pkg: PackageRecord): PackageStatus[] => {
    const statuses = packageStatuses.value.get(
      packageKey(pkg.ecosystem, pkg.name),
    )

    return statusOrder.filter((status) => statuses?.has(status))
  }

  const ecosystems = computed(() => [
    ...new Set((scan.value?.packages ?? []).map((pkg) => pkg.ecosystem)),
  ])

  const clearSearch = () => {
    packageSearch.value = ''
  }

  const sortBy = (key: PackageSortKey) => {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'

      return
    }

    sortKey.value = key
    sortDirection.value = 'asc'
  }

  const statusRank = (pkg: PackageRecord): number => {
    const [first] = statusesFor(pkg)

    return first ? statusOrder.indexOf(first) : statusOrder.length
  }

  const compare = (first: PackageRecord, second: PackageRecord): number => {
    switch (sortKey.value) {
      case 'version':
        return first.installed_version.localeCompare(
          second.installed_version,
          undefined,
          { numeric: true },
        )
      case 'type':
        return first.dependency_type.localeCompare(second.dependency_type)
      case 'status':
        return statusRank(first) - statusRank(second)
      case 'name':
        return first.name.localeCompare(second.name)
    }
  }

  const filteredPackages = computed(() => {
    if (!scan.value) return []

    const term = packageSearch.value.trim().toLowerCase()

    const packages = scan.value.packages.filter((pkg) => {
      if (packageRelationFilter.value === 'direct' && !pkg.is_direct) {
        return false
      }

      if (packageRelationFilter.value === 'transitive' && pkg.is_direct) {
        return false
      }

      if (
        packageTypeFilter.value !== 'all' &&
        pkg.dependency_type !== packageTypeFilter.value
      ) {
        return false
      }

      if (
        packageEcosystemFilter.value !== 'all' &&
        pkg.ecosystem !== packageEcosystemFilter.value
      ) {
        return false
      }

      if (!term) {
        return true
      }

      return (
        pkg.name.toLowerCase().includes(term) ||
        pkg.installed_version.toLowerCase().includes(term)
      )
    })

    const direction = sortDirection.value === 'asc' ? 1 : -1

    return packages.sort(
      (first, second) =>
        compare(first, second) * direction ||
        first.name.localeCompare(second.name),
    )
  })

  const visiblePackages = computed(() => {
    if (showAllPackages.value) return filteredPackages.value

    return filteredPackages.value.slice(0, packagePageSize)
  })

  const hasMorePackages = computed(() => {
    return filteredPackages.value.length > packagePageSize
  })

  const hasActiveFilters = computed(
    () =>
      packageRelationFilter.value !== 'all' ||
      packageTypeFilter.value !== 'all' ||
      packageEcosystemFilter.value !== 'all',
  )

  const togglePackages = () => {
    showAllPackages.value = !showAllPackages.value
  }

  return {
    packageSearch,
    packageRelationFilter,
    packageTypeFilter,
    packageEcosystemFilter,
    ecosystems,
    sortKey,
    sortDirection,
    sortBy,
    statusesFor,
    showAllPackages,
    clearSearch,
    filteredPackages,
    visiblePackages,
    hasMorePackages,
    hasActiveFilters,
    togglePackages,
  }
}
