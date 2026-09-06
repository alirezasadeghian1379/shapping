export default defineEventHandler((event) => {
  const config = useRuntimeConfig(event)
  const base = String(config.public.siteUrl).replace(/\/$/, '')
  setHeader(event, 'content-type', 'text/plain; charset=UTF-8')
  return `User-agent: *\nAllow: /\nDisallow: /profile\nDisallow: /checkout\nDisallow: /cart\nSitemap: ${base}/sitemap.xml\n`
})
