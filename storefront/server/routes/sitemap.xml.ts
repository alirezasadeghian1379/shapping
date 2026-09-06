export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig(event)
  const data = await $fetch<any>(`${config.public.apiBase}/seo/sitemap`)
  const base = String(config.public.siteUrl).replace(/\/$/, '')
  const rows = [
    { loc: '/', updated_at: new Date().toISOString() },
    { loc: '/products', updated_at: new Date().toISOString() },
    { loc: '/blog', updated_at: new Date().toISOString() },
    ...data.products.map((item: any) => ({ loc: `/products/${item.slug}`, updated_at: item.updated_at })),
    ...data.categories.map((item: any) => ({ loc: `/products?category=${item.slug}`, updated_at: item.updated_at })),
    ...data.articles.map((item: any) => ({ loc: `/blog/${item.slug}`, updated_at: item.updated_at })),
  ]
  setHeader(event, 'content-type', 'application/xml; charset=UTF-8')
  return `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${rows.map(row => `<url><loc>${base}${row.loc.replace('&', '&amp;')}</loc><lastmod>${new Date(row.updated_at).toISOString()}</lastmod></url>`).join('')}</urlset>`
})
