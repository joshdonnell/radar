<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClasses } from '../utils/colors'

const props = withDefaults(
  defineProps<{
    label: string
    value?: number | null
    suffix?: string | null
    color?: RadarColor
  }>(),
  {
    value: null,
    suffix: null,
    color: RadarColor.White,
  },
)

const colorClasses = computed(() => radarColorClasses(props.color))

const displayValue = computed(() => {
  return props.value !== null ? props.value : '-'
})
</script>

<template>
  <div class="group relative p-5 transition-colors hover:bg-white/[0.01]">
    <dt
      class="flex items-center gap-2 text-[11px] font-medium uppercase tracking-wider text-slate-500"
    >
      <span
        class="inline-flex h-4 w-4 items-center justify-center rounded text-[9px] font-bold ring-1 ring-inset transition-colors"
        :class="[colorClasses.bg, colorClasses.text, colorClasses.ring]"
      >
        <slot name="icon" />
      </span>
      {{ label }}
    </dt>
    <dd class="mt-3 flex items-baseline gap-1.5">
      <span
        class="text-3xl font-semibold tracking-tight tabular-nums"
        :class="colorClasses.text"
      >
        {{ displayValue }}
      </span>
      <span
        v-if="suffix && value !== null"
        class="text-xs font-medium text-slate-600"
      >
        {{ suffix }}
      </span>
    </dd>
  </div>
</template>
