@extends('layouts.app')
@section('title','Kalkulator Tumbuh Kembang Anak — SobatAnak')
@section('content')

<section class="bg-gradient-to-br from-[#EEFFFB] via-white to-[#FFF8EC] py-14 md:py-20 overflow-hidden relative">
    <div class="max-w-4xl mx-auto px-6 md:px-12 text-center relative z-10">
        <span class="text-coral font-black uppercase tracking-widest text-xs bg-white/60 px-4 py-2 rounded-full border border-white">Growth Tracker</span>
        <h1 class="font-display hero-title mt-6 text-4xl md:text-5xl">Kalkulator <span class="text-teal">Tumbuh Kembang</span> Anak</h1>
        <p class="text-[#6B8A88] font-bold mt-4 text-lg">Pantau apakah tinggi dan berat badan si kecil sudah ideal sesuai umurnya dengan kalkulator cerdas ini.</p>
    </div>
    
    <!-- Decorative background elements -->
    <div class="absolute top-10 left-10 text-6xl opacity-30 animate-pulse">📏</div>
    <div class="absolute bottom-10 right-10 text-6xl opacity-30 animate-pulse" style="animation-delay:1s">🧸</div>
</section>

<section class="max-w-3xl mx-auto px-6 md:px-12 py-10">
    <div class="calculator-card">
        <form id="growthForm" class="grid gap-6">
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label>Umur Anak (Bulan)</label>
                    <input type="number" id="calcAge" placeholder="Misal: 24 (untuk 2 tahun)" required min="1" max="144" class="calc-input">
                </div>
                
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select id="calcGender" required class="calc-input">
                        <option value="L">Laki-Laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label>Berat Badan (kg)</label>
                    <input type="number" id="calcWeight" placeholder="Misal: 12.5" step="0.1" required min="1" max="100" class="calc-input">
                </div>
                
                <div class="form-group">
                    <label>Tinggi Badan (cm)</label>
                    <input type="number" id="calcHeight" placeholder="Misal: 85" step="0.1" required min="30" max="200" class="calc-input">
                </div>
            </div>

            <button type="submit" class="btn-pill btn-coral w-full justify-center text-lg mt-4 shadow-xl">Hitung Sekarang 🚀</button>
        </form>

        <div id="resultBox" class="result-box hidden">
            <h3 class="font-display text-2xl text-center mb-4">Hasil Kalkulasi BMI</h3>
            
            <div class="score-circle mx-auto">
                <span id="bmiScore">0.0</span>
                <small>BMI</small>
            </div>
            
            <div class="status-alert mt-6" id="statusAlert">
                <h4 id="statusTitle" class="font-black text-xl mb-1">Status</h4>
                <p id="statusDesc" class="font-bold">Deskripsi status</p>
            </div>
            
            <div class="mt-6 text-center text-[#6B8A88] text-sm">
                <b>Catatan:</b> Perhitungan ini menggunakan rumus Body Mass Index (BMI) standar yang disesuaikan secara umum. Untuk diagnosis medis yang pasti, selalu konsultasikan dengan dokter anak atau posyandu terdekat.
            </div>
        </div>
    </div>
</section>

<style>
.calculator-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.4);
    box-shadow: 0 30px 60px rgba(42, 61, 60, 0.08), inset 0 1px 0 rgba(255,255,255,0.6);
    border-radius: 2rem;
    padding: 2.5rem;
    position: relative;
    top: -40px;
}

.form-group label {
    display: block;
    font-weight: 900;
    color: #2A3D3C;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.calc-input {
    width: 100%;
    border: 2px solid #E2E8F0;
    border-radius: 1rem;
    padding: 0.9rem 1.2rem;
    font-size: 1rem;
    font-weight: 700;
    color: #2A3D3C;
    background: #F8FAFC;
    transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.calc-input:focus {
    outline: none;
    border-color: var(--teal);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(75, 191, 176, 0.15);
    transform: translateY(-2px);
}

.result-box {
    margin-top: 2.5rem;
    border-top: 2px dashed #E2E8F0;
    padding-top: 2.5rem;
    animation: slideUpFade 0.5s ease forwards;
}

.score-circle {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--teal), #2E8F84);
    color: white;
    box-shadow: 0 15px 35px rgba(75, 191, 176, 0.3);
}

.score-circle span {
    font-size: 2.8rem;
    font-weight: 1000;
    line-height: 1;
}

.score-circle small {
    font-size: 0.9rem;
    font-weight: 800;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-top: 4px;
}

.status-alert {
    border-radius: 1.25rem;
    padding: 1.25rem 1.5rem;
    text-align: center;
    transition: 0.3s;
}

.status-kurang {
    background: #FFF3CD;
    color: #856404;
    border: 2px solid #FFEEBA;
}

.status-ideal {
    background: #D4EDDA;
    color: #155724;
    border: 2px solid #C3E6CB;
}

.status-berlebih {
    background: #F8D7DA;
    color: #721C24;
    border: 2px solid #F5C6CB;
}

@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media(max-width: 767px) {
    .calculator-card {
        padding: 1.5rem;
    }
}
</style>

<script>
document.getElementById('growthForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Ambil nilai
    const age = parseFloat(document.getElementById('calcAge').value);
    const weight = parseFloat(document.getElementById('calcWeight').value);
    const height = parseFloat(document.getElementById('calcHeight').value) / 100; // cm to m
    
    if(!weight || !height || height <= 0) return;
    
    // Hitung BMI
    const bmi = weight / (height * height);
    const bmiFixed = bmi.toFixed(1);
    
    // Logika Sederhana BMI Anak (Ini disederhanakan dari grafik WHO untuk demo)
    // Range BMI anak sedikit berbeda dari dewasa
    let statusClass = '';
    let statusText = '';
    let statusDesc = '';
    
    if(bmi < 14) {
        statusClass = 'status-kurang';
        statusText = '⚠️ Berat Badan Kurang';
        statusDesc = 'Si kecil mungkin membutuhkan asupan nutrisi yang lebih banyak. Konsultasikan dengan dokter anak untuk pola makan terbaik.';
    } else if(bmi >= 14 && bmi <= 18) {
        statusClass = 'status-ideal';
        statusText = '✨ Ideal & Sehat!';
        statusDesc = 'Bagus sekali! Berat badan dan tinggi badan anak berada pada rentang ideal. Pertahankan asupan nutrisi dan pola makan sehatnya.';
    } else if(bmi > 18 && bmi <= 21) {
        statusClass = 'status-kurang'; // yellow warning
        statusText = '⚠️ Berisiko Berlebih (Overweight)';
        statusDesc = 'Si kecil memiliki kecenderungan berat badan berlebih. Ajak anak untuk lebih banyak aktivitas fisik dan perhatikan asupan gulanya.';
    } else {
        statusClass = 'status-berlebih'; // red warning
        statusText = '🚨 Obesitas';
        statusDesc = 'Kondisi ini memerlukan perhatian ekstra. Sangat disarankan berkonsultasi dengan ahli gizi atau dokter anak untuk program kesehatan yang tepat.';
    }
    
    // Tampilkan hasil
    document.getElementById('bmiScore').textContent = bmiFixed;
    
    const alertBox = document.getElementById('statusAlert');
    alertBox.className = 'status-alert mt-6 ' + statusClass;
    
    document.getElementById('statusTitle').textContent = statusText;
    document.getElementById('statusDesc').textContent = statusDesc;
    
    const resultBox = document.getElementById('resultBox');
    resultBox.classList.remove('hidden');
    
    // Reset animasi agar bisa di trigger berulang kali
    resultBox.style.animation = 'none';
    resultBox.offsetHeight; /* trigger reflow */
    resultBox.style.animation = null;
});
</script>

@endsection
