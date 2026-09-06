export const useApi = () => {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('admin_token')
  const request = <T>(path: string, options: Parameters<typeof $fetch<T>>[1] = {}) => $fetch<T>(`${config.public.apiBase}${path}`, {
    ...options,
    headers: { Accept: 'application/json', Authorization: token.value ? `Bearer ${token.value}` : '', ...options.headers },
    onResponseError({ response }) { if (response.status === 401) { token.value = null; navigateTo('/login') } },
  })
  return { request, token }
}
