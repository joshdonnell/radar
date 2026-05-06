<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarFocusRingClass } from '../utils/colors'
import type { RadarColor as RadarColorValue } from '../utils/colors'

type NavColor = Extract<
  RadarColorValue,
  typeof RadarColor.Cyan | typeof RadarColor.Rose | typeof RadarColor.Amber
>

const props = withDefaults(
  defineProps<{
    href: string
    label: string
    isActive: boolean
    color?: NavColor
  }>(),
  {
    color: RadarColor.Cyan,
  },
)

defineEmits<{
  click: []
}>()

const activeClassMap = {
  [RadarColor.Cyan]: 'bg-cyan-400/[0.06] text-cyan-300',
  [RadarColor.Rose]: 'bg-rose-400/[0.06] text-rose-300',
  [RadarColor.Amber]: 'bg-amber-400/[0.06] text-amber-300',
} satisfies Record<NavColor, string>

const iconClassMap = {
  [RadarColor.Cyan]: 'bg-cyan-400/10 text-cyan-400',
  [RadarColor.Rose]: 'bg-rose-400/10 text-rose-400',
  [RadarColor.Amber]: 'bg-amber-400/10 text-amber-400',
} satisfies Record<NavColor, string>

const activeClasses = computed(() => activeClassMap[props.color])
const iconClasses = computed(() => iconClassMap[props.color])
const focusRingClass = computed(() => radarFocusRingClass(props.color))
</script>

<template>
  <a
    :href="href"
    class="group flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-[13px] font-medium transition-all duration-200 focus-visible:outline-none focus-visible:ring-2"
    :class="[
      isActive
        ? activeClasses
        : 'text-slate-400 hover:bg-white/[0.03] hover:text-slate-200',
      focusRingClass,
    ]"
    @click.prevent="$emit('click')"
  >
    <span
      class="flex h-5 w-5 items-center justify-center rounded-md transition-colors"
      :class="
        isActive
          ? iconClasses
          : 'bg-white/[0.04] text-slate-500 group-hover:bg-white/[0.06] group-hover:text-slate-400'
      "
    >
      <slot name="icon" />
    </span>
    {{ label }}
  </a>
</template>
