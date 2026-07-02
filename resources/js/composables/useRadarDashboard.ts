import { computed, inject, provide, ref } from 'vue'
import type { InjectionKey } from 'vue'
import { usePackageFilter } from '~/composables/usePackageFilter'
import { useScanData } from '~/composables/useScanData'
import { useSectionObserver } from '~/composables/useSectionObserver'
import type { RadarConfig } from '~/types/radar'
import { buildPackageBreakdown } from '~/utils/packages'

const sectionScrollOffset = 80

export const dashboardSections = [
  'radar-overview',
  'radar-vulnerabilities',
  'radar-packages',
  'radar-updates',
  'radar-abandoned',
] as const

function createRadarDashboard(config: RadarConfig) {
  const sectionElements = ref<Record<string, HTMLElement | null>>({})

  const { scan, loading, scanning, runScan } = useScanData(config)
  const packageFilter = usePackageFilter(scan)
  const packageBreakdown = computed(() =>
    buildPackageBreakdown(scan.value?.packages ?? []),
  )

  const { activeSection } = useSectionObserver(
    () => dashboardSections.map((id) => sectionElements.value[id] ?? null),
    dashboardSections[0],
  )

  const registerSection = (id: string, element: HTMLElement | null) => {
    sectionElements.value = { ...sectionElements.value, [id]: element }
  }

  const scrollToSection = (id: string) => {
    const element = sectionElements.value[id]

    if (!element) return

    window.scrollTo({
      top:
        element.getBoundingClientRect().top +
        window.scrollY -
        sectionScrollOffset,
      behavior: 'smooth',
    })
  }

  const inspectPackage = (packageName: string) => {
    packageFilter.packageSearch.value = packageName
    scrollToSection('radar-packages')
  }

  return {
    scan,
    loading,
    scanning,
    runScan,
    packageBreakdown,
    activeSection,
    registerSection,
    scrollToSection,
    inspectPackage,
    ...packageFilter,
  }
}

export type RadarDashboard = ReturnType<typeof createRadarDashboard>

const radarDashboardKey: InjectionKey<RadarDashboard> =
  Symbol('radar-dashboard')

export function provideRadarDashboard(config: RadarConfig): RadarDashboard {
  const dashboard = createRadarDashboard(config)

  provide(radarDashboardKey, dashboard)

  return dashboard
}

export function useRadarDashboard(): RadarDashboard {
  const dashboard = inject(radarDashboardKey)

  if (!dashboard) {
    throw new Error(
      'useRadarDashboard must be used within a component that calls provideRadarDashboard.',
    )
  }

  return dashboard
}
