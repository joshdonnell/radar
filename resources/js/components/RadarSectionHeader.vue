<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClassList } from '../utils/colors'
import type { RadarColor as RadarColorValue } from '../utils/colors'
import RadarBadge from './RadarBadge.vue'

type SectionColor = Extract<
  RadarColorValue,
  | typeof RadarColor.Cyan
  | typeof RadarColor.Rose
  | typeof RadarColor.Amber
  | typeof RadarColor.Emerald
>

const props = withDefaults(
  defineProps<{
    title: string
    subtitle: string
    count: number | string
    countColor?: SectionColor
  }>(),
  {
    countColor: RadarColor.Cyan,
  },
)

const iconClasses = computed(() => radarColorClassList(props.countColor))
</script>

<template>
  <div
    class="flex items-center justify-between gap-4 border-b border-white/[0.04] px-5 py-4"
  >
    <div class="flex items-center gap-3">
      <span
        class="flex h-7 w-7 items-center justify-center rounded-lg ring-1 ring-inset"
        :class="iconClasses"
      >
        <slot name="icon" />
      </span>
      <div>
        <h2 class="text-sm font-semibold text-white">{{ title }}</h2>
        <p class="text-[11px] text-slate-500">{{ subtitle }}</p>
      </div>
    </div>
    <radar-badge :color="countColor" size="md">
      {{ count }}
    </radar-badge>
  </div>
</template>
