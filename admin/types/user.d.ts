interface User {
    avatar?: string,
    birth_date?: string,
    created_at: string,
    email?: string,
    email_verified_at?: string,
    id: number,
    is_active: boolean,
    mobile: string,
    mobile_verified_at?: string,
    name?: string,
    national_code?: string,
    profile_completed_at?: string,
    roles?: Role[],
    updated_at?: string,
}

interface Role {
    abilities?: any,
    created_at?: string,
    id: number,
    is_super_admin: boolean,
    label?: string,
    name?: string,
    updated_at: string,
    pivot?: Pivot
}
interface Pivot {
    created_at: string,
    role_id: number,
    updated_at: string,
    user_id: number,
}
interface UserResponse {

}