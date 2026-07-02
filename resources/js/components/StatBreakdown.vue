<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClasses } from '~/utils/colors'
import type { RadarColor as RadarColorValue } from '~/utils/colors'

export type StatBreakdownRow = {
  label: string
  value: number | string
  color?: RadarColorValue
}

const props = defineProps<{
  rows: StatBreakdownRow[]
}>()

const rowStyles = computed(() =>
  props.rows.map((row) => {
    const classes = radarColorClasses(row.color ?? RadarColor.Neutral)

    return {
      dot:
        row.color && row.color !== RadarColor.Neutral
          ? classes.text
          : 'text-dim',
      value:
        row.color && row.color !== RadarColor.Neutral
          ? classes.text
          : 'text-fg/80',
    }
  }),
)
</script>

<template>
  <div class="mt-4 flex flex-col gap-3 border-t border-border pt-4 text-[13px]">
    <div
      v-for="(row, index) in rows"
      :key="row.label"
      class="flex items-center justify-between"
    >
      <span class="flex items-center gap-2.5 text-muted">
        <span
          class="h-1.5 w-1.5 rounded-full bg-current"
          :class="rowStyles[index].dot"
        />
        {{ row.label }}
      </span>
      <span class="font-medium tabular-nums" :class="rowStyles[index].value">
        {{ row.value }}
      </span>
    </div>
  </div>
</template>
