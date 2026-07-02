<script setup lang="ts">
import DashboardAbandoned from '~/components/DashboardAbandoned.vue'
import DashboardOverview from '~/components/DashboardOverview.vue'
import DashboardPackages from '~/components/DashboardPackages.vue'
import DashboardUpdates from '~/components/DashboardUpdates.vue'
import DashboardVulnerabilities from '~/components/DashboardVulnerabilities.vue'
import RadarButton from '~/components/RadarButton.vue'
import RadarInlineCopy from '~/components/RadarInlineCopy.vue'
import RadarSpinner from '~/components/RadarSpinner.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'

const { scan, loading, scanning, runScan, registerSection, inspectPackage } =
  useRadarDashboard()

const setSection = (id: string, element: unknown) => {
  registerSection(id, element instanceof HTMLElement ? element : null)
}
</script>

<template>
  <div v-if="loading" class="flex flex-col items-center justify-center py-24">
    <RadarSpinner />
    <p class="mt-4 text-xs font-medium uppercase tracking-widest text-dim">
      Loading...
    </p>
  </div>

  <div v-else-if="scan" class="relative flex flex-col gap-5">
    <div
      v-if="scanning"
      class="fixed inset-0 h-full z-30 flex bg-bg/60 backdrop-blur-sm flex items-center justify-center"
      role="status"
      aria-live="polite"
    >
      <div class="flex flex-col items-center gap-3">
        <RadarSpinner />
        <p class="text-xs font-medium uppercase tracking-widest text-muted">
          Scanning dependencies...
        </p>
      </div>
    </div>

    <div
      :ref="(el) => setSection('radar-overview', el)"
      data-section-id="radar-overview"
    >
      <DashboardOverview />
    </div>

    <div
      :ref="(el) => setSection('radar-vulnerabilities', el)"
      data-section-id="radar-vulnerabilities"
    >
      <DashboardVulnerabilities
        :vulnerabilities="scan.vulnerabilities"
        @inspect-package="inspectPackage"
      />
    </div>

    <div
      :ref="(el) => setSection('radar-packages', el)"
      data-section-id="radar-packages"
    >
      <DashboardPackages />
    </div>

    <div
      :ref="(el) => setSection('radar-updates', el)"
      data-section-id="radar-updates"
    >
      <DashboardUpdates :outdated-packages="scan.outdated" />
    </div>

    <div
      :ref="(el) => setSection('radar-abandoned', el)"
      data-section-id="radar-abandoned"
    >
      <DashboardAbandoned :abandoned-packages="scan.abandoned" />
    </div>
  </div>

  <div
    v-else
    class="mx-auto flex min-h-[calc(100vh-12rem)] max-w-2xl flex-col items-center justify-center px-6 py-16 text-center"
  >
    <div
      class="flex h-16 w-16 items-center justify-center rounded-2xl border border-border-strong bg-surface-2 text-fg"
    >
      <svg
        class="h-7 w-7"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <circle cx="12" cy="12" r="9" />
        <circle cx="12" cy="12" r="4.5" />
        <circle cx="12" cy="12" r="1" />
      </svg>
    </div>

    <p
      class="mt-6 text-[11px] font-semibold uppercase tracking-[0.2em] text-dim"
    >
      Laravel Radar
    </p>
    <h2 class="mt-3 text-3xl font-bold tracking-tight text-fg">No scans yet</h2>
    <p class="mt-3 max-w-md text-sm leading-6 text-muted">
      Run your first dependency health scan and Radar will show packages,
      vulnerabilities, updates, abandoned packages, and the health score here.
    </p>

    <RadarButton
      class="mt-8"
      variant="primary"
      :loading="scanning"
      :aria-label="scanning ? 'Running scan' : 'Run first dependency scan'"
      @click="runScan"
    >
      <template #icon>
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M8 5v14l11-7z" />
        </svg>
      </template>
      {{ scanning ? 'Running scan...' : 'Run first scan' }}
    </RadarButton>

    <p
      class="mt-4 flex flex-wrap items-center justify-center gap-1.5 text-xs text-dim"
    >
      Prefer the terminal? Run
      <RadarInlineCopy text="php artisan radar:scan" />
    </p>
  </div>
</template>
