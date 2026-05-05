<script setup lang="ts">
import Dashboard from './Dashboard.vue'
</script>

<template>
  <div
    id="radar"
    class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_30%),radial-gradient(circle_at_top_right,_rgba(45,212,191,0.12),_transparent_25%)]"
  >
    <aside
      class="fixed inset-y-0 left-0 hidden w-64 border-r border-white/10 bg-slate-950/80 backdrop-blur-xl lg:block"
    >
      <div class="flex h-16 items-center border-b border-white/10 px-6">
        <div class="flex items-center gap-3">
          <div
            class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/20"
          >
            R
          </div>
          <div>
            <p class="text-sm font-semibold tracking-wide text-white">
              Laravel Radar
            </p>
            <p class="text-xs text-slate-500">Dependency health</p>
          </div>
        </div>
      </div>

      <nav class="space-y-1 px-3 py-6 text-sm font-medium">
        <a
          href="#"
          class="flex items-center gap-3 rounded-xl bg-cyan-400/10 px-3 py-2.5 text-cyan-200 ring-1 ring-inset ring-cyan-400/20"
        >
          <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
          Overview
        </a>
        <span
          class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-500"
        >
          <span class="h-2 w-2 rounded-full bg-slate-700"></span>
          Vulnerabilities
        </span>
        <span
          class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-500"
        >
          <span class="h-2 w-2 rounded-full bg-slate-700"></span>
          Packages
        </span>
        <span
          class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-500"
        >
          <span class="h-2 w-2 rounded-full bg-slate-700"></span>
          Updates
        </span>
      </nav>
    </aside>

    <main class="lg:pl-64">
      <header class="border-b border-white/10 bg-slate-950/70 backdrop-blur-xl">
        <div
          class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8"
        >
          <div>
            <p
              class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300"
            >
              Radar
            </p>
            <h1
              class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl"
            >
              Dependency Health
            </h1>
          </div>

          <div
            class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-medium text-slate-300"
          >
            Read-only dashboard
          </div>
        </div>
      </header>

      <section class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
        <dashboard />
      </section>
    </main>
  </div>
</template>
