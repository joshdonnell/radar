<script setup lang="ts">
defineProps<{
  label: string
  colorClass: string
}>()
</script>

<template>
  <span
    class="shrink-0 rounded-md px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.05em] ring-1 ring-inset"
    :class="colorClass"
  >
    {{ label }}
  </span>
</template>
