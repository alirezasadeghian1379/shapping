<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationAndAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_messages_and_attribute_names_are_persian(): void
    {
        $this->postJson('/api/v1/auth/otp/request', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.mobile.0', 'واردکردن شماره موبایل الزامی است.');
    }

    public function test_public_can_get_active_provinces_and_their_cities(): void
    {
        $province = DB::table('provinces')->insertGetId(['name'=>'تهران','slug'=>'tehran','is_active'=>true,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()]);
        DB::table('cities')->insert(['province_id'=>$province,'name'=>'تهران','slug'=>'tehran-city','is_active'=>true,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()]);

        $this->getJson('/api/v1/locations/provinces')->assertOk()->assertJsonPath('0.name', 'تهران');
        $this->getJson("/api/v1/locations/cities?province_id={$province}")->assertOk()->assertJsonPath('0.name', 'تهران');
    }

    public function test_customer_can_create_address_only_with_city_from_selected_province(): void
    {
        $token = $this->login('09123456789');
        $tehran = DB::table('provinces')->insertGetId(['name'=>'تهران','slug'=>'tehran','is_active'=>true,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()]);
        $fars = DB::table('provinces')->insertGetId(['name'=>'فارس','slug'=>'fars','is_active'=>true,'sort_order'=>2,'created_at'=>now(),'updated_at'=>now()]);
        $shiraz = DB::table('cities')->insertGetId(['province_id'=>$fars,'name'=>'شیراز','slug'=>'shiraz','is_active'=>true,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()]);
        $payload = ['title'=>'خانه','receiver_name'=>'کاربر تست','receiver_mobile'=>'09123456789','province_id'=>$tehran,'city_id'=>$shiraz,'address'=>'خیابان تست، پلاک ۱۰','postal_code'=>'1234567890'];

        $this->withToken($token)->postJson('/api/v1/addresses', $payload)
            ->assertUnprocessable()->assertJsonPath('errors.city_id.0', 'شهر انتخاب‌شده متعلق به این استان نیست یا غیرفعال است.');
    }

    private function login(string $mobile): string
    {
        $this->postJson('/api/v1/auth/otp/request', ['mobile'=>$mobile])->assertOk();
        return $this->postJson('/api/v1/auth/otp/verify', ['mobile'=>$mobile,'code'=>'12345'])->assertOk()->json('token');
    }
}
