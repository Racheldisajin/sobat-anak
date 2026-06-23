<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$testimonials = App\Models\Testimonial::query()
    ->orderByDesc('likes_count')
    ->latest()
    ->take(3)
    ->get();
echo json_encode($testimonials);
