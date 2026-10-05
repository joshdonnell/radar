<script setup lang="ts" generic="TValue extends string">
defineProps<{
  options: { label: string; value: TValue; count?: number }[]
  label: string
}>()

const model = defineModel<TValue>({ required: true })
</script>

<template>
  <div
    class="inline-flex w-full flex-wrap gap-1 rounded-lg bg-surface-2/60 p-1 ring-1 ring-inset ring-border sm:w-auto"
    role="group"
    :aria-label="label"
  >
    <button
      v-for="option in options"
      :key="option.value"
      type="button"
      class="inline-flex h-7 cursor-pointer items-center gap-1.5 rounded-md px-2.5 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
      :class="
        model === option.value
          ? 'bg-fg/10 text-fg ring-1 ring-inset ring-border-strong'
          : 'text-muted hover:text-fg'
      "
      :aria-pressed="model === option.value"
      @click="model = option.value"
    >
      {{ option.label }}
      <span
        v-if="option.count !== undefined"
        class="tabular-nums text-dim"
        aria-hidden="true"
      >
        {{ option.count }}
      </span>
    </button>
  </div>
</template>
