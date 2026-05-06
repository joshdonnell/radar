<script setup lang="ts">
import { computed } from 'vue'
import { RadarColor, radarColorClasses } from '../utils/colors'

const props = withDefaults(
  defineProps<{
    color?: RadarColor
    size?: 'sm' | 'md'
  }>(),
  {
    color: RadarColor.White,
    size: 'sm',
  },
)

const classes = computed(() => radarColorClasses(props.color))
</script>

<template>
  <span
    class="inline-flex shrink-0 items-center gap-1.5 rounded-full font-semibold ring-1 ring-inset"
    :class="[
      classes.bg,
      classes.text,
      classes.ring,
      size === 'sm'
        ? 'px-2 py-0.5 text-[10px] uppercase tracking-wider'
        : 'px-2.5 py-0.5 text-[11px]',
    ]"
  >
    <slot name="dot" />
    <slot />
  </span>
</template>
