<script setup lang="ts">
const route = useRoute()
const props = defineProps<{
  mobileOpen: boolean
}>()
import {
  IconsLog,
  IconsDashboard,
  IconsLocation,
  IconsCatalog,
  IconsProduct,
  IconsOrder,
  IconsRequestBack,
  IconsUsers,
  IconsDiscount,
  IconsCampaign,
  IconsNotification,
  IconsCount,
  IconsReport,
  IconsChat,
  IconsComment,
  IconsContent,
  IconsFaq,
  IconsSearchSetting,
  IconsShipping,
  IconsSetting,
} from "#components";

const links = [
  {link: '/', title: 'نمای کلی', icon: IconsDashboard},
  {link: '/users', title: 'کاربران و مدیران', icon: IconsUsers},
  {link: '/catalog', title: 'دسته، برند و ویژگی‌ها', icon: IconsCatalog},
  {link: '/products', title: 'محصولات', icon: IconsProduct},
  {link: '/orders', title: 'سفارش‌ها', icon: IconsOrder},
  {link: '/reports', title: 'گزارش فروش', icon: IconsReport},
  {link: '/content', title: 'محتوا و نمایش', icon: IconsContent},
  {link: '/support', title: 'مرکز پشتیبانی', icon: IconsChat},
  {link: '/comments', title: 'نظرات کاربران', icon: IconsComment},
  {link: '/locations', title: 'استان‌ها و شهرها', icon: IconsLocation},
  {link: '/campaigns', title: 'کمپین‌های فروش', icon: IconsCampaign},
  {link: '/discounts', title: 'کدهای تخفیف', icon: IconsDiscount},
  {link: '/returns', title: 'درخواست‌های مرجوعی', icon: IconsRequestBack},
  {link: '/notifications', title: 'اعلان‌های عمومی', icon: IconsNotification},
  {link: '/inventory', title: 'مدیریت موجودی', icon: IconsCount},
  {link: '/audit-logs', title: 'گزارش فعالیت‌ها', icon: IconsLog},
  {link: '/questions', title: 'پرسش‌های محصولات', icon: IconsFaq},
  {link: '/discovery', title: 'تحلیل جستجو', icon: IconsSearchSetting},
  {link: '/shipping', title: 'ارسال و بازه‌ها', icon: IconsShipping},
  {link: '/settings', title: 'تنظیمات', icon: IconsSetting},
]
</script>

<template>
  <aside :class="['fixed inset-y-0 right-0 z-40 flex h-dvh w-72 flex-col border-l border-slate-200 bg-white p-5 transition-transform lg:translate-x-0', mobileOpen ? 'translate-x-0' : 'translate-x-full']">
    <div class="mb-8 flex shrink-0 items-center gap-3 px-2">
      <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-brand-700 text-xl font-black text-white shadow-lg shadow-brand-700/20">هـ</div>
      <div>
        <p class="font-black text-slate-900">هنرمارکت</p>
        <p class="text-xs text-slate-400">مرکز مدیریت فروشگاه</p>
      </div>
    </div>
    <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
      <div class="space-y-1.5">
        <NuxtLink v-for="(item,index) in links" :key="index" :to="item?.link" :class="['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition', route.path === item?.link ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900']">
          <component v-if="typeof item.icon !== 'string'" :is="item?.icon" class="size-5"/>
          <span class="place-items-center text-[14px]">{{ item?.title }}</span>
        </NuxtLink>
      </div>
    </nav>
  </aside>



</template>

<style scoped>

</style>