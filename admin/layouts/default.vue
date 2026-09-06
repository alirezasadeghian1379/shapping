<script setup lang="ts">
import SideBar from "../components/SideBar.vue";
import Header from "../components/Header.vue";
const mobileOpen = ref(false)


</script>

<template>

  <div class="min-h-screen bg-slate-50 text-slate-800">
    <SideBar :mobileOpen="mobileOpen"/>
    <div class="lg:pr-72">
      <Header @mobileOpen="mobileOpen = !mobileOpen"/>
      <main class="mx-auto max-w-[1600px] p-4 lg:p-8">
        <slot/>
      </main>
    </div>
    <button v-if="mobileOpen" class="fixed inset-0 z-30 bg-slate-950/30 lg:hidden" @click="mobileOpen = false"/>
  </div>
</template>
