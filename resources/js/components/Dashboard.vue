<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Scan } from '@/types/scan'
import EmptyState from './EmptyState.vue'
import StatCard from './StatCard.vue'

const scan = ref<Scan | null>(null)
const loading = ref(true)
const dashboardPath = window.location.pathname.replace(/\/$/, '')
const latestScanUrl = `${dashboardPath}/api/scans/latest`

onMounted(async () => {
  try {
    const response = await fetch(latestScanUrl)
    if (response.ok) {
      const data = (await response.json()) as { scan: Scan | null }
      scan.value = data.scan
    }
  } catch (error) {
    console.error('Failed to load scan data:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <div v-if="loading" class="text-center py-16">
      <div class="text-slate-400">Loading...</div>
    </div>

    <div v-else-if="scan">
      <div
        class="mb-6 overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03] shadow-2xl shadow-slate-950/40"
      >
        <div class="border-b border-white/10 px-6 py-5">
          <p class="text-sm text-slate-400">Latest scan</p>
          <p class="mt-1 text-sm text-slate-500">
            {{ scan.created_at_human ?? 'Time unknown' }}
          </p>
        </div>

        <dl class="grid gap-px bg-white/10 sm:grid-cols-3">
          <stat-card label="Health Score" :value="scan.score" suffix="/ 100" />
          <stat-card label="Packages" :value="scan.package_count" />
          <stat-card
            label="Vulnerabilities"
            :value="scan.vulnerability_count"
            :color="scan.vulnerability_count > 0 ? 'rose' : 'emerald'"
          />
        </dl>
      </div>

      <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-base font-semibold text-white">Next action</h2>
        <p class="mt-2 text-sm leading-6 text-slate-400">
          Radar has stored a scan snapshot. Vulnerability and package detail
          sections will appear here as the scanner slices land.
        </p>
      </div>
    </div>

    <empty-state
      v-else
      title="No scans have been recorded yet."
      message="Run <code class='rounded-md bg-white/10 px-1.5 py-0.5 text-cyan-200'>php artisan radar:scan</code> and Radar will show the latest dependency health snapshot here."
    />
  </div>
</template>
