<script setup lang="ts">
defineProps<{
  title: string
  message: string
}>()
</script>

<template>
  <div
    class="rounded-2xl border border-dashed border-white/15 bg-white/[0.03] px-6 py-16 text-center shadow-2xl shadow-slate-950/40"
  >
    <div
      class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-2xl ring-1 ring-cyan-400/20"
    >
      R
    </div>
    <h2 class="mt-6 text-xl font-semibold text-white">{{ title }}</h2>
    <p
      class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-400"
      v-html="message"
    ></p>
  </div>
</template>
