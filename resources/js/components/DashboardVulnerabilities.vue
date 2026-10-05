<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import RadarEmptyState from '~/components/RadarEmptyState.vue'
import RadarSectionHeader from '~/components/RadarSectionHeader.vue'
import RadarSegmentedControl from '~/components/RadarSegmentedControl.vue'
import VulnerabilityCard from '~/components/VulnerabilityCard.vue'
import type { Severity, VulnerabilityRecord } from '~/types/scan'
import { countBy, severities, sortBySeverity } from '~/utils/dashboard'

const props = defineProps<{
  vulnerabilities: VulnerabilityRecord[]
}>()

defineEmits<{
  inspectPackage: [packageName: string]
}>()

type SeverityFilter = 'all' | Severity

const severityFilter = ref<SeverityFilter>('all')

const severityLabels: Record<Severity, string> = {
  critical: 'Critical',
  high: 'High',
  medium: 'Medium',
  low: 'Low',
  unknown: 'Unknown',
}

const severityCounts = computed(() =>
  countBy(props.vulnerabilities, severities, (finding) => finding.severity),
)

const filterOptions = computed(() => [
  { label: 'All', value: 'all' as SeverityFilter },
  ...severities
    .filter((severity) => severityCounts.value[severity] > 0)
    .map((severity) => ({
      label: severityLabels[severity],
      value: severity as SeverityFilter,
      count: severityCounts.value[severity],
    })),
])

const visibleVulnerabilities = computed(() =>
  sortBySeverity(props.vulnerabilities).filter(
    (finding) =>
      severityFilter.value === 'all' ||
      finding.severity === severityFilter.value,
  ),
)

// A new scan may no longer contain the selected severity.
watch(severityCounts, (counts) => {
  if (severityFilter.value !== 'all' && counts[severityFilter.value] === 0) {
    severityFilter.value = 'all'
  }
})
</script>

<template>
  <section
    id="radar-vulnerabilities"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-border bg-surface"
  >
    <RadarSectionHeader
      title="Vulnerabilities"
      subtitle="Security advisories detected during the latest scan, most severe first."
      :count="vulnerabilities.length + ' found'"
      :count-color="vulnerabilities.length ? 'danger' : 'neutral'"
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
    </RadarSectionHeader>

    <div
      v-if="filterOptions.length > 2"
      class="border-b border-border px-5 py-3"
    >
      <RadarSegmentedControl
        v-model="severityFilter"
        label="Filter by severity"
        :options="filterOptions"
      />
    </div>

    <div v-if="visibleVulnerabilities.length" class="divide-y divide-border">
      <VulnerabilityCard
        v-for="vulnerability in visibleVulnerabilities"
        :key="`${vulnerability.ecosystem}:${vulnerability.package_name}:${vulnerability.id}`"
        :vulnerability="vulnerability"
        @inspect-package="$emit('inspectPackage', $event)"
      />
    </div>

    <RadarEmptyState
      v-else
      message="No vulnerabilities recorded in this scan."
    />
  </section>
</template>
