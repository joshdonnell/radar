<script setup lang="ts">
import { computed } from 'vue'
import StatBreakdown from '~/components/StatBreakdown.vue'
import type { StatBreakdownRow } from '~/components/StatBreakdown.vue'
import StatCard from '~/components/StatCard.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'
import { RadarColor } from '~/utils/colors'

const { scan, packageBreakdown } = useRadarDashboard()

const scoreColor = computed(() => {
  const score = scan.value?.score ?? null

  if (score === null) return RadarColor.Fg
  if (score >= 80) return RadarColor.Success
  if (score >= 50) return RadarColor.Warning

  return RadarColor.Danger
})

const healthRows = computed<StatBreakdownRow[]>(() => [
  {
    label: 'Vulnerabilities',
    value: scan.value?.vulnerability_count ?? 0,
    color: RadarColor.Danger,
  },
  {
    label: 'Abandoned',
    value: scan.value?.abandoned.length ?? 0,
    color: RadarColor.Abandoned,
  },
  {
    label: 'Outdated',
    value: scan.value?.outdated.length ?? 0,
    color: RadarColor.Warning,
  },
])

const packageRows = computed<StatBreakdownRow[]>(() =>
  packageBreakdown.value.map((row) => ({
    label: row.label,
    value: row.value,
    color: RadarColor.Neutral,
  })),
)

const vulnerabilityColor = computed(() =>
  (scan.value?.vulnerability_count ?? 0) > 0
    ? RadarColor.Danger
    : RadarColor.Success,
)

const abandonedColor = computed(() =>
  (scan.value?.abandoned.length ?? 0) > 0
    ? RadarColor.Abandoned
    : RadarColor.Fg,
)
</script>

<template>
  <div v-if="scan" class="grid gap-4 sm:grid-cols-2">
    <StatCard
      label="Health Score"
      :value="scan.score"
      suffix="/ 100"
      :color="scoreColor"
      size="lg"
    >
      <template #icon>
        <svg
          class="h-4 w-4"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M3 12h4l2 6 4-14 2 8h6" />
        </svg>
      </template>
      <template #breakdown>
        <StatBreakdown :rows="healthRows" />
      </template>
    </StatCard>

    <StatCard
      label="Packages"
      :value="scan.package_count"
      :color="RadarColor.Fg"
      size="lg"
    >
      <template #icon>
        <svg
          class="h-4 w-4"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M21 8l-9-5-9 5 9 5 9-5z" />
          <path d="M3 8v8l9 5 9-5V8" />
          <path d="M12 13v8" />
        </svg>
      </template>
      <template v-if="packageRows.length" #breakdown>
        <StatBreakdown :rows="packageRows" />
      </template>
    </StatCard>

    <StatCard
      label="Vulnerabilities"
      :value="scan.vulnerability_count"
      :color="vulnerabilityColor"
    >
      <template #icon>
        <svg
          class="h-4 w-4 text-danger"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M12 3l9 16H3z" />
          <path d="M12 10v4M12 17h.01" />
        </svg>
      </template>
    </StatCard>

    <StatCard
      label="Abandoned"
      :value="scan.abandoned.length"
      :color="abandonedColor"
    >
      <template #icon>
        <svg
          class="h-4 w-4"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M6 4h12v9a6 6 0 01-12 0z" />
          <path d="M9 21h6M12 17v4" />
        </svg>
      </template>
    </StatCard>
  </div>
</template>
