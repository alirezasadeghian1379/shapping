<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $users = [
            ['name' => 'سارا احمدی', 'mobile' => '09121111111', 'email' => 'sara@example.test'],
            ['name' => 'علی رضایی', 'mobile' => '09122222222', 'email' => 'ali@example.test'],
            ['name' => 'مریم کریمی', 'mobile' => '09123333333', 'email' => 'maryam@example.test'],
            ['name' => 'رضا محمدی', 'mobile' => '09124444444', 'email' => 'reza@example.test'],
            ['name' => 'نگار حسینی', 'mobile' => '09125555555', 'email' => 'negar@example.test'],
        ];
        foreach ($users as $user) {
            User::updateOrCreate(['mobile' => $user['mobile']], $user + ['profile_completed_at' => $now, 'is_active' => true])
                ->forceFill(['mobile_verified_at' => $now])->save();
        }
        $customers = User::whereIn('mobile', array_column($users, 'mobile'))->get()->keyBy('mobile');
        $admin = User::where('mobile', '09120000000')->firstOrFail();

        $locations = [
            'آذربایجان شرقی'=>['تبریز','مراغه','مرند'],'آذربایجان غربی'=>['ارومیه','خوی','مهاباد'],'اردبیل'=>['اردبیل','مشگین‌شهر','پارس‌آباد'],
            'اصفهان'=>['اصفهان','کاشان','نجف‌آباد'],'البرز'=>['کرج','فردیس','نظرآباد'],'ایلام'=>['ایلام','دهلران','آبدانان'],
            'بوشهر'=>['بوشهر','برازجان','گناوه'],'تهران'=>['تهران','شهریار','اسلامشهر','ری'],'چهارمحال و بختیاری'=>['شهرکرد','بروجن','فارسان'],
            'خراسان جنوبی'=>['بیرجند','قائن','طبس'],'خراسان رضوی'=>['مشهد','نیشابور','سبزوار'],'خراسان شمالی'=>['بجنورد','شیروان','اسفراین'],
            'خوزستان'=>['اهواز','آبادان','دزفول'],'زنجان'=>['زنجان','ابهر','خرمدره'],'سمنان'=>['سمنان','شاهرود','دامغان'],
            'سیستان و بلوچستان'=>['زاهدان','چابهار','ایرانشهر'],'فارس'=>['شیراز','مرودشت','جهرم'],'قزوین'=>['قزوین','تاکستان','آبیک'],
            'قم'=>['قم','جعفریه','کهک'],'کردستان'=>['سنندج','سقز','مریوان'],'کرمان'=>['کرمان','رفسنجان','سیرجان'],
            'کرمانشاه'=>['کرمانشاه','اسلام‌آباد غرب','پاوه'],'کهگیلویه و بویراحمد'=>['یاسوج','دوگنبدان','دهدشت'],'گلستان'=>['گرگان','گنبد کاووس','علی‌آباد کتول'],
            'گیلان'=>['رشت','بندرانزلی','لاهیجان'],'لرستان'=>['خرم‌آباد','بروجرد','دورود'],'مازندران'=>['ساری','بابل','آمل'],
            'مرکزی'=>['اراک','ساوه','خمین'],'هرمزگان'=>['بندرعباس','قشم','میناب'],'همدان'=>['همدان','ملایر','نهاوند'],'یزد'=>['یزد','میبد','اردکان'],
        ];
        foreach ($locations as $provinceIndex => $cityNames) {
            $provinceSlug='province-'.(array_search($provinceIndex,array_keys($locations),true)+1);
            DB::table('provinces')->updateOrInsert(['name'=>$provinceIndex],['slug'=>$provinceSlug,'sort_order'=>array_search($provinceIndex,array_keys($locations),true)+1,'is_active'=>true,'updated_at'=>$now,'created_at'=>$now]);
            $provinceId=DB::table('provinces')->where('name',$provinceIndex)->value('id');
            foreach($cityNames as $cityIndex=>$cityName)DB::table('cities')->updateOrInsert(['province_id'=>$provinceId,'name'=>$cityName],['slug'=>$provinceSlug.'-city-'.($cityIndex+1),'sort_order'=>$cityIndex+1,'is_active'=>true,'updated_at'=>$now,'created_at'=>$now]);
        }

        $categories = [
            ['title' => 'سفال و سرامیک', 'slug' => 'pottery', 'description' => 'ظروف و دکورهای سفالی دست‌ساز'],
            ['title' => 'گلیم و بافته‌ها', 'slug' => 'woven-art', 'description' => 'بافته‌های اصیل و رنگارنگ ایرانی'],
            ['title' => 'زیورآلات دست‌ساز', 'slug' => 'handmade-jewelry', 'description' => 'زیورآلات هنری با طراحی منحصربه‌فرد'],
            ['title' => 'دکور و هدیه', 'slug' => 'decor-gifts', 'description' => 'هدیه‌های خاص برای خانه‌های ایرانی'],
        ];
        foreach ($categories as $i => $category) DB::table('categories')->updateOrInsert(['slug' => $category['slug']], $category + ['sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $categoryIds = DB::table('categories')->whereIn('slug', array_column($categories, 'slug'))->pluck('id', 'slug');

        $brands = [
            ['title' => 'هنر آبی', 'slug' => 'honar-abi', 'description' => 'کارگاه سفال و سرامیک ایرانی'],
            ['title' => 'نقشینه', 'slug' => 'naghshineh', 'description' => 'بافته‌های اصیل با مواد طبیعی'],
            ['title' => 'ماه‌نقره', 'slug' => 'mah-noghre', 'description' => 'زیورآلات دست‌ساز مینیمال'],
            ['title' => 'خانه هنر', 'slug' => 'khaneh-honar', 'description' => 'محصولات دکوراتیو هنرمندان ایران'],
        ];
        foreach ($brands as $brand) DB::table('brands')->updateOrInsert(['slug' => $brand['slug']], $brand + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $brandIds = DB::table('brands')->whereIn('slug', array_column($brands, 'slug'))->pluck('id', 'slug');

        $demoAttributes = [
            ['title'=>'رنگ','slug'=>'color','type'=>'color','is_variant'=>true,'values'=>[['فیروزه‌ای','turquoise','#14b8a6'],['لاجوردی','azure','#1d4ed8'],['قرمز لاکی','lacquer-red','#b91c1c'],['طلایی','gold','#d4a72c']]],
            ['title'=>'اندازه','slug'=>'size','type'=>'select','is_variant'=>true,'values'=>[['کوچک','small',null],['متوسط','medium',null],['بزرگ','large',null]]],
            ['title'=>'جنس','slug'=>'material','type'=>'select','is_variant'=>false,'values'=>[['سفال','pottery',null],['نقره','silver',null],['چوب','wood',null],['پشم طبیعی','wool',null]]],
        ];
        foreach($demoAttributes as $attributeIndex=>$item){$values=$item['values'];unset($item['values']);DB::table('attributes')->updateOrInsert(['slug'=>$item['slug']],$item+['is_filterable'=>true,'unit'=>null,'updated_at'=>$now,'created_at'=>$now]);$attributeId=DB::table('attributes')->where('slug',$item['slug'])->value('id');foreach($values as $valueIndex=>[$label,$value,$color])DB::table('attribute_values')->updateOrInsert(['attribute_id'=>$attributeId,'value'=>$value],['label'=>$label,'color'=>$color,'sort_order'=>$valueIndex+1,'updated_at'=>$now,'created_at'=>$now]);}

        $products = [
            ['ست فنجان سفالی فیروزه‌ای','turquoise-pottery-cups','pottery','honar-abi',485000,550000,18,'آبی فیروزه‌ای','۶ عددی','demo/pottery.svg'],
            ['گلدان سرامیکی طرح انار','pomegranate-ceramic-vase','pottery','honar-abi',690000,760000,9,'کرم','ارتفاع ۲۸ سانت','demo/pottery.svg'],
            ['بشقاب دیوارکوب میناکاری','minakari-wall-plate','pottery','khaneh-honar',1250000,1390000,6,'لاجوردی','قطر ۲۵ سانت','demo/pottery.svg'],
            ['گلیم دست‌بافت طرح هریس','heriz-handwoven-kilim','woven-art','naghshineh',3850000,4200000,4,'قرمز لاکی','۱×۱.۵ متر','demo/woven.svg'],
            ['کوسن گلیم دست‌دوز','handmade-kilim-cushion','woven-art','naghshineh',590000,650000,15,'چند رنگ','۴۰×۴۰','demo/woven.svg'],
            ['رانر قلمکار سنتی','traditional-ghalamkar-runner','woven-art','khaneh-honar',425000,null,22,'سرمه‌ای','۱۲۰ سانت','demo/woven.svg'],
            ['گردنبند نقره مرغ آمین','morgh-amin-silver-necklace','handmade-jewelry','mah-noghre',1780000,1950000,8,'نقره‌ای','زنجیر ۴۵ سانت','demo/jewelry.svg'],
            ['گوشواره برنجی شمسه','shamseh-brass-earrings','handmade-jewelry','mah-noghre',620000,690000,12,'طلایی','سبک','demo/jewelry.svg'],
            ['دستبند سنگ عقیق','agate-stone-bracelet','handmade-jewelry','mah-noghre',890000,null,10,'قرمز','فری سایز','demo/jewelry.svg'],
            ['جعبه چوبی خاتم‌کاری','khatam-wooden-box','decor-gifts','khaneh-honar',1450000,1600000,7,'قهوه‌ای','۲۰×۱۲ سانت','demo/decor.svg'],
            ['چراغ رومیزی مشبک','mashrabiya-table-lamp','decor-gifts','khaneh-honar',2150000,2400000,5,'گردویی','ارتفاع ۳۵ سانت','demo/decor.svg'],
            ['پک هدیه نوروز ایرانی','iranian-nowruz-gift-box','decor-gifts','khaneh-honar',980000,1150000,20,'سبز','پک کامل','demo/decor.svg'],
        ];
        $variantIds = [];
        foreach ($products as $i => [$title,$slug,$category,$brand,$price,$compare,$stock,$color,$size,$image]) {
            DB::table('products')->updateOrInsert(['slug' => $slug], ['category_id' => $categoryIds[$category], 'brand_id' => $brandIds[$brand], 'title' => $title, 'product_type' => 'physical', 'short_description' => "{$title}، تولیدشده توسط هنرمندان ایرانی با بسته‌بندی ایمن و مناسب هدیه.", 'description' => "این محصول با دقت و به‌صورت دست‌ساز تولید شده است. تفاوت‌های جزئی در نقش و رنگ، نشانه اصالت اثر و منحصربه‌فرد بودن آن است.\n\nارسال محصول در بسته‌بندی مقاوم انجام می‌شود و امکان بازگشت طبق قوانین فروشگاه وجود دارد.", 'status' => 'published', 'is_featured' => $i < 6, 'view_count' => 80 + ($i * 37), 'seo' => json_encode(['title' => $title.' | هنرمارکت', 'description' => 'خرید '.$title.' با ضمانت اصالت'], JSON_UNESCAPED_UNICODE), 'published_at' => $now->copy()->subDays(15 - $i), 'updated_at' => $now, 'created_at' => $now]);
            $productId = DB::table('products')->where('slug', $slug)->value('id');
            $sku = 'HM-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            DB::table('product_variants')->updateOrInsert(['sku' => $sku], ['product_id' => $productId, 'price' => $price, 'compare_at_price' => $compare, 'stock' => $stock, 'low_stock_threshold' => 3, 'weight' => 400 + $i * 90, 'attributes' => json_encode(['color' => $color, 'size' => $size], JSON_UNESCAPED_UNICODE), 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]);
            $variantIds[] = DB::table('product_variants')->where('sku', $sku)->value('id');
            DB::table('product_media')->updateOrInsert(['product_id' => $productId, 'sort_order' => 0], ['path' => $image, 'alt' => $title, 'type' => 'image', 'updated_at' => $now, 'created_at' => $now]);
        }

        foreach ([['WELCOME10','تخفیف اولین خرید','percentage',10,150000,300000],['HANDMADE200','هدیه هنرمارکت','fixed',200000,null,1000000]] as [$code,$title,$type,$value,$max,$min]) DB::table('discount_codes')->updateOrInsert(['code'=>$code], ['title'=>$title,'type'=>$type,'value'=>$value,'max_discount'=>$max,'min_order_amount'=>$min,'usage_limit'=>100,'per_user_limit'=>1,'used_count'=>0,'starts_at'=>$now->copy()->subDay(),'expires_at'=>$now->copy()->addMonths(2),'is_active'=>true,'conditions'=>null,'updated_at'=>$now,'created_at'=>$now]);

        $shipping = DB::table('shipping_methods')->where('code', 'post')->value('id');
        $tehranProvince=DB::table('provinces')->where('name','تهران')->first();$tehranCity=DB::table('cities')->where('province_id',$tehranProvince->id)->where('name','تهران')->first();
        foreach ($customers->values() as $i => $user) DB::table('addresses')->updateOrInsert(['user_id'=>$user->id,'title'=>'خانه'], ['receiver_name'=>$user->name,'receiver_mobile'=>$user->mobile,'province_id'=>$tehranProvince->id,'city_id'=>$tehranCity->id,'province'=>'تهران','city'=>'تهران','address'=>'خیابان ولیعصر، کوچه هنر، پلاک '.($i+10),'postal_code'=>'141'.str_pad((string)$i,7,'0'),'is_default'=>true,'updated_at'=>$now,'created_at'=>$now]);
        $statuses = [['delivered','paid'],['processing','paid'],['shipped','paid'],['pending','unpaid'],['cancelled','failed']];
        foreach ($customers->values() as $i => $user) {
            $variant = DB::table('product_variants')->where('id', $variantIds[$i * 2])->first(); $product = DB::table('products')->find($variant->product_id); $address = DB::table('addresses')->where('user_id',$user->id)->first(); [$status,$paymentStatus]=$statuses[$i]; $subtotal=$variant->price*($i%2+1); $shippingAmount=$subtotal>=2000000?0:85000;
            $number = '00000000-0000-4000-8000-'.str_pad((string)($i+1),12,'0');
            DB::table('orders')->updateOrInsert(['number'=>$number], ['user_id'=>$user->id,'address_id'=>$address->id,'shipping_method_id'=>$shipping,'status'=>$status,'payment_status'=>$paymentStatus,'subtotal'=>$subtotal,'discount_amount'=>0,'shipping_amount'=>$shippingAmount,'payable_amount'=>$subtotal+$shippingAmount,'address_snapshot'=>json_encode(['receiver_name'=>$user->name,'mobile'=>$user->mobile,'province'=>'تهران','city'=>'تهران','address'=>$address->address,'postal_code'=>$address->postal_code],JSON_UNESCAPED_UNICODE),'customer_note'=>$i===1?'لطفاً برای هدیه بسته‌بندی شود.':null,'updated_at'=>$now->copy()->subDays($i),'created_at'=>$now->copy()->subDays($i+2)]);
            $orderId=DB::table('orders')->where('number',$number)->value('id');
            DB::table('order_items')->updateOrInsert(['order_id'=>$orderId,'sku'=>$variant->sku], ['product_id'=>$product->id,'variant_id'=>$variant->id,'title'=>$product->title,'attributes'=>$variant->attributes,'unit_price'=>$variant->price,'quantity'=>$i%2+1,'total'=>$subtotal,'updated_at'=>$now,'created_at'=>$now]);
            if($paymentStatus==='paid') DB::table('payments')->updateOrInsert(['order_id'=>$orderId,'gateway'=>'zarinpal'], ['authority'=>'DEMO-'.($i+1),'reference_id'=>'REF-1405-'.($i+1),'amount'=>$subtotal+$shippingAmount,'status'=>'paid','paid_at'=>$now->copy()->subDays($i+1),'updated_at'=>$now,'created_at'=>$now]);
        }

        $articles = [['راهنمای نگهداری از ظروف سفالی','pottery-care-guide'],['چطور یک هدیه دست‌ساز انتخاب کنیم؟','handmade-gift-guide'],['قصه گلیم ایرانی؛ از تار تا پود','story-of-iranian-kilim']];
        foreach($articles as $i=>[$title,$slug]) DB::table('articles')->updateOrInsert(['slug'=>$slug], ['author_id'=>$admin->id,'title'=>$title,'excerpt'=>'نکته‌های کاربردی و خواندنی از دنیای هنر و صنایع دستی ایران.','body'=>'صنایع دستی فقط یک کالا نیست؛ نتیجه زمان، مهارت و داستان هنرمندی است که آن را خلق کرده است. در این مقاله نکته‌های ساده و کاربردی برای انتخاب و نگهداری بهتر آثار هنری را مرور می‌کنیم.','cover'=>'demo/article.svg','status'=>'published','tags'=>json_encode(['صنایع دستی','راهنمای خرید'],JSON_UNESCAPED_UNICODE),'view_count'=>120+$i*85,'published_at'=>$now->copy()->subDays(10-$i),'updated_at'=>$now,'created_at'=>$now]);
        $productIds = DB::table('products')->whereIn('slug', array_column($products, 1))->pluck('id')->values();
        $articleIds = DB::table('articles')->whereIn('slug', array_column($articles, 1))->pluck('id')->values();
        foreach ($customers->values() as $i => $user) {
            DB::table('favorites')->updateOrInsert(['user_id'=>$user->id,'product_id'=>$productIds[$i]], ['updated_at'=>$now,'created_at'=>$now]);
            DB::table('saved_articles')->updateOrInsert(['user_id'=>$user->id,'article_id'=>$articleIds[$i % $articleIds->count()]], ['updated_at'=>$now,'created_at'=>$now]);
            DB::table('comments')->updateOrInsert(['user_id'=>$user->id,'commentable_type'=>'App\\Models\\Product','commentable_id'=>$productIds[$i],'body'=>'کیفیت ساخت خیلی خوب بود و بسته‌بندی سالم به دستم رسید.'], ['rating'=>5-($i%2),'status'=>'approved','is_buyer'=>true,'is_staff'=>false,'updated_at'=>$now->copy()->subDays($i),'created_at'=>$now->copy()->subDays($i+1)]);
            DB::table('comments')->updateOrInsert(['user_id'=>$user->id,'commentable_type'=>'App\\Models\\Article','commentable_id'=>$articleIds[$i % $articleIds->count()],'body'=>'مقاله مفید و روانی بود، ممنون از نکته‌های کاربردی.'], ['rating'=>null,'status'=>'approved','is_buyer'=>false,'is_staff'=>false,'updated_at'=>$now,'created_at'=>$now]);
            DB::table('product_questions')->updateOrInsert(['product_id'=>$productIds[$i+1],'user_id'=>$user->id,'question'=>'این محصول برای هدیه بسته‌بندی ویژه هم دارد؟'], ['answered_by'=>$i<3?$admin->id:null,'answer'=>$i<3?'بله، در توضیحات سفارش درخواست بسته‌بندی هدیه را بنویسید.':null,'status'=>$i<3?'answered':'pending','answered_at'=>$i<3?$now:null,'updated_at'=>$now,'created_at'=>$now]);
            DB::table('search_logs')->updateOrInsert(['user_id'=>$user->id,'query'=>['سفال','گلیم','هدیه دست‌ساز','گردنبند','دکور سنتی'][$i]], ['result_count'=>3+$i,'ip'=>'127.0.0.1','created_at'=>$now->copy()->subHours($i+1),'updated_at'=>$now]);
        }

        DB::table('sliders')->updateOrInsert(['title'=>'جشنواره هنر دست'], ['image'=>'demo/banner.svg','mobile_image'=>'demo/banner.svg','link'=>'/campaigns/summer-handmade','position'=>'home','sort_order'=>1,'is_active'=>true,'starts_at'=>$now->copy()->subDay(),'ends_at'=>$now->copy()->addDays(12),'updated_at'=>$now,'created_at'=>$now]);
        foreach ([['انتخاب‌های سفالی','home-middle','/products?category=pottery'],['هدیه‌های خاص ایرانی','home-bottom','/products?category=decor-gifts']] as $i=>[$title,$placement,$link]) DB::table('banners')->updateOrInsert(['title'=>$title], ['image'=>'demo/banner.svg','link'=>$link,'placement'=>$placement,'sort_order'=>$i+1,'is_active'=>true,'updated_at'=>$now,'created_at'=>$now]);
        foreach([['روش ثبت سفارش چگونه است؟','پس از افزودن محصول به سبد، آدرس و روش ارسال را انتخاب و پرداخت را تکمیل کنید.'],['آیا محصولات ضمانت اصالت دارند؟','بله، همه آثار مستقیماً از هنرمندان و کارگاه‌های تأییدشده تهیه می‌شوند.'],['امکان مرجوع کردن کالا وجود دارد؟','تا ۷ روز پس از تحویل و مطابق شرایط بازگشت می‌توانید درخواست مرجوعی ثبت کنید.'],['چطور سفارش را پیگیری کنم؟','وارد پروفایل شوید و از بخش سفارش‌ها وضعیت و کد رهگیری را ببینید.']] as $i=>[$q,$a]) DB::table('faqs')->updateOrInsert(['question'=>$q],['answer'=>$a,'group'=>'خرید و ارسال','sort_order'=>$i+1,'is_active'=>true,'updated_at'=>$now,'created_at'=>$now]);

        DB::table('sale_campaigns')->updateOrInsert(['slug'=>'summer-handmade'],['title'=>'جشنواره هنر دست','badge'=>'تا ۲۰٪ تخفیف','image'=>'demo/banner.svg','starts_at'=>$now->copy()->subDay(),'ends_at'=>$now->copy()->addDays(12),'is_active'=>true,'sort_order'=>1,'updated_at'=>$now,'created_at'=>$now]);
        $campaignId=DB::table('sale_campaigns')->where('slug','summer-handmade')->value('id');
        foreach(array_slice($variantIds,0,4) as $variantId){$price=DB::table('product_variants')->where('id',$variantId)->value('price');DB::table('sale_campaign_items')->updateOrInsert(['campaign_id'=>$campaignId,'variant_id'=>$variantId],['sale_price'=>(int)round($price*.85),'stock_limit'=>10,'sold'=>random_int(1,4),'updated_at'=>$now,'created_at'=>$now]);}
        foreach($customers as $user) DB::table('user_notifications')->updateOrInsert(['user_id'=>$user->id,'type'=>'announcement','title'=>'به هنرمارکت خوش آمدید'],['message'=>'با کد WELCOME10 اولین خریدتان را با تخفیف ثبت کنید.','action_url'=>'/products','updated_at'=>$now,'created_at'=>$now]);
    }
}
