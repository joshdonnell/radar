<script setup lang="ts">
import type { Scan } from '@/types/scan'
import RadarBadge from './RadarBadge.vue'
import RadarButton from './RadarButton.vue'
import StatCard from './StatCard.vue'

defineProps<{
  scan: Scan
  scanning: boolean
}>()

defineEmits<{
  runScan: []
}>()
</script>

<template>
  <div
    id="radar-overview"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-white/[0.04] bg-[#0f1420]/80 shadow-xl shadow-black/20"
  >
    <div
      class="flex items-center justify-between border-b border-white/[0.04] px-5 py-3.5"
    >
      <div class="flex items-center gap-2">
        <p class="text-xs font-medium text-slate-500">Latest scan</p>
        <span class="h-0.5 w-0.5 rounded-full bg-slate-700" />
        <p class="text-xs text-slate-600">
          {{ scan.created_at_human ?? 'Time unknown' }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <radar-button
          variant="secondary"
          size="sm"
          :loading="scanning"
          :aria-label="
            scanning ? 'Scanning dependencies' : 'Run new dependency scan'
          "
          @click="$emit('runScan')"
        >
          <template #icon>
            <svg
              class="h-3 w-3"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <polyline points="23 4 23 10 17 10" />
              <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10" />
            </svg>
          </template>
          {{ scanning ? 'Scanning...' : 'Run Scan' }}
        </radar-button>
        <radar-badge color="white">
          <template #dot>
            <span class="h-1 w-1 rounded-full bg-cyan-400" />
          </template>
          {{ scan.package_count }} packages
        </radar-badge>
      </div>
    </div>

    <dl class="grid divide-x divide-white/[0.04] sm:grid-cols-2 lg:grid-cols-4">
      <stat-card
        label="Health Score"
        :value="scan.score"
        suffix="/ 100"
        color="cyan"
      >
        <template #icon>
          <svg
            class="h-3 w-3"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <path
              d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
            />
          </svg>
        </template>
      </stat-card>
      <stat-card label="Packages" :value="scan.package_count" color="white">
        <template #icon>
          <svg
            class="h-3 w-3"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <path
              d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"
            />
            <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
            <line x1="12" y1="22.08" x2="12" y2="12" />
          </svg>
        </template>
      </stat-card>
      <stat-card
        label="Vulnerabilities"
        :value="scan.vulnerability_count"
        :color="scan.vulnerability_count > 0 ? 'rose' : 'emerald'"
      >
        <template #icon>
          <svg
            class="h-3 w-3"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <path
              d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
            />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
          </svg>
        </template>
      </stat-card>
      <stat-card
        label="Abandoned"
        :value="scan.abandoned.length"
        :color="scan.abandoned.length > 0 ? 'amber' : 'emerald'"
      >
        <template #icon>
          <svg
            class="h-3 w-3"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
        </template>
      </stat-card>
    </dl>
  </div>
</template>
