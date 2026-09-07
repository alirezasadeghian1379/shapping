import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', ()=>{
    const user = ref<User | null>(null)
    const loading = ref<boolean>(false)

    const setUser = async () => {
        loading.value = true
        try {
            const response:UserResponse = await useAsyncData('users', () => request<any>('/admin/users'))
            console.log(response)
            // if(response?.status == 'success'){
            //     user.value = response?.data?.user;
            //     restaurant.value = response?.data?.restaurant;
            // }
        } catch (err){
            console.log(err)
        } finally {
            loading.value = false
        }
    }

    const clearUser = ()=>{
        user.value = null;
    }

    const checkLogin = () => {
        const token = useCookie('admin_token')
        return !!token.value
    }

    return {
        user,
        loading,
        setUser,
        clearUser,
        checkLogin,
    }

})