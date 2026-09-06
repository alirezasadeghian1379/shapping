export default defineNuxtConfig({
    compatibilityDate: '2025-05-15',
    devtools: {enabled: true},
    modules: [
        '@nuxtjs/tailwindcss',
    ],
    css: [
        '~/assets/css/main.css',
    ],
    app: {
        head: {
            htmlAttrs: {
                lang: 'fa',
                dir: 'rtl'
            },
            titleTemplate: '%s | پنل مدیریت'
        }
    },
    imports: {
        dirs: ['composables'],
    },
    runtimeConfig: {
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000/api/v1'
        }
    },
})
