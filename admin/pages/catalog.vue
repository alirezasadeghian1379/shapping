<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'ساختار کاتالوگ'});
const {request} = useApi();
const tab = ref<'categories' | 'brands' | 'attributes'>('categories'), error = ref('');
const {
  data: categories,
  refresh: refreshCategories
} = await useAsyncData('catalog-categories', () => request<any[]>('/admin/categories'));
const {
  data: brands,
  refresh: refreshBrands
} = await useAsyncData('catalog-brands', () => request<any>('/admin/brands'));
const {
  data: attributes,
  refresh: refreshAttributes
} = await useAsyncData('catalog-attributes', () => request<any[]>('/admin/attributes'));
const category = reactive({title: '', slug: '', parent_id: null as number | null, is_active: true, sort_order: 0}),
    brand = reactive({title: '', slug: '', description: '', is_active: true}),
    attribute = reactive({title: '', slug: '', type: 'select', is_filterable: true, is_variant: true, unit: ''}),
    valueForms = reactive<Record<number, { label: string, value: string, color: string }>>({});

async function run(action: () => Promise<any>) {
  error.value = '';
  try {
    await action()
  } catch (e: any) {
    error.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || 'عملیات انجام نشد.'
  }
}

async function addCategory() {
  await run(async () => {
    await request('/admin/categories', {method: 'POST', body: category});
    Object.assign(category, {title: '', slug: '', parent_id: null, is_active: true, sort_order: 0});
    refreshCategories()
  })
}

async function addBrand() {
  await run(async () => {
    await request('/admin/brands', {method: 'POST', body: brand});
    Object.assign(brand, {title: '', slug: '', description: '', is_active: true});
    refreshBrands()
  })
}

async function addAttribute() {
  await run(async () => {
    await request('/admin/attributes', {method: 'POST', body: attribute});
    Object.assign(attribute, {title: '', slug: '', type: 'select', is_filterable: true, is_variant: true, unit: ''});
    refreshAttributes()
  })
}

async function addValue(a: any) {
  const f = valueForms[a.id] || {label: '', value: '', color: ''};
  if (!f.label || !f.value) return;
  await run(async () => {
    await request(`/admin/attributes/${a.id}/values`, {method: 'POST', body: f});
    valueForms[a.id] = {label: '', value: '', color: ''};
    refreshAttributes()
  })
}

async function remove(path: string, refresh: () => any) {
  if (confirm('این مورد حذف شود؟')) await run(async () => {
    await request(path, {method: 'DELETE'});
    refresh()
  })
}
</script>
<template>
  <div class="space-y-6">
    <SectionIntro title="ساختار کاتالوگ"
                  text="دسته‌بندی‌ها، برندها و ویژگی‌های قابل استفاده برای فیلتر و تنوع محصول را مدیریت کنید."/>
    <div class="flex gap-2 overflow-auto rounded-2xl border bg-white p-2">
      <button v-for="item in [['categories','دسته‌بندی‌ها'],['brands','برندها'],['attributes','ویژگی‌ها و مقادیر']]"
              :key="item[0]"
              :class="['shrink-0 rounded-xl px-5 py-2.5 text-sm font-bold',tab===item[0]?'bg-brand-700 text-white':'']"
              @click="tab=item[0] as any">{{ item[1] }}
      </button>
    </div>
    <p v-if="error" class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ error }}</p>
    <div v-if="tab==='categories'" class="grid gap-5 lg:grid-cols-[380px_1fr]">
      <form class="h-fit space-y-3 rounded-2xl border bg-white p-5" @submit.prevent="addCategory"><h2
          class="font-black">دسته‌بندی جدید</h2><input v-model="category.title" required
                                                       class="h-11 w-full rounded-xl border px-3"
                                                       placeholder="عنوان"><input v-model="category.slug" required
                                                                                  dir="ltr"
                                                                                  class="h-11 w-full rounded-xl border px-3 text-left"
                                                                                  placeholder="slug"><select
          v-model="category.parent_id" class="h-11 w-full rounded-xl border bg-white px-3">
        <option :value="null">دسته اصلی</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
      </select><input v-model.number="category.sort_order" type="number" min="0"
                      class="h-11 w-full rounded-xl border px-3" placeholder="ترتیب">
        <button class="h-11 w-full rounded-xl bg-brand-700 font-bold text-white">ثبت دسته</button>
      </form>
      <section class="grid gap-3 sm:grid-cols-2">
        <article v-for="c in categories" :key="c.id" class="rounded-2xl border bg-white p-5">
          <div class="flex justify-between"><b>{{ c.title }}</b>
            <button class="text-rose-600" @click="remove(`/admin/categories/${c.id}`,refreshCategories)">حذف</button>
          </div>
          <p dir="ltr" class="mt-2 text-left text-xs text-slate-400">{{ c.slug }}</p>
          <p class="mt-3 text-xs text-slate-500">{{ c.children?.length || 0 }} زیردسته</p></article>
      </section>
    </div>
    <div v-if="tab==='brands'" class="grid gap-5 lg:grid-cols-[380px_1fr]">
      <form class="h-fit space-y-3 rounded-2xl border bg-white p-5" @submit.prevent="addBrand"><h2 class="font-black">
        برند جدید</h2><input v-model="brand.title" required class="h-11 w-full rounded-xl border px-3"
                             placeholder="عنوان برند"><input v-model="brand.slug" required dir="ltr"
                                                             class="h-11 w-full rounded-xl border px-3 text-left"
                                                             placeholder="slug"><textarea v-model="brand.description"
                                                                                          class="w-full rounded-xl border p-3"
                                                                                          placeholder="توضیحات"/>
        <button class="h-11 w-full rounded-xl bg-brand-700 font-bold text-white">ثبت برند</button>
      </form>
      <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        <article v-for="b in brands?.data" :key="b.id" class="rounded-2xl border bg-white p-5">
          <div class="flex justify-between"><b>{{ b.title }}</b>
            <button class="text-rose-600" @click="remove(`/admin/brands/${b.id}`,refreshBrands)">حذف</button>
          </div>
          <p class="mt-2 text-xs text-slate-400">{{ b.slug }}</p>
          <p class="mt-3 line-clamp-2 text-sm text-slate-500">{{ b.description }}</p></article>
      </section>
    </div>
    <div v-if="tab==='attributes'" class="grid gap-5 lg:grid-cols-[380px_1fr]">
      <form class="h-fit space-y-3 rounded-2xl border bg-white p-5" @submit.prevent="addAttribute"><h2
          class="font-black">ویژگی جدید</h2><input v-model="attribute.title" required
                                                   class="h-11 w-full rounded-xl border px-3"
                                                   placeholder="مثلاً رنگ"><input v-model="attribute.slug" required
                                                                                  dir="ltr"
                                                                                  class="h-11 w-full rounded-xl border px-3 text-left"
                                                                                  placeholder="color"><select
          v-model="attribute.type" class="h-11 w-full rounded-xl border bg-white px-3">
        <option value="select">انتخابی</option>
        <option value="color">رنگ</option>
        <option value="text">متنی</option>
        <option value="number">عددی</option>
      </select><input v-model="attribute.unit" class="h-11 w-full rounded-xl border px-3"
                      placeholder="واحد، مثل سانتی‌متر"><label class="flex gap-2 text-sm"><input
          v-model="attribute.is_variant" type="checkbox">برای ساخت تنوع</label><label class="flex gap-2 text-sm"><input
          v-model="attribute.is_filterable" type="checkbox">قابل فیلتر</label>
        <button class="h-11 w-full rounded-xl bg-brand-700 font-bold text-white">ثبت ویژگی</button>
      </form>
      <section class="space-y-4">
        <article v-for="a in attributes" :key="a.id" class="rounded-2xl border bg-white p-5">
          <div class="flex justify-between">
            <div><b>{{ a.title }}</b><span class="mr-2 text-xs text-slate-400">{{ a.slug }}</span></div>
            <button class="text-xs text-rose-600" @click="remove(`/admin/attributes/${a.id}`,refreshAttributes)">حذف
              ویژگی
            </button>
          </div>
          <div class="mt-4 flex flex-wrap gap-2"><span v-for="v in a.values" :key="v.id"
                                                       class="flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs"><i
              v-if="v.color" class="size-3 rounded-full" :style="{background:v.color}"/>{{ v.label }}<button
              class="text-rose-600"
              @click="remove(`/admin/attribute-values/${v.id}`,refreshAttributes)">×</button></span></div>
          <form class="mt-4 grid gap-2 sm:grid-cols-[1fr_1fr_100px_auto]" @submit.prevent="addValue(a)"><input
              v-model="(valueForms[a.id]||(valueForms[a.id]={label:'',value:'',color:''})).label"
              class="h-9 rounded-lg border px-2" placeholder="عنوان"><input v-model="valueForms[a.id].value" dir="ltr"
                                                                            class="h-9 rounded-lg border px-2 text-left"
                                                                            placeholder="value"><input
              v-model="valueForms[a.id].color" dir="ltr" class="h-9 rounded-lg border px-2 text-left"
              placeholder="#ffffff">
            <button class="rounded-lg border px-3 text-xs font-bold">افزودن مقدار</button>
          </form>
        </article>
      </section>
    </div>
  </div>
</template>
