<script setup lang="ts">defineProps<{ title: string, text: string, action?: string }>();
defineEmits(['action'])</script>
<template>
  <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div><h2 class="text-xl font-black">{{ title }}</h2>
      <p class="mt-1 text-sm text-slate-400">{{ text }}</p></div>
    <button v-if="action" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white"
            @click="$emit('action')">＋ {{ action }}
    </button>
  </div>
</template>
