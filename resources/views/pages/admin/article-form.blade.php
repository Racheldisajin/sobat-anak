@php
    $mode = $mode ?? 'create';
    $article = $article ?? new \App\Models\Post();
    $categories = $categories ?? collect();
    $isCreate = $mode === 'create';
@endphp

@extends('layouts.admin')
@section('title', ($isCreate ? 'Tambah' : 'Edit').' Artikel — Admin SobatAnak')
@section('page-title', ($isCreate ? 'Tambah' : 'Edit').' Artikel')

@section('admin-content')
<style>
    .admin-article-page{
        background:
            radial-gradient(circle at 12% 8%, rgba(75,191,176,.18), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(232,117,106,.16), transparent 26%),
            linear-gradient(180deg,#FAFCFC 0%,#F6FBFA 100%);
        min-height: calc(100vh - 100px);
        padding: 34px 0 56px;
    }
    .admin-article-wrap{width:min(1180px, calc(100% - 40px));margin:0 auto;}
    .admin-article-nav{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:22px;}
    .admin-article-breadcrumb{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
    .admin-article-back{
        display:inline-flex;align-items:center;gap:8px;border:1px solid #D4EEEC;background:#fff;color:#2A3D3C;
        padding:11px 18px;border-radius:999px;font-weight:900;box-shadow:0 12px 28px rgba(42,61,60,.06);transition:.2s;
    }
    .admin-article-back:hover{transform:translateY(-2px);border-color:#4BBFB0;background:#F3FFFD;}
    .admin-article-hero{
        position:relative;overflow:hidden;border:1px solid rgba(212,238,236,.9);border-radius:30px;padding:30px;
        background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(240,252,250,.96));box-shadow:0 24px 70px rgba(42,61,60,.08);
        margin-bottom:22px;
    }
    .admin-article-hero:after{content:"";position:absolute;right:-60px;top:-75px;width:230px;height:230px;border-radius:999px;background:rgba(232,117,106,.13);}
    .admin-article-kicker{display:inline-flex;align-items:center;gap:8px;color:#E8756A;font-size:.76rem;font-weight:1000;letter-spacing:.13em;text-transform:uppercase;position:relative;z-index:1;}
    .admin-article-title{font-size:clamp(2.2rem,5vw,4.6rem);line-height:.98;font-weight:1000;color:#2A3D3C;margin-top:10px;position:relative;z-index:1;letter-spacing:-.04em;}
    .admin-article-title span{color:#4BBFB0;}
    .admin-article-desc{max-width:780px;color:#5E7977;font-weight:800;margin-top:14px;position:relative;z-index:1;}
    .admin-article-shell{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:22px;align-items:start;}
    .admin-article-card{background:rgba(255,255,255,.98);border:1px solid #D4EEEC;border-radius:28px;padding:24px;box-shadow:0 20px 55px rgba(42,61,60,.07);}
    .admin-article-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px dashed #D4EEEC;}
    .admin-article-card-head small{color:#E8756A;text-transform:uppercase;letter-spacing:.12em;font-weight:1000;font-size:.7rem;}
    .admin-article-card-head h2{font-size:1.35rem;font-weight:1000;color:#2A3D3C;margin-top:2px;}
    .admin-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .admin-form-field{display:flex;flex-direction:column;gap:8px;margin-bottom:16px;color:#2A3D3C;font-weight:1000;}
    .admin-form-field.full{grid-column:1/-1;}
    .admin-form-field small{font-weight:800;color:#6B8A88;line-height:1.45;}
    .admin-form-field input,.admin-form-field select,.admin-form-field textarea{
        width:100%;border:1.5px solid #D4EEEC;background:#FAFCFC;border-radius:18px;padding:14px 16px;font-weight:850;color:#2A3D3C;outline:none;transition:.2s;
    }
    .admin-form-field textarea{min-height:300px;resize:vertical;line-height:1.65;}
    .admin-form-field input:focus,.admin-form-field select:focus,.admin-form-field textarea:focus{border-color:#4BBFB0;box-shadow:0 0 0 5px rgba(75,191,176,.13);background:#fff;}
    .admin-form-field input::placeholder,.admin-form-field textarea::placeholder{color:#9AAEAC;font-weight:800;}
    .admin-side-card{position:sticky;top:18px;}
    .admin-image-box{border:2px dashed #D4EEEC;background:linear-gradient(135deg,#F7FFFE,#fff);border-radius:24px;min-height:245px;display:flex;align-items:center;justify-content:center;overflow:hidden;text-align:center;color:#6B8A88;font-weight:900;margin-bottom:16px;}
    .admin-image-box img{width:100%;height:245px;object-fit:cover;display:block;}
    .admin-image-empty{padding:22px;}
    .admin-image-empty b{display:block;color:#2A3D3C;font-size:1.15rem;margin-bottom:6px;}
    .admin-file-input{background:#fff!important;cursor:pointer;}
    .admin-file-input::file-selector-button{border:0;background:#4BBFB0;color:#fff;border-radius:999px;padding:10px 14px;font-weight:1000;margin-right:12px;cursor:pointer;}
    .admin-submit-btn{width:100%;border:0;background:#E8756A;color:#fff;border-radius:999px;padding:16px 20px;font-weight:1000;font-size:1.02rem;box-shadow:0 16px 34px rgba(232,117,106,.24);transition:.2s;}
    .admin-submit-btn:hover{background:#D05A50;transform:translateY(-2px);}
    .admin-note{font-weight:850;color:#6B8A88;line-height:1.5;margin-top:14px;text-align:center;}
    .admin-alert-error{background:#FFF1F1;border:1px solid rgba(232,117,106,.45);color:#B94138;border-radius:22px;padding:14px 18px;font-weight:1000;margin-bottom:18px;}
    @media(max-width:960px){.admin-article-shell{grid-template-columns:1fr}.admin-side-card{position:static}.admin-form-grid{grid-template-columns:1fr}.admin-article-card{padding:18px}.admin-article-hero{padding:24px}.admin-article-wrap{width:min(100% - 24px,1180px)}}
</style>

<section class="admin-article-page">
    <div class="admin-article-wrap">
        <div class="admin-article-nav">
            <div class="admin-article-breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="admin-article-back">← Admin Dashboard</a>
                <a href="{{ route('admin.articles') }}" class="admin-article-back">← Daftar Artikel</a>
            </div>
        </div>

        <div class="admin-article-hero">
            <span class="admin-article-kicker">✦ Admin Postingan Berita</span>
            <h1 class="admin-article-title">{{ $isCreate ? 'Buat Artikel' : 'Edit Artikel' }} <span>{{ $isCreate ? 'Baru' : 'SobatAnak' }}</span></h1>
            <p class="admin-article-desc">Isi artikel parenting, kesehatan anak, produk, atau tips Mom & Baby Care. Tampilan ini sudah dirapikan supaya form tidak dempet dan lebih enak dipakai admin.</p>
        </div>

        @if($errors->any())
            <div class="admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ $isCreate ? route('admin.articles.store') : route('admin.articles.update', $article) }}" class="admin-article-shell">
            @csrf
            @if(!$isCreate) @method('PATCH') @endif

            <div class="admin-article-card">
                <div class="admin-article-card-head">
                    <div>
                        <small>Konten Artikel</small>
                        <h2>{{ $isCreate ? 'Tulis postingan baru' : 'Perbarui postingan' }}</h2>
                    </div>
                </div>

                <label class="admin-form-field full">Judul Postingan
                    <input name="title" value="{{ old('title', $article->title) }}" required placeholder="Contoh: Tips Memilih Popok Newborn yang Aman">
                </label>

                <label class="admin-form-field full">Slug URL
                    <input name="slug" value="{{ old('slug', $article->slug) }}" placeholder="Kosongkan untuk dibuat otomatis">
                    <small>Slug dipakai untuk URL artikel. Contoh: tips-memilih-popok-newborn</small>
                </label>

                <div class="admin-form-grid">
                    <label class="admin-form-field">Kategori
                        <select name="category_id" required>
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $article->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="admin-form-field">Status
                        <select name="status" required>
                            <option value="published" @selected(old('status', $article->status ?: 'published') === 'published')>Published</option>
                            <option value="draft" @selected(old('status', $article->status) === 'draft')>Draft</option>
                        </select>
                        <small>Draft tidak tampil di halaman artikel user.</small>
                    </label>
                </div>

                <label class="admin-form-field full">Tags
                    <input name="tags" value="{{ old('tags', $article->tags) }}" placeholder="parenting, bayi, mom tips">
                    <small>Pisahkan tag dengan koma agar AI dan pencarian artikel lebih mudah menemukan konten.</small>
                </label>

                @php
                    $preparedAdminArticle = !$isCreate ? \App\Support\SobatArticleContent::find($article->slug) : null;
                    $adminArticleContent = old('content', $preparedAdminArticle ? \App\Support\SobatArticleContent::toPlainText($preparedAdminArticle) : $article->content);
                @endphp

                <label class="admin-form-field full">Isi Artikel / Content
                    <textarea name="content" required placeholder="Tulis isi artikel lengkap di sini...">{{ $adminArticleContent }}</textarea>
                    <small>Isi ini disamakan dengan materi artikel yang tampil di halaman user. Tidak mengubah database sampai tombol Update Artikel ditekan.</small>
                </label>
            </div>

            <aside class="admin-article-card admin-side-card">
                <div class="admin-article-card-head">
                    <div>
                        <small>Media Artikel</small>
                        <h2>Gambar Utama</h2>
                    </div>
                </div>

                <div class="admin-image-box" id="articleImagePreview">
                    @if(!$isCreate && $article->image)
                        <img src="{{ $article->image }}" alt="Preview artikel" onerror="this.src='{{ asset('images/logo-cropped.png') }}'">
                    @else
                        <div class="admin-image-empty">
                            <b>Preview Gambar</b>
                            <small>Gambar artikel akan muncul di sini setelah dipilih.</small>
                        </div>
                    @endif
                </div>

                <label class="admin-form-field full">Upload Gambar
                    <input class="admin-file-input" id="articleImageInput" type="file" name="image_file" accept="image/png,image/jpeg,image/jpg,image/webp" {{ $isCreate ? 'required' : '' }}>
                    <small>Format JPG, PNG, WEBP. Maksimal 4MB. {{ !$isCreate ? 'Kosongkan kalau gambar tidak diganti.' : '' }}</small>
                </label>

                <button class="admin-submit-btn" type="submit">{{ $isCreate ? 'Simpan Artikel' : 'Update Artikel' }}</button>
                <p class="admin-note">Pastikan judul, kategori, gambar, dan isi artikel sudah sesuai sebelum publish.</p>
            </aside>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('articleImageInput');
        const preview = document.getElementById('articleImagePreview');
        if (!input || !preview) return;
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview artikel">';
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endsection
