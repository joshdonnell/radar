<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClasses } from '~/utils/colors'

export type StatTileSegment = {
  label: string
  value: number
  color: RadarColor
}

const props = withDefaults(
  defineProps<{
    label: string
    value: number
    color?: RadarColor
    segments?: StatTileSegment[]
  }>(),
  {
    color: RadarColor.Fg,
    segments: () => [],
  },
)

defineEmits<{
  select: []
}>()

const valueColor = computed(() => radarColorClasses(props.color).text)
const visibleSegments = computed(() =>
  props.segments.filter((segment) => segment.value > 0),
)
</script>

<template>
  <button
    type="button"
    class="flex cursor-pointer flex-col rounded-2xl border border-border bg-surface p-5 text-left transition-colors hover:border-border-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
    @click="$emit('select')"
  >
    <span
      class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-muted"
    >
      <span class="flex h-4 w-4 items-center justify-center">
        <slot name="icon" />
      </span>
      {{ label }}
    </span>
    <span
      class="mt-3 text-[34px] font-bold leading-none tracking-tight tabular-nums"
      :class="valueColor"
    >
      {{ value }}
    </span>
    <span
      class="mt-3 flex min-h-[18px] flex-wrap gap-x-3 gap-y-1 text-[11.5px]"
    >
      <span
        v-for="segment in visibleSegments"
        :key="segment.label"
        class="inline-flex items-center gap-1.5 text-muted"
      >
        <span
          class="h-1.5 w-1.5 rounded-full bg-current"
          :class="radarColorClasses(segment.color).text"
        />
        <span class="font-semibold tabular-nums text-fg/90">{{
          segment.value
        }}</span>
        {{ segment.label }}
      </span>
      <span v-if="!visibleSegments.length" class="text-dim">None found</span>
    </span>
  </button>
</template>
