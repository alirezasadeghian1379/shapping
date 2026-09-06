<script setup lang="ts">
import {useApi} from "../../composables/useApi";

definePageMeta({middleware: 'auth'});
const route = useRoute(), {request} = useApi(), resource = String(route.params.resource);
if (!['sliders', 'banners', 'faqs'].includes(resource)) throw createError({statusCode: 404});
const config: any = {
  sliders: {title: 'مدیریت اسلایدرها', directory: 'sliders'},
  banners: {title: 'مدیریت بنرها', directory: 'banners'},
  faqs: {title: 'سؤالات متداول', directory: null}
}[resource];
useHead({title: config.title});
const {data, refresh} = await useAsyncData(`content-${resource}`, () => request<any[]>(`/admin/content/${resource}`));
const empty = () => resource === 'faqs' ? {
  question: '',
  answer: '',
  group: 'عمومی',
  sort_order: 0,
  is_active: true
} : resource === 'sliders' ? {
  title: '',
  image: '',
  mobile_image: '',
  link: '/products',
  position: 'home',
  sort_order: 0,
  is_active: true,
  starts_at: '',
  ends_at: ''
} : {title: '', image: '', link: '/products', placement: 'home-middle', sort_order: 0, is_active: true};
const form = reactive<any>(empty()), editing = ref<number | null>(null), saving = ref(false), error = ref('');

function edit(item: any) {
  editing.value = item.id;
  Object.assign(form, empty(), item);
  window.scrollTo({top: 0, behavior: 'smooth'})
}

function reset() {
  editing.value = null;
  Object.assign(form, empty())
}

async function upload(e: Event, field = 'image') {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('file', file);
  fd.append('directory', config.directory);
  const r = await request<any>('/admin/media', {method: 'POST', body: fd});
  form[field] = r.path
}

async function save() {
  saving.value = true;
  error.value = '';
  try {
    await request(`/admin/content/${resource}${editing.value ? '/' + editing.value : ''}`, {
      method: editing.value ? 'PUT' : 'POST',
      body: form
    });
    reset();
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || 'ذخیره انجام نشد.'
  } finally {
    saving.value = false
  }
}

async function remove(id: number) {
  if (!confirm('این محتوا حذف شود؟')) return;
  await request(`/admin/content/${resource}/${id}`, {method: 'DELETE'});
  await refresh()
}

const media = (path: string) => path ? `http://127.0.0.1:8000/storage/${path}` : ''
</script>
<template>
  <div class="space-y-6">
    <SectionIntro :title="config.title" text="محتوای این بخش را بدون تغییر کد، مرتب و منتشر کنید."/>
    <form class="rounded-2xl border bg-white p-5" @submit.prevent="save"><p v-if="error"
                                                                            class="mb-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-700">
      {{ error }}</p>
      <div v-if="resource==='faqs'" class="grid gap-4 md:grid-cols-2"><input v-model="form.question" required
                                                                             class="h-11 rounded-xl border px-3 md:col-span-2"
                                                                             placeholder="متن سؤال"><textarea
          v-model="form.answer" required rows="4" class="rounded-xl border p-3 md:col-span-2"
          placeholder="پاسخ کامل"/><input v-model="form.group" class="h-11 rounded-xl border px-3"
                                          placeholder="گروه"><input v-model.number="form.sort_order" type="number"
                                                                    min="0" class="h-11 rounded-xl border px-3"
                                                                    placeholder="ترتیب"></div>
      <div v-else class="grid gap-4 md:grid-cols-2"><input v-model="form.title" required
                                                           class="h-11 rounded-xl border px-3"
                                                           placeholder="عنوان"><input v-model="form.link" dir="ltr"
                                                                                      class="h-11 rounded-xl border px-3 text-left"
                                                                                      placeholder="/products"><label
          class="rounded-xl border border-dashed p-4 text-sm"><span
          class="mb-2 block font-bold">تصویر دسکتاپ</span><input type="file" accept="image/*"
                                                                 @change="upload($event,'image')"><img v-if="form.image"
                                                                                                       :src="media(form.image)"
                                                                                                       class="mt-3 h-24 w-full rounded-lg object-cover"></label><label
          v-if="resource==='sliders'" class="rounded-xl border border-dashed p-4 text-sm"><span
          class="mb-2 block font-bold">تصویر موبایل</span><input type="file" accept="image/*"
                                                                 @change="upload($event,'mobile_image')"><img
          v-if="form.mobile_image" :src="media(form.mobile_image)"
          class="mt-3 h-24 w-full rounded-lg object-cover"></label><input v-if="resource==='sliders'"
                                                                          v-model="form.position"
                                                                          class="h-11 rounded-xl border px-3"
                                                                          placeholder="جایگاه"><input v-else
                                                                                                      v-model="form.placement"
                                                                                                      required
                                                                                                      class="h-11 rounded-xl border px-3"
                                                                                                      placeholder="جایگاه بنر"><input
          v-model.number="form.sort_order" type="number" min="0" class="h-11 rounded-xl border px-3"
          placeholder="ترتیب"><input v-if="resource==='sliders'" v-model="form.starts_at" type="datetime-local"
                                     class="h-11 rounded-xl border px-3"><input v-if="resource==='sliders'"
                                                                                v-model="form.ends_at"
                                                                                type="datetime-local"
                                                                                class="h-11 rounded-xl border px-3">
      </div>
      <div class="mt-5 flex items-center justify-between"><label class="flex gap-2 text-sm font-bold"><input
          v-model="form.is_active" type="checkbox" class="accent-brand-700">فعال و قابل نمایش</label>
        <div class="flex gap-2">
          <button v-if="editing" type="button" class="rounded-xl border px-5 py-2" @click="reset">انصراف</button>
          <button :disabled="saving" class="rounded-xl bg-brand-700 px-6 py-2 font-bold text-white">
            {{ saving ? 'در حال ذخیره…' : editing ? 'ویرایش' : 'افزودن' }}
          </button>
        </div>
      </div>
    </form>
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article v-for="item in data" :key="item.id" class="overflow-hidden rounded-2xl border bg-white"><img
          v-if="item.image" :src="media(item.image)" class="h-40 w-full object-cover">
        <div class="p-5"><b>{{ item.title || item.question }}</b>
          <p v-if="item.answer" class="mt-2 line-clamp-3 text-sm leading-7 text-slate-500">{{ item.answer }}</p>
          <div class="mt-4 flex justify-between text-xs"><span
              :class="item.is_active?'text-emerald-600':'text-slate-400'">{{ item.is_active ? 'فعال' : 'غیرفعال' }}</span><span
              class="flex gap-3"><button class="text-brand-700" @click="edit(item)">ویرایش</button><button
              class="text-rose-600" @click="remove(item.id)">حذف</button></span></div>
        </div>
      </article>
    </section>
  </div>
</template>
