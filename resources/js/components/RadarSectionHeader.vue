<script setup lang="ts">
import { computed } from 'vue'
import RadarBadge from '~/components/RadarBadge.vue'
import { RadarColor, radarColorClassList } from '~/utils/colors'
import type { RadarColor as RadarColorValue } from '~/utils/colors'

type SectionColor = Extract<
  RadarColorValue,
  | typeof RadarColor.Danger
  | typeof RadarColor.Warning
  | typeof RadarColor.Success
  | typeof RadarColor.Abandoned
  | typeof RadarColor.Neutral
>

const props = withDefaults(
  defineProps<{
    title: string
    subtitle: string
    count: number | string
    countColor?: SectionColor
  }>(),
  {
    countColor: RadarColor.Neutral,
  },
)

const iconClasses = computed(() => radarColorClassList(props.countColor))
</script>

<template>
  <div
    class="flex items-center justify-between gap-4 border-b border-border px-5 py-4"
  >
    <div class="flex items-center gap-3">
      <span
        class="flex h-9 w-9 items-center justify-center rounded-lg ring-1 ring-inset"
        :class="iconClasses"
      >
        <slot name="icon" />
      </span>
      <div>
        <h2 class="text-[15px] font-semibold text-fg">{{ title }}</h2>
        <p class="text-[12px] text-muted">{{ subtitle }}</p>
      </div>
    </div>
    <RadarBadge :color="countColor" size="md">
      {{ count }}
    </RadarBadge>
  </div>
</template>
