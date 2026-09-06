export default defineNuxtRouteMiddleware(to=>{if(!useCookie('shop_token').value)return navigateTo('/login?redirect='+encodeURIComponent(to.fullPath))})
