<script setup lang="ts">
import { computed } from 'vue'
import type { ScanWarning } from '~/types/scan'
import { ecosystemLabel } from '~/utils/dashboard'

const props = defineProps<{
  warnings: ScanWarning[]
}>()

const checkLabels: Record<ScanWarning['check'], string> = {
  inventory: 'package list',
  vulnerabilities: 'vulnerability audit',
  outdated: 'update check',
}

const hasFailedChecks = computed(() =>
  props.warnings.some((warning) => warning.check !== 'inventory'),
)
</script>

<template>
  <div
    class="rounded-2xl border border-warning/30 bg-warning/[0.06] px-5 py-4"
    role="status"
  >
    <div class="flex items-start gap-3">
      <svg
        class="mt-0.5 h-4 w-4 shrink-0 text-warning"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <path d="M12 3l9 16H3z" />
        <path d="M12 10v4M12 17h.01" />
      </svg>
      <div class="min-w-0 flex-1">
        <h2 class="text-[14px] font-semibold text-fg">
          {{
            hasFailedChecks
              ? 'This scan is incomplete'
              : 'Some results are limited'
          }}
        </h2>
        <p class="mt-1 text-[12px] text-muted">
          {{
            hasFailedChecks
              ? 'Some checks could not run, so findings may be missing and the health score may be higher than it should be.'
              : 'Radar could not read everything for this project.'
          }}
        </p>
        <ul class="mt-3 flex flex-col gap-2">
          <li
            v-for="(warning, index) in warnings"
            :key="index"
            class="flex flex-col gap-1 text-[12px] sm:flex-row sm:gap-2"
          >
            <span class="shrink-0 font-semibold text-warning">
              {{ ecosystemLabel(warning.ecosystem) }}
              {{ checkLabels[warning.check] }}
            </span>
            <span
              class="min-w-0 break-words font-mono text-[11.5px] text-fg/80"
            >
              {{ warning.message }}
            </span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
