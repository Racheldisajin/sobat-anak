<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\{Product,Post,PostCategory,PostTag,Testimonial,Reward,User,UserPoint};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Product::truncate(); Post::truncate(); PostCategory::truncate(); PostTag::truncate(); Testimonial::truncate(); Reward::truncate(); UserPoint::truncate(); User::truncate();

        $admin = User::create(['name'=>'Felix','email'=>'felixzanqueen@gmail.com','password'=>'$2y$12$oA1YpHl0Jajpl0rN8SirieMPkcdTUFht6P.6fOiuM90wa3j1.Ejay','role'=>'admin']);
        UserPoint::create(['user_id'=>$admin->id,'points'=>1250]);

        $products = [
            ['Botol Susu Anti-Kolik','Bayi 0–12 bln',189000,'Terlaris',4.9,3241,25,'https://img.rocket.new/generatedImages/rocket_gen_img_1b9f97762-1766130752655.png'],
            ['Popok Premium Newborn','Bayi 0–12 bln',135000,'Stok Terbatas',4.8,1890,18,'https://img.rocket.new/generatedImages/rocket_gen_img_1ad59fa5f-1772142839813.png'],
            ['Boneka Edukatif Sensorik','Balita 1–3 thn',215000,'Baru',4.7,987,12,'https://img.rocket.new/generatedImages/rocket_gen_img_16582a9a6-1772711604593.png'],
            ['Set Baju Bayi Muslin','Pakaian',299000,null,4.9,2105,15,'https://img.rocket.new/generatedImages/rocket_gen_img_13ee4c0a8-1767742445786.png'],
            ['Stroller Lipat Ringan','Balita 1–3 thn',1450000,'Diskon 19%',4.6,542,7,'https://img.rocket.new/generatedImages/rocket_gen_img_1ac1784c7-1771669706135.png'],
            ['Set Mandi Bayi Lengkap','Perawatan',395000,'Bundle Hemat',4.8,1678,20,'https://images.unsplash.com/photo-1635874714425-c342060a4c58'],
            ['Mainan Gigit Silikon','Bayi 0–12 bln',89000,null,4.7,4320,30,'https://img.rocket.new/generatedImages/rocket_gen_img_1e14701d0-1772100946575.png'],
            ['Buku Cerita Anak Bergambar','Anak 3–12 thn',75000,null,4.9,5670,22,'https://images.unsplash.com/photo-1550701644-af041ecfe403'],
            ['Sereal Bayi Organik','Nutrisi',125000,'Organik',4.8,2890,16,'https://img.rocket.new/generatedImages/rocket_gen_img_1920981fd-1767550823123.png'],
            ['Sepatu Bayi Pertama','Pakaian',165000,'Baru',4.6,890,9,'https://images.unsplash.com/photo-1719595375830-d7503d9e72e9'],
            ['Puzzle Kayu Edukatif','Anak 3–12 thn',195000,null,4.7,1234,14,'https://img.rocket.new/generatedImages/rocket_gen_img_1fe9f393f-1778253574216.png'],
            ['Lotion Bayi Aloe Vera','Perawatan',95000,null,4.9,3456,24,'https://images.unsplash.com/photo-1625342000939-499ffc818b9d'],
        ];
        foreach($products as $p){ Product::create(['name'=>$p[0],'category'=>$p[1],'price'=>$p[2],'badge'=>$p[3],'rating'=>$p[4],'sold'=>$p[5],'stock'=>$p[6],'image'=>$p[7]]); }

        $cats = [];
        foreach(['Parenting','Edukasi','Mom Tips','Baby Care','Nutrisi'] as $name){ $cats[$name] = PostCategory::create(['slug'=>Str::slug($name),'name'=>$name]); }
        foreach(['parenting','bayi','mom-tips','edukasi','nutrisi'] as $tag){ PostTag::create(['slug'=>Str::slug($tag),'name'=>Str::title(str_replace('-',' ',$tag))]); }

        $posts = [
            ['Panduan Memilih Botol Susu Aman','Parenting','parenting,bayi','Tips memilih botol susu anti-kolik, BPA free, dan mudah dibersihkan untuk bayi.','https://images.unsplash.com/photo-1555252333-9f8e92e65df9'],
            ['Mainan Edukatif untuk Tumbuh Kembang Anak','Edukasi','edukasi,mainan-anak','Rekomendasi mainan sensorik, puzzle, dan buku bergambar untuk mendukung perkembangan si kecil.','https://images.unsplash.com/photo-1516627145497-ae6968895b74'],
            ['Checklist Perlengkapan Bayi Baru Lahir','Mom Tips','mom-tips,newborn','Daftar kebutuhan newborn yang penting agar belanja lebih hemat dan tidak berlebihan.','https://images.unsplash.com/photo-1522771930-78848d9293e8'],
            ['Tips Menjaga Kulit Bayi Tetap Lembap','Baby Care','baby-care,kulit-bayi','Cara memilih produk perawatan bayi yang lembut dan aman untuk kulit sensitif.','https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4'],
            ['MPASI Pertama: Apa yang Perlu Disiapkan?','Nutrisi','nutrisi,mpasi','Panduan sederhana menyiapkan perlengkapan MPASI dan jadwal makan awal si kecil.','https://images.unsplash.com/photo-1546015720-b8b30df5aa27'],
            ['Cara Membuat Rutinitas Tidur Bayi','Parenting','parenting,tidur-bayi','Tips membuat jam tidur lebih teratur agar bayi dan ibu sama-sama nyaman.','https://images.unsplash.com/photo-1546015720-8f2fbde05e46'],
            ['Mainan Sensorik Sesuai Usia Anak','Edukasi','edukasi,sensorik','Rekomendasi mainan berdasarkan usia untuk membantu motorik dan rasa ingin tahu anak.','https://images.unsplash.com/photo-1566576912321-d58ddd7a6088'],
            ['Perlengkapan Traveling dengan Bayi','Mom Tips','mom-tips,traveling','Checklist stroller, tas popok, botol, snack, dan barang wajib saat bepergian.','https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9'],
            ['Memilih Pakaian Bayi yang Nyaman','Baby Care','baby-care,pakaian','Bahan, ukuran, dan model pakaian yang aman untuk aktivitas harian si kecil.','https://images.unsplash.com/photo-1515488764276-beab7607c1e6'],
            ['Ide Aktivitas Edukatif di Rumah','Edukasi','edukasi,aktivitas-anak','Aktivitas sederhana dengan buku, puzzle, dan permainan warna untuk anak.','https://images.unsplash.com/photo-1484820540004-14229fe36ca4'],
            ['Cara Hemat Belanja Kebutuhan Bayi','Mom Tips','mom-tips,hemat','Strategi membeli bundle, membuat prioritas, dan menghindari barang yang jarang dipakai.','https://images.unsplash.com/photo-1555252333-9f8e92e65df9'],
            ['Panduan Membersihkan Botol Susu','Baby Care','baby-care,botol-susu','Langkah mencuci, steril, dan menyimpan botol susu agar tetap higienis.','https://images.unsplash.com/photo-1584464491033-06628f3a6b7b'],
        ];
        foreach($posts as $post){ Post::create(['slug'=>Str::slug($post[0]),'title'=>$post[0],'image'=>$post[4],'content'=>$post[3],'counter'=>0,'status'=>'published','category_id'=>$cats[$post[1]]->id,'tags'=>$post[2],'created_by'=>$admin->id,'updated_by'=>null,'published_at'=>now(),'meta_data'=>['summary'=>$post[3]],'source'=>'web']); }

        Testimonial::create(['name'=>'Nadia Putri','message'=>'Produknya lengkap dan kualitasnya bagus. Anak saya suka banget sama mainan edukatifnya!']);
        Testimonial::create(['name'=>'Rani Maharani','message'=>'Belanja perlengkapan bayi jadi gampang. Desain websitenya lucu dan mudah dipakai.']);
        Testimonial::create(['name'=>'Dewi Lestari','message'=>'Mini game dan poinnya bikin anak semangat. Voucher belanjanya juga berguna.']);
        Reward::create(['name'=>'Voucher Belanja Rp25.000','points'=>500,'description'=>'Tukar poin untuk potongan belanja produk bayi.']);
        Reward::create(['name'=>'Gratis Ongkir','points'=>300,'description'=>'Voucher ongkir untuk pembelian berikutnya.']);
        Reward::create(['name'=>'Mystery Gift Anak','points'=>800,'description'=>'Hadiah kejutan edukatif untuk si kecil.']);
    }
}
