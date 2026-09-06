<script setup lang="ts">const{ids}=useCompare()</script>
<template><Transition><NuxtLink v-if="ids.length" to="/compare" class="fixed bottom-5 left-5 z-50 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-2xl">مقایسه {{ids.length}} محصول ←</NuxtLink></Transition></template>
