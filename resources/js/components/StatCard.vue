<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClasses } from '~/utils/colors'

const props = withDefaults(
  defineProps<{
    label: string
    value?: number | null
    suffix?: string | null
    color?: RadarColor
    size?: 'md' | 'lg'
  }>(),
  {
    value: null,
    suffix: null,
    color: RadarColor.Fg,
    size: 'md',
  },
)

const valueColor = computed(() => radarColorClasses(props.color).text)

const displayValue = computed(() => (props.value !== null ? props.value : '—'))
</script>

<template>
  <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
    <div
      class="mb-3.5 flex items-center gap-2.5 text-[12px] font-semibold uppercase tracking-[0.08em] text-muted"
    >
      <span class="flex h-4 w-4 items-center justify-center text-muted">
        <slot name="icon" />
      </span>
      {{ label }}
    </div>

    <div class="flex items-baseline gap-2">
      <span
        class="font-bold leading-none tracking-tight tabular-nums"
        :class="[valueColor, size === 'lg' ? 'text-[52px]' : 'text-[40px]']"
      >
        {{ displayValue }}
      </span>
      <span
        v-if="suffix && value !== null"
        class="text-lg font-medium text-dim"
      >
        {{ suffix }}
      </span>
    </div>

    <slot name="breakdown" />
  </div>
</template>
