<script setup lang="ts">
import { useWindowScroll } from '@vueuse/core'
import { useTemplateRef, watch } from 'vue'
import { usePackageFilter } from '@/composables/usePackageFilter'
import { useScanData } from '@/composables/useScanData'
import { useSectionObserver } from '@/composables/useSectionObserver'
import type { RadarConfig } from '@/types/radar'
import DashboardAbandoned from './DashboardAbandoned.vue'
import DashboardOverview from './DashboardOverview.vue'
import DashboardPackages from './DashboardPackages.vue'
import DashboardUpdates from './DashboardUpdates.vue'
import DashboardVulnerabilities from './DashboardVulnerabilities.vue'
import RadarButton from './RadarButton.vue'
import RadarSpinner from './RadarSpinner.vue'

const props = defineProps<{
  radarConfig: RadarConfig
}>()

const emit = defineEmits<{
  activeSection: [sectionId: string]
  scanState: [hasScan: boolean]
}>()

const sectionScrollOffset = 80
const overviewSection = useTemplateRef<HTMLElement>('overviewSection')
const vulnerabilitiesSection = useTemplateRef<HTMLElement>(
  'vulnerabilitiesSection',
)
const packagesSection = useTemplateRef<HTMLElement>('packagesSection')
const updatesSection = useTemplateRef<HTMLElement>('updatesSection')
const abandonedSection = useTemplateRef<HTMLElement>('abandonedSection')
const sectionElements: Record<string, { readonly value: HTMLElement | null }> =
  {
    'radar-overview': overviewSection,
    'radar-vulnerabilities': vulnerabilitiesSection,
    'radar-packages': packagesSection,
    'radar-updates': updatesSection,
    'radar-abandoned': abandonedSection,
  }

const { scan, loading, scanning, runScan } = useScanData(props.radarConfig)
const { y: windowScrollY } = useWindowScroll({ behavior: 'smooth' })
const { activeSection } = useSectionObserver(sectionElements)
const {
  packageSearch,
  showAllPackages,
  clearSearch,
  filteredPackages,
  visiblePackages,
  hasMorePackages,
  togglePackages,
} = usePackageFilter(scan)

watch(
  scan,
  (currentScan) => {
    emit('scanState', currentScan !== null)
  },
  { immediate: true },
)

watch(
  activeSection,
  (sectionId) => {
    if (sectionId) emit('activeSection', sectionId)
  },
  { immediate: true },
)

const scrollToSection = (id: string) => {
  const element = sectionElements[id]?.value

  if (!element) return

  windowScrollY.value =
    element.getBoundingClientRect().top +
    windowScrollY.value -
    sectionScrollOffset
}

const inspectPackage = (packageName: string) => {
  packageSearch.value = packageName
  scrollToSection('radar-packages')
}

defineExpose({ scrollToSection })
</script>

<template>
  <div>
    <div v-if="loading" class="flex flex-col items-center justify-center py-24">
      <radar-spinner />
      <p
        class="mt-4 text-xs font-medium uppercase tracking-widest text-slate-600"
      >
        Loading...
      </p>
    </div>

    <div v-else-if="scan" class="relative space-y-5">
      <div
        v-if="scanning"
        class="absolute inset-0 z-10 flex items-start justify-center rounded-2xl bg-[#0B0F19]/60 pt-32 backdrop-blur-sm transition-opacity"
        role="status"
        aria-live="polite"
      >
        <div class="flex flex-col items-center gap-3">
          <radar-spinner />
          <p
            class="text-xs font-medium uppercase tracking-widest text-cyan-400"
          >
            Scanning dependencies...
          </p>
        </div>
      </div>

      <div ref="overviewSection" data-section-id="radar-overview">
        <dashboard-overview
          :scan="scan"
          :scanning="scanning"
          @run-scan="runScan"
        />
      </div>

      <div ref="vulnerabilitiesSection" data-section-id="radar-vulnerabilities">
        <dashboard-vulnerabilities
          :vulnerabilities="scan.vulnerabilities"
          @inspect-package="inspectPackage"
        />
      </div>

      <div ref="packagesSection" data-section-id="radar-packages">
        <dashboard-packages
          v-model:package-search="packageSearch"
          :filtered-packages="filteredPackages"
          :visible-packages="visiblePackages"
          :has-more-packages="hasMorePackages"
          :show-all-packages="showAllPackages"
          @clear-search="clearSearch"
          @toggle-packages="togglePackages"
        />
      </div>

      <div ref="updatesSection" data-section-id="radar-updates">
        <dashboard-updates :outdated-packages="scan.outdated" />
      </div>

      <div ref="abandonedSection" data-section-id="radar-abandoned">
        <dashboard-abandoned :abandoned-packages="scan.abandoned" />
      </div>
    </div>

    <div
      v-else
      class="mx-auto flex min-h-[calc(100vh-9rem)] max-w-2xl flex-col items-center justify-center px-6 py-16 text-center"
    >
      <div
        class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300 ring-1 ring-inset ring-cyan-400/20 shadow-2xl shadow-cyan-950/40"
      >
        <div class="absolute inset-0 rounded-2xl bg-cyan-400/10 blur-xl" />
        <svg
          class="relative h-7 w-7"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <circle cx="12" cy="12" r="3" />
          <path d="M12 2v4m0 12v4M2 12h4m12 0h4" stroke-linecap="round" />
        </svg>
      </div>

      <p
        class="mt-6 text-[10px] font-bold uppercase tracking-[0.35em] text-cyan-400/70"
      >
        Laravel Radar
      </p>
      <h2 class="mt-3 text-3xl font-semibold tracking-tight text-white">
        No scans yet
      </h2>
      <p class="mt-3 max-w-md text-sm leading-6 text-slate-500">
        Run your first dependency health scan and Radar will show packages,
        vulnerabilities, updates, abandoned packages, and the health score here.
      </p>

      <radar-button
        class="mt-8"
        variant="primary"
        size="md"
        :loading="scanning"
        :aria-label="scanning ? 'Running scan' : 'Run first dependency scan'"
        @click="runScan"
      >
        <template #icon>
          <svg
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <polyline points="23 4 23 10 17 10" />
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10" />
          </svg>
        </template>
        {{ scanning ? 'Running scan...' : 'Run first scan' }}
      </radar-button>

      <p class="mt-4 text-xs text-slate-600">
        Prefer the terminal? Run
        <code
          class="rounded-md bg-white/5 px-1.5 py-0.5 font-mono text-cyan-200 ring-1 ring-inset ring-white/10"
          >php artisan radar:scan</code
        >.
      </p>
    </div>
  </div>
</template>
