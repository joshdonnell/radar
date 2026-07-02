<script setup lang="ts">
defineProps<{
  href: string
  label: string
  isActive: boolean
}>()

defineEmits<{
  click: []
}>()
</script>

<template>
  <a
    :href="href"
    class="group flex cursor-pointer items-center gap-3 rounded-md border px-3 py-2.5 text-[13px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
    :class="
      isActive
        ? 'border-border-strong bg-surface text-fg'
        : 'border-transparent text-muted hover:bg-surface/60 hover:text-fg'
    "
    @click.prevent="$emit('click')"
  >
    <span class="flex h-[17px] w-[17px] shrink-0 items-center justify-center">
      <slot name="icon" />
    </span>
    <span class="flex-1 truncate">{{ label }}</span>
    <slot name="trailing" />
  </a>
</template>
