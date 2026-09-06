<script setup>
const emit = defineEmits(['mobileOpen'])
const title = 'پنل مدیریت'
</script>

<template>
  <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur lg:px-8">
    <div class="flex items-center gap-3">
      <button class="rounded-xl border p-2 lg:hidden" @click="emit('mobileOpen')">☰</button>
      <div><h1 class="font-black text-slate-900">{{ title }}</h1>
        <p class="hidden text-xs text-slate-400 sm:block">امروز، یک روز خوب برای رشد فروشگاه است.</p></div>
    </div>
    <div class="flex items-center gap-3">
      <div class="grid size-10 place-items-center rounded-xl bg-amber-100 font-black text-amber-700">م</div>
    </div>
  </header>
</template>

<style scoped>

</style>