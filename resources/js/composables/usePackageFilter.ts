import { computed, ref } from 'vue'
import type { Ref } from 'vue'
import type { Scan } from '@/types/scan'

export type PackageRelationFilter = 'all' | 'direct' | 'transitive'
export type PackageTypeFilter = 'all' | 'production' | 'development' | 'peer'

export function tousePackageFilter(scan: Ref<Scan | null>) {
  const packageSearch = ref('')
  const packageRelationFilter = ref<PackageRelationFilter>('all')
  const packageTypeFilter = ref<PackageTypeFilter>('all')
  const packagePageSize = 10
  const showAllPackages = ref(false)

  const clearSearch = () => {
    packageSearch.value = ''
  }

  const filteredPackages = computed(() => {
    if (!scan.value) return []

    const term = packageSearch.value.trim().toLowerCase()

    return scan.value.packages.filter((pkg) => {
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

      if (!term) {
        return true
      }

      return (
        pkg.name.toLowerCase().includes(term) ||
        pkg.installed_version.toLowerCase().includes(term)
      )
    })
  })

  const visiblePackages = computed(() => {
    if (showAllPackages.value) return filteredPackages.value

    return filteredPackages.value.slice(0, packagePageSize)
  })

  const hasMorePackages = computed(() => {
    return filteredPackages.value.length > packagePageSize
  })

  const togglePackages = () => {
    showAllPackages.value = !showAllPackages.value
  }

  return {
    packageSearch,
    packageRelationFilter,
    packageTypeFilter,
    showAllPackages,
    clearSearch,
    filteredPackages,
    visiblePackages,
    hasMorePackages,
    togglePackages,
  }
}
