<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClassList } from '../utils/colors'
import type { RadarColor as RadarColorValue } from '../utils/colors'

type LinkColor = Extract<
  RadarColorValue,
  typeof RadarColor.Cyan | typeof RadarColor.Amber
>

const props = withDefaults(
  defineProps<{
    href: string
    label: string
    color?: LinkColor
  }>(),
  {
    color: RadarColor.Cyan,
  },
)

const classes = computed(() => radarColorClassList(props.color))
</script>

<template>
  <a
    :href="href"
    target="_blank"
    rel="noreferrer"
    class="inline-flex cursor-pointer items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset transition-colors hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
    :class="classes"
  >
    <svg
      class="h-2.5 w-2.5"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2.5"
    >
      <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
      <polyline points="15 3 21 3 21 9" />
      <line x1="10" y1="14" x2="21" y2="3" />
    </svg>
    {{ label }}
  </a>
</template>
