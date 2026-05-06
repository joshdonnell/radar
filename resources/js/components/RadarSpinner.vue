<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: 'sm' | 'md'
  }>(),
  {
    size: 'md',
  },
)
</script>

<template>
  <div class="relative" :class="size === 'sm' ? 'h-6 w-6' : 'h-8 w-8'">
    <div class="absolute inset-0 rounded-full border-2 border-cyan-400/10" />
    <div
      class="absolute inset-0 rounded-full border-2 border-t-cyan-400 animate-spin"
    />
  </div>
</template>
