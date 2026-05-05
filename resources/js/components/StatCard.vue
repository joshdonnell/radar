<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    label: string
    value?: number | null
    suffix?: string | null
    color?: 'white' | 'rose' | 'emerald'
  }>(),
  {
    value: null,
    suffix: null,
    color: 'white',
  },
)

const colorClass = computed(() => {
  const map: Record<string, string> = {
    rose: 'text-rose-300',
    emerald: 'text-emerald-300',
    white: 'text-white',
  }
  return map[props.color] ?? map.white
})

const displayValue = computed(() => {
  return props.value !== null ? props.value : 'N/A'
})
</script>

<template>
  <div class="bg-slate-900/95 p-6">
    <dt class="text-sm font-medium text-slate-400">{{ label }}</dt>
    <dd class="mt-3 flex items-baseline gap-2">
      <span class="text-4xl font-semibold tracking-tight" :class="colorClass">{{
        displayValue
      }}</span>
      <span v-if="suffix && value !== null" class="text-sm text-slate-500">{{
        suffix
      }}</span>
    </dd>
  </div>
</template>
