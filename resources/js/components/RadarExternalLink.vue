<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClassList } from '~/utils/colors'
import type { RadarColor as RadarColorValue } from '~/utils/colors'

type LinkColor = Extract<
  RadarColorValue,
  typeof RadarColor.Neutral | typeof RadarColor.Warning
>

const props = withDefaults(
  defineProps<{
    href: string
    label: string
    color?: LinkColor
  }>(),
  {
    color: RadarColor.Neutral,
  },
)

const classes = computed(() => radarColorClassList(props.color))
</script>

<template>
  <a
    :href="href"
    target="_blank"
    rel="noreferrer"
    class="inline-flex cursor-pointer items-center gap-1.5 rounded-md px-2.5 py-1 text-[11px] font-medium ring-1 ring-inset transition-colors hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/30"
    :class="classes"
  >
    <svg
      class="h-3 w-3"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="1.8"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <path d="M15 3h6v6" />
      <path d="M10 14L21 3" />
      <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
    </svg>
    {{ label }}
  </a>
</template>
