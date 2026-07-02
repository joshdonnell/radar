<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary'
    size?: 'sm' | 'md'
    loading?: boolean
    disabled?: boolean
    ariaLabel?: string
  }>(),
  {
    variant: 'secondary',
    size: 'md',
    loading: false,
    disabled: false,
    ariaLabel: undefined,
  },
)

defineEmits<{
  click: []
}>()

const variantClasses: Record<string, string> = {
  primary: 'border-fg bg-fg text-bg hover:bg-fg/90',
  secondary:
    'border-border-strong bg-surface-2 text-fg hover:border-dim hover:bg-surface-2/70',
}

const sizeClasses: Record<string, string> = {
  sm: 'gap-1.5 rounded-lg px-3 py-1.5 text-[12px]',
  md: 'gap-2 rounded-lg px-4 py-2.5 text-[13px]',
}
</script>

<template>
  <button
    class="inline-flex cursor-pointer items-center border font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/30 focus-visible:ring-offset-2 focus-visible:ring-offset-bg"
    :class="[variantClasses[variant], sizeClasses[size]]"
    :disabled="disabled || loading"
    :aria-label="ariaLabel"
    @click="$emit('click')"
  >
    <svg
      v-if="loading"
      class="animate-spin"
      :class="size === 'sm' ? 'h-3.5 w-3.5' : 'h-4 w-4'"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2.5"
    >
      <path d="M12 2v4m0 12v4M2 12h4m12 0h4" stroke-linecap="round" />
    </svg>
    <slot v-else name="icon" />
    <slot />
  </button>
</template>
