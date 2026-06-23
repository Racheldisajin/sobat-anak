@extends('layouts.admin')
@section('title','Setting Game — Admin SobatAnak')
@section('page-title','Setting Game')
@section('admin-content')
<section class="admin-game-page">
    <div class="admin-game-wrap">
        <div class="admin-game-head">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="admin-game-back">← Admin Dashboard</a>
                <span class="admin-game-eyebrow">Game Center</span>
                <h1>Setting <span>Mini Games</span></h1>
                <p>Atur nama, status aktif, urutan, poin, cover card, path folder game, dan gambar puzzle dari dashboard admin.</p>
            </div>
            <a href="{{ route('mini-games') }}" class="admin-game-preview">Lihat Halaman Mini Game →</a>
        </div>

        @if(!empty($tableMissing))
            <div class="admin-game-alert">
                <b>Tabel setting game belum ada.</b>
                <span>Import file SQL <code>database/sql/2026_06_18_create_game_settings.sql</code> dulu lewat phpMyAdmin, lalu refresh halaman ini.</span>
            </div>
        @else
            <div class="admin-game-summary">
                <div><b>{{ $games->count() }}</b><span>Total Game</span></div>
                <div><b>{{ $games->where('is_active', true)->count() }}</b><span>Aktif</span></div>
                <div><b>{{ $games->where('slug','puzzle-edukatif')->count() ? 'Siap' : '-' }}</b><span>Puzzle Config</span></div>
            </div>

            <div class="admin-game-grid">
                @foreach($games as $game)
                    @php $settings = $game->settings ?: []; @endphp
                    <form class="admin-game-card" method="POST" action="{{ route('admin.games.update', $game) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="game-card-topline">
                            <div class="game-card-icon">{{ $game->icon }}</div>
                            <div>
                                <span>{{ $game->slug }}</span>
                                <h2>{{ $game->title }}</h2>
                            </div>
                            <label class="game-switch">
                                <input type="checkbox" name="is_active" value="1" @checked($game->is_active)>
                                <i></i>
                            </label>
                        </div>

                        <div class="game-cover-row">
                            <div class="game-cover-box">
                                @if($game->cover_image)
                                    <img src="{{ asset(ltrim($game->cover_image, '/')) }}" alt="{{ $game->title }}">
                                @else
                                    <span>{{ $game->icon }}</span>
                                @endif
                            </div>
                            <div>
                                <label>Cover Card Game</label>
                                <input type="file" name="cover_image_file" accept="image/*">
                                <small>Opsional. Kalau kosong, tetap pakai icon emoji.</small>
                            </div>
                        </div>

                        <div class="game-form-grid">
                            <div class="game-field wide">
                                <label>Nama Game</label>
                                <input type="text" name="title" value="{{ old('title', $game->title) }}" required>
                            </div>
                            <div class="game-field">
                                <label>Icon</label>
                                <input type="text" name="icon" value="{{ old('icon', $game->icon) }}" required>
                            </div>
                            <div class="game-field">
                                <label>Warna</label>
                                <select name="color" required>
                                    @foreach(['teal'=>'Tosca','purple'=>'Ungu','orange'=>'Orange','green'=>'Hijau','blue'=>'Biru','pink'=>'Pink'] as $value => $label)
                                        <option value="{{ $value }}" @selected($game->color === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="game-field wide">
                                <label>Deskripsi Card</label>
                                <textarea name="description" rows="3" required>{{ old('description', $game->description) }}</textarea>
                            </div>
                            <div class="game-field wide">
                                <label>Path Folder / File Game</label>
                                <input type="text" name="game_path" value="{{ old('game_path', $game->game_path) }}" placeholder="games/puzzle/dist/index.html">
                                <small>Path dari folder <b>public</b>. Contoh: <code>games/mewarnai/index.html</code></small>
                            </div>
                            <div class="game-field">
                                <label>Poin per Main</label>
                                <input type="number" name="points_per_play" value="{{ old('points_per_play', $game->points_per_play) }}" min="0" max="999" required>
                            </div>
                            <div class="game-field">
                                <label>Maks Poin</label>
                                <input type="number" name="max_points" value="{{ old('max_points', $game->max_points) }}" min="0" max="9999" required>
                            </div>
                            <div class="game-field">
                                <label>Urutan</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', $game->sort_order) }}" min="1" max="99" required>
                            </div>
                        </div>

                        @if($game->slug === 'puzzle-edukatif')
                            <div class="puzzle-admin-box">
                                <h3>🧩 Gambar Puzzle</h3>
                                <p>Upload gambar baru untuk pilihan puzzle. Setelah disimpan, halaman Puzzle Edukatif akan memakai gambar ini.</p>
                                <div class="puzzle-upload-grid">
                                    @for($i = 1; $i <= 3; $i++)
                                        @php $img = $settings['puzzle_image_'.$i] ?? null; @endphp
                                        <label class="puzzle-upload-card">
                                            <span>Gambar {{ $i }}</span>
                                            @if($img)
                                                <img src="{{ asset(ltrim($img, '/')) }}" alt="Puzzle {{ $i }}">
                                            @else
                                                <em>Default sob{{ $i }}.png</em>
                                            @endif
                                            <input type="file" name="puzzle_image_{{ $i }}" accept="image/*">
                                        </label>
                                    @endfor
                                </div>
                            </div>
                        @endif

                        <button class="admin-game-save" type="submit">Simpan Setting Game</button>
                    </form>
                @endforeach
            </div>
        @endif
    </div>
</section>

<style>
.admin-game-page{background:linear-gradient(135deg,#f7fffd 0%,#fff 48%,#fff5ee 100%);min-height:calc(100vh - 110px);padding:46px 0 70px}.admin-game-wrap{max-width:1180px;margin:auto;padding:0 24px}.admin-game-head{display:flex;justify-content:space-between;gap:22px;align-items:flex-end;margin-bottom:28px}.admin-game-back,.admin-game-preview{display:inline-flex;align-items:center;border:1px solid #d4eeec;border-radius:999px;background:#fff;color:#2a3d3c;text-decoration:none;font-weight:1000;padding:12px 16px;box-shadow:0 12px 28px rgba(42,61,60,.06)}.admin-game-eyebrow{display:block;margin-top:20px;color:#e8756a;font-size:12px;text-transform:uppercase;letter-spacing:.18em;font-weight:1000}.admin-game-head h1{font-family:var(--font-display,inherit);font-size:clamp(2.8rem,6vw,5.2rem);line-height:.95;color:#2a3d3c;font-weight:1000;letter-spacing:-.06em;margin-top:10px}.admin-game-head h1 span{color:#4bbfb0}.admin-game-head p{max-width:700px;color:#6b8a88;font-weight:900;line-height:1.7;margin-top:12px}.admin-game-alert{background:#fff6ec;border:1px solid #f6c69c;border-radius:26px;padding:22px;color:#2a3d3c;font-weight:900}.admin-game-alert b{display:block;font-size:1.2rem}.admin-game-alert span{display:block;color:#6b8a88;margin-top:6px}.admin-game-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:22px}.admin-game-summary>div{background:#fff;border:1px solid #d4eeec;border-radius:24px;padding:20px;box-shadow:0 14px 36px rgba(42,61,60,.05)}.admin-game-summary b{display:block;font-size:2.1rem;font-weight:1000;color:#4bbfb0}.admin-game-summary span{display:block;color:#6b8a88;font-weight:1000}.admin-game-grid{display:grid;gap:20px}.admin-game-card{background:#fff;border:1px solid #d4eeec;border-radius:34px;padding:24px;box-shadow:0 24px 70px rgba(42,61,60,.08)}.game-card-topline{display:grid;grid-template-columns:64px 1fr auto;gap:14px;align-items:center;margin-bottom:18px}.game-card-icon{width:64px;height:64px;border-radius:22px;background:#eefffb;border:1px solid #d4eeec;display:grid;place-items:center;font-size:2.3rem}.game-card-topline span{color:#e8756a;text-transform:uppercase;letter-spacing:.14em;font-size:11px;font-weight:1000}.game-card-topline h2{font-size:1.8rem;font-weight:1000;color:#2a3d3c;letter-spacing:-.03em}.game-switch input{display:none}.game-switch i{display:block;width:62px;height:34px;border-radius:999px;background:#f3b4ae;position:relative;border:1px solid #ef8e84}.game-switch i:before{content:"";position:absolute;width:26px;height:26px;border-radius:999px;background:#fff;left:4px;top:3px;box-shadow:0 4px 12px rgba(42,61,60,.16);transition:.2s}.game-switch input:checked+i{background:#4bbfb0;border-color:#28a99b}.game-switch input:checked+i:before{left:30px}.game-cover-row{display:grid;grid-template-columns:92px 1fr;gap:16px;align-items:center;background:#f8fefd;border:1px dashed #bfece6;border-radius:24px;padding:14px;margin-bottom:18px}.game-cover-box{width:92px;height:92px;border-radius:24px;background:#fff;border:1px solid #d4eeec;display:grid;place-items:center;font-size:2.4rem;overflow:hidden}.game-cover-box img{width:100%;height:100%;object-fit:cover}.game-cover-row label,.game-field label{display:block;color:#2a3d3c;font-weight:1000;margin-bottom:7px}.game-cover-row small,.game-field small{display:block;color:#6b8a88;font-weight:800;margin-top:6px}.game-form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.game-field.wide{grid-column:span 3}.game-field input,.game-field select,.game-field textarea,.game-cover-row input{width:100%;border:1px solid #d4eeec;border-radius:18px;padding:13px 14px;background:#fff;color:#2a3d3c;font-weight:850;outline:none}.game-field textarea{resize:vertical}.game-field input:focus,.game-field select:focus,.game-field textarea:focus{border-color:#4bbfb0;box-shadow:0 0 0 4px rgba(75,191,176,.12)}.puzzle-admin-box{margin-top:18px;background:linear-gradient(135deg,#fff8e8,#fff);border:1px solid #f6dba2;border-radius:26px;padding:18px}.puzzle-admin-box h3{font-weight:1000;color:#2a3d3c;font-size:1.25rem}.puzzle-admin-box p{color:#6b8a88;font-weight:850;margin:6px 0 14px}.puzzle-upload-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.puzzle-upload-card{background:#fff;border:1px solid #efd8a4;border-radius:22px;padding:12px;display:grid;gap:10px}.puzzle-upload-card span{font-weight:1000;color:#2a3d3c}.puzzle-upload-card img{width:100%;height:130px;object-fit:cover;border-radius:16px;border:1px solid #f1dfb7}.puzzle-upload-card em{height:130px;border-radius:16px;background:#fff9e9;border:1px dashed #e9cd8c;display:grid;place-items:center;color:#a07922;font-style:normal;font-weight:900}.admin-game-save{margin-top:18px;border:0;background:#e8756a;color:#fff;border-radius:999px;padding:15px 22px;font-weight:1000;box-shadow:0 16px 34px rgba(232,117,106,.25);cursor:pointer}.admin-game-save:hover{transform:translateY(-1px)}@media(max-width:900px){.admin-game-head{display:block}.admin-game-preview{margin-top:16px}.admin-game-summary,.game-form-grid,.puzzle-upload-grid{grid-template-columns:1fr}.game-field.wide{grid-column:auto}.game-card-topline{grid-template-columns:56px 1fr}.game-switch{grid-column:1/-1}.game-cover-row{grid-template-columns:1fr}.game-cover-box{width:100%;height:150px}}
</style>
@endsection
