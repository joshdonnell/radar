<script setup lang="ts">
import { computed } from 'vue'
import StatBreakdown from '~/components/StatBreakdown.vue'
import type { StatBreakdownRow } from '~/components/StatBreakdown.vue'
import StatCard from '~/components/StatCard.vue'
import StatTile from '~/components/StatTile.vue'
import type { StatTileSegment } from '~/components/StatTile.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'
import type { UpdateType } from '~/types/scan'
import { RadarColor } from '~/utils/colors'
import type { RadarColor as RadarColorValue } from '~/utils/colors'
import { countBy, severities } from '~/utils/dashboard'

const { scan, packageBreakdown, scrollToSection } = useRadarDashboard()

const scoreColor = computed(() => {
  const score = scan.value?.score ?? null

  if (score === null) return RadarColor.Fg
  if (score >= 80) return RadarColor.Success
  if (score >= 50) return RadarColor.Warning

  return RadarColor.Danger
})

const scoreLabel = computed(() => {
  const score = scan.value?.score ?? null

  if (score === null) return 'Not scored'
  if (score >= 80) return 'Healthy'
  if (score >= 50) return 'Needs attention'

  return 'At risk'
})

const scoreBarClasses: Partial<Record<RadarColorValue, string>> = {
  [RadarColor.Success]: 'bg-success',
  [RadarColor.Warning]: 'bg-warning',
  [RadarColor.Danger]: 'bg-danger',
}

const scoreBarClass = computed(
  () => scoreBarClasses[scoreColor.value] ?? 'bg-fg',
)

const isIncomplete = computed(
  () =>
    scan.value?.warnings.some((warning) => warning.check !== 'inventory') ??
    false,
)

const packageRows = computed<StatBreakdownRow[]>(() =>
  packageBreakdown.value.map((row) => ({
    label: row.label,
    value: row.value,
    color: RadarColor.Neutral,
  })),
)

const vulnerabilitySegments = computed<StatTileSegment[]>(() => {
  const counts = countBy(
    scan.value?.vulnerabilities ?? [],
    severities,
    (finding) => finding.severity,
  )

  return [
    { label: 'critical', value: counts.critical, color: RadarColor.Danger },
    { label: 'high', value: counts.high, color: RadarColor.Danger },
    { label: 'medium', value: counts.medium, color: RadarColor.Warning },
    { label: 'low', value: counts.low, color: RadarColor.Success },
    { label: 'unknown', value: counts.unknown, color: RadarColor.Neutral },
  ]
})

const updateTypes: UpdateType[] = ['major', 'minor', 'patch', 'unknown']

const outdatedSegments = computed<StatTileSegment[]>(() => {
  const counts = countBy(
    scan.value?.outdated ?? [],
    updateTypes,
    (finding) => finding.update_type,
  )

  return [
    { label: 'major', value: counts.major, color: RadarColor.Danger },
    { label: 'minor', value: counts.minor, color: RadarColor.Warning },
    { label: 'patch', value: counts.patch, color: RadarColor.Success },
    { label: 'other', value: counts.unknown, color: RadarColor.Neutral },
  ]
})

const abandonedSegments = computed<StatTileSegment[]>(() => {
  const abandoned = scan.value?.abandoned ?? []
  const direct = abandoned.filter((finding) => finding.is_direct).length

  return [
    { label: 'direct', value: direct, color: RadarColor.Abandoned },
    {
      label: 'transitive',
      value: abandoned.length - direct,
      color: RadarColor.Neutral,
    },
  ]
})

const vulnerabilityCount = computed(() => scan.value?.vulnerability_count ?? 0)
const outdatedCount = computed(() => scan.value?.outdated.length ?? 0)
const abandonedCount = computed(() => scan.value?.abandoned.length ?? 0)
</script>

<template>
  <div v-if="scan" class="flex flex-col gap-4">
    <div class="grid gap-4 md:grid-cols-[3fr_2fr]">
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
          <div class="mt-5">
            <div
              class="h-1.5 w-full overflow-hidden rounded-full bg-surface-2"
              role="progressbar"
              :aria-valuenow="scan.score ?? 0"
              aria-valuemin="0"
              aria-valuemax="100"
              :aria-label="`Health score ${scan.score ?? 0} out of 100`"
            >
              <div
                class="h-full rounded-full transition-[width] duration-500"
                :class="scoreBarClass"
                :style="{ width: `${scan.score ?? 0}%` }"
              />
            </div>
            <p class="mt-3 text-[12px] text-muted">
              <span class="font-semibold text-fg">{{ scoreLabel }}.</span>
              Vulnerabilities lower the score the most. Outdated and abandoned
              packages have a capped penalty.
            </p>
            <p v-if="isIncomplete" class="mt-1.5 text-[12px] text-warning">
              Some checks could not run, so this score may be too high.
            </p>
          </div>
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
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
      <StatTile
        label="Vulnerabilities"
        :value="vulnerabilityCount"
        :color="vulnerabilityCount > 0 ? RadarColor.Danger : RadarColor.Success"
        :segments="vulnerabilitySegments"
        @select="scrollToSection('radar-vulnerabilities')"
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
            <path d="M12 3l9 16H3z" />
            <path d="M12 10v4M12 17h.01" />
          </svg>
        </template>
      </StatTile>

      <StatTile
        label="Outdated"
        :value="outdatedCount"
        :color="outdatedCount > 0 ? RadarColor.Warning : RadarColor.Fg"
        :segments="outdatedSegments"
        @select="scrollToSection('radar-updates')"
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
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
            <polyline points="17 6 23 6 23 12" />
          </svg>
        </template>
      </StatTile>

      <StatTile
        label="Abandoned"
        :value="abandonedCount"
        :color="abandonedCount > 0 ? RadarColor.Abandoned : RadarColor.Fg"
        :segments="abandonedSegments"
        @select="scrollToSection('radar-abandoned')"
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
      </StatTile>
    </div>
  </div>
</template>
