<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'مدیر کل', 'abilities' => ['*'], 'is_super_admin' => true]);
        Role::firstOrCreate(['name' => 'content-manager'], ['label' => 'مدیر محتوا', 'abilities' => ['admin.access', 'content.manage']]);
        $admin = User::firstOrCreate(['mobile' => '09120000000'], ['name' => 'مدیر فروشگاه', 'mobile_verified_at' => now(), 'profile_completed_at' => now()]);
        $admin->roles()->syncWithoutDetaching($adminRole);

        Category::firstOrCreate(['slug' => 'handicrafts'], ['title' => 'صنایع دستی', 'is_active' => true]);
        DB::table('shipping_methods')->updateOrInsert(['code' => 'post'], ['title' => 'پست پیشتاز', 'description' => 'تحویل بین ۳ تا ۵ روز کاری', 'base_cost' => 85000, 'free_threshold' => 2000000, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]);
        foreach (range(1, 7) as $day) {
            DB::table('delivery_slots')->updateOrInsert(['date' => today()->addDays($day)->toDateString(), 'starts_at' => '09:00:00'], ['ends_at' => '15:00:00', 'capacity' => 30, 'reserved' => 0, 'extra_cost' => 0, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]);
        }
        foreach ([
            ['general', 'site_title', 'فروشگاه صنایع دستی', true],
            ['general', 'site_tagline', 'قصه‌ی هنر ایرانی در خانه‌ی شما', true],
            ['branding', 'primary_color', '#0f766e', true],
            ['branding', 'logo', null, true],
            ['pages', 'about_us', '', true],
            ['pages', 'contact_us', '', true],
            ['social', 'instagram', '', true],
        ] as [$group, $key, $value, $public]) {
            Setting::firstOrCreate(['key' => $key], ['group' => $group, 'value' => $value, 'is_public' => $public]);
        }

        $this->call(DemoStoreSeeder::class);
    }
}
