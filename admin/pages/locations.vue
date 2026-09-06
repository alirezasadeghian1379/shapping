<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'مدیریت استان و شهر'});
const {request} = useApi();
const {data, refresh} = await useAsyncData('admin-locations', () => request<any[]>('/admin/locations'));
const selected = ref<number | null>(null), provinceName = ref(''), cityName = ref(''), error = ref('');
const current = computed(() => data.value?.find(x => x.id === selected.value));
watch(data, () => {
  if (!selected.value && data.value?.length) selected.value = data.value[0].id
}, {immediate: true});

async function addProvince() {
  if (!provinceName.value) return;
  try {
    await request('/admin/locations/provinces', {method: 'POST', body: {name: provinceName.value, is_active: true}});
    provinceName.value = '';
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'ثبت استان انجام نشد.'
  }
}

async function addCity() {
  if (!cityName.value || !selected.value) return;
  try {
    await request('/admin/locations/cities', {
      method: 'POST',
      body: {province_id: selected.value, name: cityName.value, is_active: true}
    });
    cityName.value = '';
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'ثبت شهر انجام نشد.'
  }
}

async function toggle(type: 'provinces' | 'cities', item: any) {
  await request(`/admin/locations/${type}/${item.id}`, {
    method: 'PUT',
    body: {name: item.name, slug: item.slug, sort_order: item.sort_order, is_active: !item.is_active}
  });
  await refresh()
}

async function remove(type: 'provinces' | 'cities', id: number) {
  if (!confirm('از حذف این مورد مطمئن هستید؟')) return;
  try {
    await request(`/admin/locations/${type}/${id}`, {method: 'DELETE'});
    if (type === 'provinces' && selected.value === id) selected.value = null;
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'این مورد قابل حذف نیست.'
  }
}
</script>
<template>
  <div class="space-y-6">
    <SectionIntro title="استان‌ها و شهرها" text="لیست مناطق قابل انتخاب در آدرس کاربران را مدیریت کنید."/>
    <p v-if="error" class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ error }}</p>
    <div class="grid gap-5 lg:grid-cols-[360px_1fr]">
      <section class="rounded-2xl border bg-white p-5"><h2 class="font-black">استان‌ها</h2>
        <form class="mt-4 flex gap-2" @submit.prevent="addProvince"><input v-model="provinceName"
                                                                           class="h-11 min-w-0 flex-1 rounded-xl border px-3"
                                                                           placeholder="نام استان">
          <button class="rounded-xl bg-brand-700 px-4 font-bold text-white">افزودن</button>
        </form>
        <div class="mt-4 max-h-[600px] space-y-2 overflow-auto">
          <button v-for="p in data" :key="p.id"
                  :class="['flex w-full items-center justify-between rounded-xl border p-3 text-right',selected===p.id?'border-brand-600 bg-brand-50':'']"
                  @click="selected=p.id"><span><b>{{ p.name }}</b><small
              class="mr-2 text-slate-400">{{ p.cities.length }} شهر</small></span><span class="flex gap-2"><i
              :class="p.is_active?'text-emerald-600':'text-slate-400'" class="not-italic"
              @click.stop="toggle('provinces',p)">{{ p.is_active ? 'فعال' : 'غیرفعال' }}</i><i
              class="not-italic text-rose-600" @click.stop="remove('provinces',p.id)">×</i></span></button>
        </div>
      </section>
      <section class="rounded-2xl border bg-white p-5"><h2 class="font-black">شهرهای
        {{ current?.name || 'استان انتخابی' }}</h2>
        <form class="mt-4 flex gap-2" @submit.prevent="addCity"><input v-model="cityName" :disabled="!selected"
                                                                       class="h-11 min-w-0 flex-1 rounded-xl border px-3"
                                                                       placeholder="نام شهر">
          <button :disabled="!selected" class="rounded-xl bg-brand-700 px-5 font-bold text-white disabled:opacity-40">
            افزودن شهر
          </button>
        </form>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
          <article v-for="city in current?.cities" :key="city.id"
                   class="flex items-center justify-between rounded-xl border p-4"><span
              class="font-bold">{{ city.name }}</span>
            <div class="flex gap-3 text-xs">
              <button :class="city.is_active?'text-emerald-600':'text-slate-400'" @click="toggle('cities',city)">
                {{ city.is_active ? 'فعال' : 'غیرفعال' }}
              </button>
              <button class="text-rose-600" @click="remove('cities',city.id)">حذف</button>
            </div>
          </article>
        </div>
      </section>
    </div>
  </div>
</template>
