<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({layout: 'auth'})
useHead({title: 'ورود مدیر'})
const {request, token} = useApi()
const step = ref<'mobile' | 'code'>('mobile'), mobile = ref('09120000000'), code = ref('12345'), loading = ref(false),
    error = ref('')

async function sendCode() {
  loading.value = true;
  error.value = '';
  try {
    await request('/auth/otp/request', {method: 'POST', body: {mobile: mobile.value}});
    step.value = 'code'
  } catch (e: any) {
    error.value = e?.data?.message || 'ارسال کد انجام نشد.'
  } finally {
    loading.value = false
  }
}

async function login() {
  loading.value = true;
  error.value = '';
  try {
    const data = await request<{ token: string }>('/auth/otp/verify', {
      method: 'POST',
      body: {mobile: mobile.value, code: code.value, device_name: 'admin-web'}
    });
    token.value = data.token;
    await navigateTo('/')
  } catch (e: any) {
    error.value = e?.data?.message || 'کد ورود صحیح نیست.'
  } finally {
    loading.value = false
  }
}
</script>
<template>
  <div class="rounded-[2rem] bg-white p-7 shadow-2xl sm:p-10">
    <div class="mb-8">
      <div class="mb-5 grid size-14 place-items-center rounded-2xl bg-brand-700 text-2xl font-black text-white">هـ</div>
      <h1 class="text-2xl font-black text-slate-900">ورود به پنل مدیریت</h1>
      <p class="mt-2 text-sm leading-6 text-slate-500">برای مدیریت فروشگاه، شماره موبایل خود را وارد کنید.</p></div>
    <form class="space-y-5" @submit.prevent="step === 'mobile' ? sendCode() : login()"><label class="block"><span
        class="mb-2 block text-sm font-bold">شماره موبایل</span><input v-model="mobile" dir="ltr"
                                                                       :disabled="step === 'code'"
                                                                       class="h-12 w-full rounded-xl border border-slate-200 px-4 text-left outline-none transition focus:border-brand-600 focus:ring-4 focus:ring-brand-50"/></label><label
        v-if="step === 'code'" class="block"><span class="mb-2 block text-sm font-bold">کد پنج رقمی</span><input
        v-model="code" dir="ltr" maxlength="5" autofocus
        class="h-12 w-full rounded-xl border border-slate-200 px-4 text-center text-xl tracking-[.5em] outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-50"/></label>
      <p v-if="error" class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ error }}</p>
      <button :disabled="loading"
              class="h-12 w-full rounded-xl bg-brand-700 font-bold text-white shadow-lg shadow-brand-700/20 transition hover:bg-brand-900 disabled:opacity-50">
        {{ loading ? 'کمی صبر کنید…' : step === 'mobile' ? 'دریافت کد ورود' : 'ورود به فروشگاه' }}
      </button>
      <button v-if="step === 'code'" type="button" class="w-full text-sm text-slate-500" @click="step = 'mobile'">اصلاح
        شماره موبایل
      </button>
    </form>
    <p class="mt-7 text-center text-xs text-slate-400">ورود امن با رمز یک‌بارمصرف</p></div>
</template>
