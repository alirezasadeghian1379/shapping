<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'})
useHead({title: 'گزارش فعالیت مدیران'})
type AuditLog = {
  id: number;
  user_name: string | null;
  user_mobile: string | null;
  method: string;
  path: string;
  subject_type: string | null;
  subject_id: string | null;
  request_payload: any;
  response_status: number;
  ip: string | null;
  duration_ms: number;
  created_at: string
}
const {request} = useApi();
const filters = reactive({search: '', method: '', from: '', to: ''}), applied = ref({...filters}), page = ref(1),
    openId = ref<number | null>(null)
const query = computed(() => {
  const p = new URLSearchParams({page: String(page.value), per_page: '25'});
  Object.entries(applied.value).forEach(([k, v]) => v && p.set(k, v));
  return p.toString()
})
const {
  data,
  pending,
  refresh
} = await useAsyncData('admin-audit-logs', () => request<any>(`/admin/audit-logs?${query.value}`), {watch: [page]})

function applyFilters() {
  page.value = 1;
  applied.value = {...filters};
  refresh()
}

function payloadOf(log: AuditLog) {
  if (!log.request_payload) return 'بدون داده';
  try {
    return JSON.stringify(typeof log.request_payload === 'string' ? JSON.parse(log.request_payload) : log.request_payload, null, 2)
  } catch {
    return String(log.request_payload)
  }
}

const labels: Record<string, string> = {POST: 'ایجاد', PUT: 'جایگزینی', PATCH: 'ویرایش', DELETE: 'حذف'},
    colors: Record<string, string> = {
      POST: 'bg-emerald-50 text-emerald-700',
      PUT: 'bg-blue-50 text-blue-700',
      PATCH: 'bg-amber-50 text-amber-700',
      DELETE: 'bg-rose-50 text-rose-700'
    }
</script>
<template>
  <div class="space-y-6">
    <SectionIntro title="گزارش فعالیت مدیران"
                  text="تمام تغییرات حساس پنل برای پیگیری و افزایش امنیت فروشگاه ثبت می‌شوند."/>
    <form class="grid gap-3 rounded-2xl border bg-white p-4 lg:grid-cols-5" @submit.prevent="applyFilters"><input
        v-model="filters.search" class="h-11 rounded-xl border px-3 lg:col-span-2"
        placeholder="جست‌وجوی مدیر، مسیر یا IP"><select v-model="filters.method" class="h-11 rounded-xl border px-3">
      <option value="">همه عملیات‌ها</option>
      <option value="POST">ایجاد</option>
      <option value="PUT">جایگزینی</option>
      <option value="PATCH">ویرایش</option>
      <option value="DELETE">حذف</option>
    </select><input v-model="filters.from" type="date" class="h-11 rounded-xl border px-3">
      <div class="flex gap-2"><input v-model="filters.to" type="date" class="min-w-0 flex-1 rounded-xl border px-3">
        <button class="rounded-xl bg-brand-700 px-4 font-bold text-white">اعمال</button>
      </div>
    </form>
    <section class="overflow-hidden rounded-2xl border bg-white">
      <div v-if="pending" class="p-10 text-center text-slate-400">در حال دریافت گزارش…</div>
      <div v-else-if="!data?.data?.length" class="p-10 text-center text-slate-400">فعالیتی با این مشخصات ثبت نشده است.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-right text-sm">
          <thead class="bg-slate-50 text-xs text-slate-500">
          <tr>
            <th class="p-4">مدیر</th>
            <th class="p-4">عملیات</th>
            <th class="p-4">مسیر</th>
            <th class="p-4">وضعیت</th>
            <th class="p-4">زمان</th>
            <th class="p-4">جزئیات</th>
          </tr>
          </thead>
          <tbody class="divide-y">
          <template v-for="log in data.data as AuditLog[]" :key="log.id">
            <tr>
              <td class="p-4"><b>{{ log.user_name || 'مدیر' }}</b>
                <p class="text-xs text-slate-400">{{ log.user_mobile || log.ip }}</p></td>
              <td class="p-4"><span
                  :class="['rounded-lg px-2.5 py-1 text-xs font-bold',colors[log.method]]">{{ labels[log.method] }}</span>
              </td>
              <td class="p-4 font-mono text-xs" dir="ltr">{{ log.path }}</td>
              <td class="p-4" :class="log.response_status<400?'text-emerald-700':'text-rose-700'">
                {{ log.response_status }} <small class="text-slate-400">{{ log.duration_ms }}ms</small></td>
              <td class="p-4 text-xs">{{ new Date(log.created_at).toLocaleString('fa-IR') }}</td>
              <td class="p-4">
                <button class="rounded-lg border px-3 py-1" @click="openId=openId===log.id?null:log.id">
                  {{ openId === log.id ? 'بستن' : 'مشاهده' }}
                </button>
              </td>
            </tr>
            <tr v-if="openId===log.id">
              <td colspan="6" class="bg-slate-950 p-4 text-slate-200"><p class="mb-2 text-xs">IP: {{ log.ip || '—' }}
                <span v-if="log.subject_type"> | منبع: {{ log.subject_type }} #{{ log.subject_id }}</span></p>
                <pre class="max-h-72 overflow-auto whitespace-pre-wrap text-left text-xs"
                     dir="ltr">{{ payloadOf(log) }}</pre>
              </td>
            </tr>
          </template>
          </tbody>
        </table>
      </div>
      <footer v-if="data?.last_page>1" class="flex justify-between border-t p-4">
        <button :disabled="page<=1" @click="page--">قبلی</button>
        <span>صفحه {{ data.current_page }} از {{ data.last_page }}</span>
        <button :disabled="page>=data.last_page" @click="page++">بعدی</button>
      </footer>
    </section>
  </div>
</template>
