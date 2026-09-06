type RecentProduct = { id: number; title: string; slug: string; variants?: any[]; media?: any[] }

export const useRecentlyViewed = () => {
  const items = useState<RecentProduct[]>('recent-products', () => [])
  const hydrate = () => { if (import.meta.client) items.value = JSON.parse(localStorage.getItem('recent-products') || '[]') }
  const addRecent = (product: RecentProduct) => {
    if (!import.meta.client) return
    items.value = [product, ...items.value.filter(item => item.id !== product.id)].slice(0, 10)
    localStorage.setItem('recent-products', JSON.stringify(items.value))
  }
  return { items, hydrate, addRecent }
}
