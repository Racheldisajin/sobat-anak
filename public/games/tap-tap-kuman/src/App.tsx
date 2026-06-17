import React, { useState, useEffect, useCallback, useRef } from "react";
import { 
  Timer, 
  Trophy, 
  Play, 
  Volume2, 
  VolumeX, 
  Sparkles, 
  ShieldCheck, 
  Droplets, 
  Heart, 
  Star, 
  ChevronRight, 
  Store, 
  Info, 
  RotateCcw, 
  X, 
  CheckCircle,
  ThumbsUp,
  Award
} from "lucide-react";

// Tipe data untuk Kuman (Germ)
interface KumanType {
  id: number;
  x: number; // posisi X dalam piksel (0 - 800)
  y: number; // posisi Y dalam piksel (0 - 450)
  size: number; // ukuran kuman (70 - 100px)
  variant: 'goofy' | 'spiky' | 'octopus' | 'crowned';
  colorIndex: number;
}

// Tipe data untuk animasi letusan partikel (burst)
interface BurstType {
  id: number;
  x: number;
  y: number;
  color: string;
}

// Tipe data untuk angka "+1" melayang
interface FloatingTextType {
  id: number;
  x: number;
  y: number;
}

// Data Pilihan Kuman (Warna-warna pastel ceria)
const KUMAN_COLORS = [
  "#FF8A65", // Coral Red
  "#4FC3F7", // Sky Blue
  "#81C784", // Mint Green
  "#FFD54F", // Cheerful Yellow
];

export default function App() {
  // Game States
  const [gameState, setGameState] = useState<'START' | 'PLAYING' | 'FINISHED'>('START');
  const [score, setScore] = useState<number>(0);
  const [highScore, setHighScore] = useState<number>(() => {
    const saved = localStorage.getItem("taptap_high_score");
    return saved ? parseInt(saved, 10) : 0;
  });
  const [timeLeft, setTimeLeft] = useState<number>(30);
  const [kuman, setKuman] = useState<KumanType | null>(null);
  const [soundEnabled, setSoundEnabled] = useState<boolean>(true);
  
  // Efek Animasi
  const [bursts, setBursts] = useState<BurstType[]>([]);
  const [floatingTexts, setFloatingTexts] = useState<FloatingTextType[]>([]);
  
  // Modal / Drawer State untuk Produk dan Tips Orang Tua
  const [isProductModalOpen, setIsProductModalOpen] = useState<boolean>(false);
  const [selectedProductVariant, setSelectedProductVariant] = useState<'Strawberry' | 'Chamomile' | 'Apple'>('Strawberry');
  const [productQuantity, setProductQuantity] = useState<number>(1);
  const [isCheckoutSuccess, setIsCheckoutSuccess] = useState<boolean>(false);
  
  // Tab Aktif untuk Edukasi Parenting
  const [activeTab, setActiveTab] = useState<number>(0);

  // Ref audio context untuk menjamin inisialisasi hanya setelah interaksi user
  const audioCtxRef = useRef<AudioContext | null>(null);
  const bgmRef = useRef<HTMLAudioElement | null>(null);

  // Ref kotak game untuk hitung posisi kuman responsif
  const gameAreaRef = useRef<HTMLElement | null>(null);

  useEffect(() => {
  const bgm = new Audio('/audio/backsound-tap-kuman.mp3');
  bgm.loop = true;
  bgm.volume = 0.25;
  bgm.muted = !soundEnabled;

  bgmRef.current = bgm;

  return () => {
    bgm.pause();
    bgm.currentTime = 0;
  };
}, []);

  const playBacksound = (forcePlay = false) => {
    if (!bgmRef.current) return;
    if (!forcePlay && !soundEnabled) return;

    bgmRef.current.volume = 0.25;
    bgmRef.current.muted = false;

    bgmRef.current.play().catch(() => {
      // Browser bisa block kalau belum ada klik user
    });
  };

const stopBacksound = () => {
  if (!bgmRef.current) return;

  bgmRef.current.pause();
};

  // Fungsi memutar efek suara Pop menggunakan Web Audio API
  const playPopSound = useCallback(() => {
    if (!soundEnabled) return;
    try {
      if (!audioCtxRef.current) {
        audioCtxRef.current = new (window.AudioContext || (window as any).webkitAudioContext)();
      }
      const ctx = audioCtxRef.current;
      
      // Jika disuspensi (kebijakan browser), resume
      if (ctx.state === "suspended") {
        ctx.resume();
      }

      // Sintesis frekuensi instan menyapu ke atas (Bubble Pop)
      const osc = ctx.createOscillator();
      const gainNode = ctx.createGain();
      
      osc.type = "sine";
      
      // Sapuan frekuensi naik memberikan tekstur gelembung pop yang imut
      osc.frequency.setValueAtTime(120, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
      
      gainNode.gain.setValueAtTime(0.25, ctx.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.13);
      
      osc.connect(gainNode);
      gainNode.connect(ctx.destination);
      
      osc.start();
      osc.stop(ctx.currentTime + 0.14);
    } catch (e) {
      console.warn("Audio API didukung tetapi gagal terinisialisasi:", e);
    }
  }, [soundEnabled]);

  // Fungsi memutar suara start/win game
  const playWinSound = useCallback(() => {
    if (!soundEnabled) return;
    try {
      if (!audioCtxRef.current) {
        audioCtxRef.current = new (window.AudioContext || (window as any).webkitAudioContext)();
      }
      const ctx = audioCtxRef.current;
      if (ctx.state === "suspended") ctx.resume();

      const playTone = (freq: number, start: number, duration: number) => {
        const osc = ctx.createOscillator();
        const gainNode = ctx.createGain();
        osc.type = "triangle";
        osc.frequency.setValueAtTime(freq, ctx.currentTime + start);
        gainNode.gain.setValueAtTime(0.15, ctx.currentTime + start);
        gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + duration);
        osc.connect(gainNode);
        gainNode.connect(ctx.destination);
        osc.start(ctx.currentTime + start);
        osc.stop(ctx.currentTime + start + duration);
      };

      // Nada ceria beruntun
      playTone(523.25, 0, 0.15); // C5
      playTone(659.25, 0.12, 0.15); // E5
      playTone(783.99, 0.24, 0.15); // G5
      playTone(1046.50, 0.36, 0.4); // C6 (panjang)
    } catch (e) {
      console.warn(e);
    }
  }, [soundEnabled]);

  // Fungsi memutar suara game over
  const playGameOverSound = useCallback(() => {
    if (!soundEnabled) return;
    try {
      if (!audioCtxRef.current) {
        audioCtxRef.current = new (window.AudioContext || (window as any).webkitAudioContext)();
      }
      const ctx = audioCtxRef.current;
      if (ctx.state === "suspended") ctx.resume();

      const playTone = (freq: number, start: number, duration: number) => {
        const osc = ctx.createOscillator();
        const gainNode = ctx.createGain();
        osc.type = "sawtooth";
        osc.frequency.setValueAtTime(freq, ctx.currentTime + start);
        gainNode.gain.setValueAtTime(0.12, ctx.currentTime + start);
        gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + duration);
        osc.connect(gainNode);
        gainNode.connect(ctx.destination);
        osc.start(ctx.currentTime + start);
        osc.stop(ctx.currentTime + start + duration);
      };

      // Nada sedih menurun
      playTone(392.00, 0, 0.2); // G4
      playTone(349.23, 0.18, 0.2); // F4
      playTone(311.13, 0.36, 0.45); // Eb4
    } catch (e) {
      console.warn(e);
    }
  }, [soundEnabled]);

  // Spawn Kuman acak di area fix 
const spawnKuman = useCallback(() => {
  const container = gameAreaRef.current;

  const containerWidth = container?.clientWidth || 800;
  const containerHeight = container?.clientHeight || 450;

  // Skala ukuran kuman mengikuti lebar kotak game
  const scale = containerWidth / 800;

  // Ukuran kuman tetap enak diklik di HP, tapi tidak terlalu besar
  const minSize = Math.max(52, Math.floor(75 * scale));
  const maxSize = Math.max(68, Math.floor(100 * scale));
  const size = Math.floor(Math.random() * (maxSize - minSize + 1)) + minSize;

  const margin = Math.max(12, Math.floor(20 * scale));

  const maxX = Math.max(margin, containerWidth - size - margin);
  const maxY = Math.max(margin, containerHeight - size - margin);

  const x = Math.floor(Math.random() * (maxX - margin + 1)) + margin;
  const y = Math.floor(Math.random() * (maxY - margin + 1)) + margin;

  const variants: ('goofy' | 'spiky' | 'octopus' | 'crowned')[] = [
    'goofy',
    'spiky',
    'octopus',
    'crowned'
  ];

  const rVariant = variants[Math.floor(Math.random() * variants.length)];
  const rColorIndex = Math.floor(Math.random() * KUMAN_COLORS.length);

  setKuman({
    id: Date.now() + Math.random(),
    x,
    y,
    size,
    variant: rVariant,
    colorIndex: rColorIndex,
  });
}, []);

  // Mulai Game Baru
  const handleStartGame = () => {
    // Inisialisasi AudioContext jika belum diaktifkan
    if (!audioCtxRef.current) {
      audioCtxRef.current = new (window.AudioContext || (window as any).webkitAudioContext)();
    }

    if (bgmRef.current) {
      bgmRef.current.currentTime = 0;
    }

    if (soundEnabled) {
      playBacksound(true);
    }
    
    setScore(0);
    setTimeLeft(30);
    setGameState('PLAYING');
    spawnKuman();
    playWinSound();
  };

  // Efek Timer Hitung Mundur 30 Detik
  useEffect(() => {
    if (gameState !== 'PLAYING') return;

    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          setGameState('FINISHED');
          playGameOverSound();
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [gameState, playGameOverSound]);

  // Sinkronisasi High Score saat game selesai
  useEffect(() => {
    if (gameState === 'FINISHED') {
      if (score > highScore) {
        setHighScore(score);
        localStorage.setItem("taptap_high_score", score.toString());
      }
    }
  }, [gameState, score, highScore]);

  // Kuman berpindah secara berkala setiap 1.2 detik jika didiamkan
  useEffect(() => {
    if (gameState !== 'PLAYING' || !kuman) return;

    const autoRelocate = setTimeout(() => {
      spawnKuman();
    }, 1200);

    return () => clearTimeout(autoRelocate);
  }, [kuman, gameState, spawnKuman]);

  // Handler klik kuman yang berhasil diketok
const handleKumanClick = (e: React.MouseEvent) => {
  if (!kuman) return;

  e.stopPropagation();

  // Putar suara & tambahkan skor
  playPopSound();
  setScore((prev) => prev + 1);

  // Koordinat pusat kuman untuk titik burst
  const centerX = kuman.x + kuman.size / 2;
  const centerY = kuman.y + kuman.size / 2;

  // Catat burst
  const burstId = Date.now() + Math.random();

  setBursts((prev) => [
    ...prev,
    {
      id: burstId,
      x: centerX,
      y: centerY,
      color: KUMAN_COLORS[kuman.colorIndex],
    },
  ]);

  // Catat angka "+1" mengapung
  const textId = Date.now() + Math.random();

  setFloatingTexts((prev) => [
    ...prev,
    {
      id: textId,
      x: centerX,
      y: Math.max(10, kuman.y - 10),
    },
  ]);

  // Hapus burst & teks setelah animasi selesai
  setTimeout(() => {
    setBursts((prev) => prev.filter((b) => b.id !== burstId));
  }, 600);

  setTimeout(() => {
    setFloatingTexts((prev) => prev.filter((t) => t.id !== textId));
  }, 600);

  // Langsung munculkan kuman baru di tempat lain
  spawnKuman();
};

  // List Tips Parenting terstruktur
  const PARENTING_TIPS = [
    {
      title: "Pentingnya Pembiasaan Dini",
      icon: ShieldCheck,
      color: "border-[#81C784] bg-emerald-50 text-[#81C784]",
      tagline: "Mencegah penyakit dengan cara paling murah, praktis, dan efektif.",
      content: "Mengajarkan anak mencuci tangan dengan sabun sejak balita merupakan kunci penting dalam membentuk kebiasaan bersih seumur hidup. Menurut World Health Organization (WHO), cuci tangan yang rutin dapat melindung anak dari penyakit diare hingga infeksi saluran pernapasan akut yang sangat sering menular di area sekolah atau taman bermain."
    },
    {
      title: "Aturan Seru 6 Langkah WHO",
      icon: Droplets,
      color: "border-[#4FC3F7] bg-sky-50 text-[#4FC3F7]",
      tagline: "Basuh seluruh celah kuman agar tangan steril seutuhnya.",
      content: "Ajak anak menghafal gerakan: (1) Basahi tangan dan gunakan sabun, gosok telapak tangan. (2) Gosok punggung tangan bergantian. (3) Gosok sela-sela jari tangan. (4) Kunci jari-jari bagian dalam. (5) Putar ibu jari melingkar. (6) Usap ujung kuku di telapak tangan. Seluruh langkah ini sebaiknya dilakukan selama minimal 20 detik—ideal sambil menyanyikan lagu riang!"
    },
    {
      title: "Tips Kreatif Pembingkaian",
      icon: Sparkles,
      color: "border-[#FFD54F] bg-amber-50 text-[#FFD54F]",
      tagline: "Ubah keharusan menjadi permainan interaktif bagi balita.",
      content: "Anak-anak meniru apa yang mereka lihat! Jadikan contoh setiap sebelum makan. Gunakan sabun yang memiliki busa melimpah dengan kemasan lucu yang disukai anak, seperti Sabun Antiseptik Tentang Anak. Anda juga bisa berkreasi dengan membuat bagan poin bintang (reward chart) di kamar mandi setiap kali si kecil berhasil mencuci tangannya secara mandiri."
    },
    {
      title: "Momentum Wajib Cuci Tangan",
      icon: Heart,
      color: "border-[#FF8A65] bg-rose-50 text-[#FF8A65]",
      tagline: "4 Waktu Utama untuk menjaga sistem imun keluarga tetap terjaga.",
      content: "Orang tua wajib mengingatkan anak mencuci tangan minimal pada momen-momen emas ini: Sesaat sebelum dan setelah makan makanan utama, setelah menggunakan toilet/pispot, sesampainya di rumah setelah bermain di luar ruangan, dan sesaat sesudah memegang hewan peliharaan atau bersin/batuk."
    }
  ];

  return (
    <div id="app_root" className="min-h-screen bg-orange-50 text-gray-800 font-sans px-4 py-4 md:p-6 flex flex-col items-center justify-start selection:bg-orange-200">
      
      {/* HEADER DEKORATIF UTAMA */}
      <header className="w-full max-w-4xl flex items-center justify-between gap-3 mb-4 mt-2">
        <div className="flex items-center gap-3">
          {/* Logo Sobat Anak + Tagline */}
          <div className="flex items-center gap-3">
            <img
              src="/logo sobat anak.png"
              alt="Logo Sobat Anak"
              className="w-24 sm:w-28 md:w-32 h-auto object-contain"
            />

            <div
              className="h-8 rounded-full"
              style={{
                width: "3px",
                backgroundColor: "#FF8A65"
              }}
            />

            <span
              className="hidden sm:inline text-xs md:text-sm font-black whitespace-nowrap"
              style={{ color: "#1e2939" }}
            >
              Mom & Baby Care
            </span>
          </div>
        </div>

        {/* Audio Controller & Help Bubbles */}
        <div className="flex items-center gap-2">
          <button 
            id="toggle_sound_btn"
            onClick={() => {
              setSoundEnabled((prev) => {
                const newVal = !prev;

                if (bgmRef.current) {
                  bgmRef.current.muted = !newVal;

                  if (!newVal) {
                    bgmRef.current.pause();
                  } else {
                    playBacksound(true);
                  }
                }

                return newVal;
              });
            }}
            className={`p-3 rounded-2xl border-2 border-white shadow-sm transition-all duration-300 hover:scale-105 active:scale-95 flex items-center justify-center cursor-pointer ${
              soundEnabled ? 'bg-orange-100 text-orange-700' : 'bg-gray-200 text-gray-500'
            }`}
            title={soundEnabled ? "Matikan Suara" : "Aktifkan Suara"}
          >
            {soundEnabled ? (
              <Volume2 size={20} className="animate-pulse" />
            ) : (
              <VolumeX size={20} />
            )}
          </button>
        </div>
      </header>

      {/* 1. BAGIAN ATAS (Di luar Kotak Game Utama) */}
      <section className="w-full max-w-4xl mt-1 mb-4 md:mb-6 px-1 grid grid-cols-3 gap-2.5 md:gap-5 select-none">
        
        {/* Panel Waktu Raksasa */}
        <div 
          id="time_panel" 
          className={`bg-white border-2 md:border-3 rounded-2xl md:rounded-3xl p-2 md:p-4 flex items-center justify-center md:justify-start shadow-lg relative h-16 md:h-28 select-none transition-all duration-200 ${
            gameState === 'PLAYING' && timeLeft <= 5 
              ? 'border-red-500 animate-shaky-warning bg-red-50/25' 
              : 'border-orange-500 hover:scale-[1.01]'
          }`}
        >
          <div className="flex items-center space-x-1.5 md:space-x-3.5">
            <div className={`w-8 h-8 md:w-14 md:h-14 rounded-xl md:rounded-2xl flex items-center justify-center border shrink-0 transition-colors ${
              gameState === 'PLAYING' && timeLeft <= 5
                ? 'bg-red-100 border-red-300'
                : 'bg-orange-100 border-orange-200'
            }`}>
              <Timer 
                className={`w-4 h-4 md:w-7 md:h-7 transition-colors ${
                  gameState === 'PLAYING' && timeLeft <= 5 
                    ? 'text-red-500 animate-pulse' 
                    : 'text-orange-500'
                }`} 
                strokeWidth={2.5} 
              />
            </div>

            <div>
              <p className={`text-[8px] md:text-[11px] font-extrabold uppercase tracking-wider md:tracking-widest leading-none transition-colors ${
                gameState === 'PLAYING' && timeLeft <= 5 ? 'text-red-500' : 'text-orange-500'
              }`}>
                Waktu
              </p>

              <div className="flex items-baseline mt-0.5 md:mt-1">
                <span className={`text-base md:text-3xl font-black leading-none transition-colors ${
                  gameState === 'PLAYING' && timeLeft <= 5 ? 'text-red-600' : 'text-orange-600'
                }`}>
                  {timeLeft}
                </span>

                <span className={`text-[9px] md:text-[13px] font-extrabold ml-0.5 md:ml-1.5 leading-none transition-colors ${
                  gameState === 'PLAYING' && timeLeft <= 5 ? 'text-red-600' : 'text-orange-600'
                }`}>
                  detik
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Panel Judul Game */}
        <div className="bg-blue-500 rounded-2xl md:rounded-3xl p-2 md:p-4 flex flex-col justify-center items-center h-16 md:h-28 shadow-lg select-none transition-transform duration-200 hover:scale-[1.01] border-2 md:border-4 border-white">
          <p className="text-[8px] md:text-[11px] font-extrabold text-blue-100 uppercase tracking-wider md:tracking-widest text-center opacity-90 leading-none">
            SOBAT ANAK
          </p>

          <h3 className="text-xs md:text-2xl font-black text-white uppercase text-center mt-0.5 md:mt-1 tracking-tight leading-none">
            MINI GAME
          </h3>
        </div>

        {/* Panel Skor Raksasa */}
        <div 
          id="score_panel" 
          className="bg-white border-2 md:border-3 border-blue-500 rounded-2xl md:rounded-3xl p-2 md:p-4 flex items-center justify-between shadow-lg relative h-16 md:h-28 select-none transition-transform duration-200 hover:scale-[1.01]"
        >
          <div className="flex items-center space-x-1.5 md:space-x-3.5">
            <div className="w-8 h-8 md:w-14 md:h-14 bg-blue-100 rounded-xl md:rounded-2xl flex items-center justify-center border border-blue-200 shrink-0">
              <Trophy 
                className={`w-4 h-4 md:w-7 md:h-7 text-blue-500 ${score > 0 ? 'animate-bounce' : ''}`} 
                strokeWidth={2.5} 
              />
            </div>

            <div>
              <p className="text-[8px] md:text-[11px] font-extrabold text-blue-500 uppercase tracking-wider md:tracking-widest leading-none">
                Skor
              </p>

              <span 
                id="current_score_lbl"
                className="text-base md:text-3xl font-black text-blue-600 leading-none mt-0.5 md:mt-1 block"
              >
                {score}
              </span>
            </div>
          </div>

          <div className="absolute bottom-1 right-1.5 md:bottom-2.5 md:right-4 leading-none">
            <p className="text-[7px] md:text-[10px] font-black text-blue-500 uppercase tracking-tight md:tracking-wider leading-none">
              <span className="hidden sm:inline">Rekor: </span>{highScore}
            </p>
          </div>
        </div>
      </section>

      {/* 2. BAGIAN TENGAH / UTAMA (Kotak Game Utama - 800x450px) */}
      <main
        ref={gameAreaRef}
        id="game_container"
        className="relative bg-white rounded-3xl border-4 md:border-8 border-white shadow-2xl overflow-hidden mb-6 flex flex-col items-center justify-center select-none w-full max-w-4xl min-h-90 sm:min-h-105 md:min-h-0 md:aspect-video"
        style={{
          boxShadow: "0 25px 50px -12px rgba(0,0,0,0.15), inset 0 0 40px rgba(59, 130, 246, 0.08)"
        }}
      >
              {/* Dekorasi Awan Belakang untuk memberikan space kedalaman */}
        <div className="absolute inset-0 pointer-events-none opacity-40 z-0">
          {/* Back light blue overlay */}
          <div className="absolute inset-0 bg-blue-50 opacity-30" />
          {/* Awan-awan mengapung */}
          <div className="absolute top-8 left-12 w-28 h-10 bg-white rounded-full filter blur-[1px] animate-float opacity-80" />
          <div className="absolute top-24 right-16 w-36 h-12 bg-white rounded-full filter blur-[1.5px] animate-float opacity-70" style={{ animationDelay: '2s' }} />
          <div className="absolute bottom-16 left-28 w-32 h-10 bg-white rounded-full filter blur-[1px] animate-float opacity-60" style={{ animationDelay: '4s' }} />
          {/* Pola grid ubin tipis khas kamar mandi bersih */}
          <div
  className="absolute inset-0"
  style={{
    backgroundImage:
      "linear-gradient(rgba(59,130,246,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(59,130,246,0.04) 1px, transparent 1px)",
    backgroundSize: "40px 40px"
  }}
/>
        </div>

        {/* STATE 1: AWAL / START LAYOUT */}
        {gameState === 'START' && (
          <div className="relative z-10 w-full h-full px-4 py-5 sm:p-6 md:p-8 text-center flex flex-col items-center justify-center animate-pop-in">
          
          {/* Animasi Logo Kuman-kuman yang ramah */}
          <div className="flex gap-2.5 sm:gap-4 justify-center mb-4 sm:mb-6">
            <div className="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 bg-orange-100 rounded-full flex items-center justify-center animate-bounce-gentle border-4 border-orange-300 shadow-md">
              <svg
                className="w-8 h-8 sm:w-11 sm:h-11 md:w-12 md:h-12 animate-spin text-orange-500"
                style={{ animationDuration: "8s" }}
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path d="M12 2a1 1 0 0 1 .993.883L13 3v1.071c3.167.433 5.567 2.833 6 6h1.071a1 1 0 0 1 .117 1.993l-.117.007H19c-.433 3.167-2.833 5.567-6 6v1.071a1 1 0 0 1-1.993.117l-.007-.117V17c-3.167-.433-5.567-2.833-6-6H4.071a1 1 0 0 1-.117-1.993l.117-.007H5c.433-3.167 2.833-5.567 6-6V3a1 1 0 0 1 1-1zm0 5a5 5 0 0 0-4.992 4.783L7 12a5 5 0 1 0 5-5zm0 3a2 2 0 1 1-1.995 2.15L10 12a2 2 0 0 1 2-2z" />
              </svg>
            </div>

            <div
              className="w-14 h-14 sm:w-18 sm:h-18 md:w-20 md:h-20 bg-emerald-100 rounded-full flex items-center justify-center animate-bounce-gentle border-4 border-emerald-300 shadow-md"
              style={{ animationDelay: "0.4s" }}
            >
              <svg
                className="w-9 h-9 sm:w-12 sm:h-12 md:w-13 md:h-13 text-emerald-500"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path d="M12 3a9 9 0 0 1 9 9c0 1.25-.25 2.44-.71 3.53-.4.94-.96 1.78-1.64 2.47-.69.68-1.53 1.24-2.47 1.64A8.99 8.99 0 0 1 12 21a9 9 0 0 1-9-9 9 9 0 0 1 9-9zm-2.5 5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm5 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.5 5.5A2.5 2.5 0 0 0 9.51 15h4.98c-.1-.7-.66-1.5-2-1.5z" />
              </svg>
            </div>

            <div
              className="w-14 h-14 sm:w-18 sm:h-18 md:w-20 md:h-20 bg-blue-100 rounded-full flex items-center justify-center animate-bounce-gentle border-4 border-blue-300 shadow-md"
              style={{ animationDelay: "0.8s" }}
            >
              <svg
                className="w-8 h-8 sm:w-11 sm:h-11 md:w-12 md:h-12 text-blue-500"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <circle cx="12" cy="12" r="8" />
                <circle cx="9.5" cy="10" r="1.5" fill="#fff" />
                <circle cx="14.5" cy="10" r="1.5" fill="#fff" />
                <ellipse cx="12" cy="14" rx="2" ry="1.5" fill="#fff" />
              </svg>
            </div>
          </div>

          <h1 className="text-2xl sm:text-3xl md:text-5xl font-black text-slate-800 tracking-tight leading-tight mb-3 sm:mb-4 uppercase">
            TAP-TAP KUMAN
          </h1>

          <p className="text-slate-600 font-bold text-xs sm:text-sm md:text-base mb-5 sm:mb-6 md:mb-8 max-w-70 sm:max-w-md leading-relaxed">
            Bantu hilangkan kuman jahat dengan mengetuk mereka secepat mungkin!
          </p>

          <button
            id="start_game_btn"
            onClick={handleStartGame}
            className="bg-green-500 hover:bg-green-600 text-white text-base sm:text-lg md:text-2xl font-black px-7 sm:px-8 md:px-12 py-3 md:py-4 rounded-full shadow-[0_6px_0_0_#166534] active:shadow-none active:translate-y-1 transition-all uppercase tracking-widest cursor-pointer flex items-center gap-2 md:gap-3 hover:scale-105 active:scale-95 duration-150"
          >
            <Play size={22} fill="currentColor" />
            MULAI GAME
          </button>
        </div>
        )}

        {/* STATE 2: GAMEPLAY LAYOUT */}
        {gameState === 'PLAYING' && (
          <div className="absolute inset-0 z-10 w-full h-full overflow-hidden cursor-crosshair">
            
            {/* RENDER KUMAN AKTIF */}
            {kuman && (
              <button
                id={`kuman_${kuman.id}`}
                onClick={handleKumanClick}
                className="absolute animate-pop-in hover:scale-110 focus:outline-none transition-transform duration-75 active:scale-90"
                style={{
                  left: `${kuman.x}px`,
                  top: `${kuman.y}px`,
                  width: `${kuman.size}px`,
                  height: `${kuman.size}px`,
                  color: KUMAN_COLORS[kuman.colorIndex],
                  cursor: "pointer",
                }}
              >
                {/* Visual Varian Kuman Lucu dengan SVG */}
                {kuman.variant === 'goofy' && <GoofyKumanSVG color={KUMAN_COLORS[kuman.colorIndex]} />}
                {kuman.variant === 'spiky' && <SpikyKumanSVG color={KUMAN_COLORS[kuman.colorIndex]} />}
                {kuman.variant === 'octopus' && <OctopusKumanSVG color={KUMAN_COLORS[kuman.colorIndex]} />}
                {kuman.variant === 'crowned' && <CrownedKumanSVG color={KUMAN_COLORS[kuman.colorIndex]} />}
              </button>
            )}

            {/* RENDER BURST / LETUSAN PARTIKEL */}
            {bursts.map(b => (
              <div 
                key={b.id} 
                className="absolute pointer-events-none z-20 flex items-center justify-center"
                style={{ left: `${b.x}px`, top: `${b.y}px` }}
              >
                {/* Lingkaran cincin merenggang ke luar */}
                <div 
                  className="absolute rounded-full border-4 animate-ping"
                  style={{ 
                    borderColor: b.color, 
                    width: '100px', 
                    height: '100px', 
                    animationDuration: '0.4s' 
                  }} 
                />
                {/* Serbuk partikel mini */}
                {[...Array(8)].map((_, i) => {
                  const angle = (i * 45 * Math.PI) / 180;
                  const distance = 40; // jarak terpancar
                  const targetX = Math.cos(angle) * distance;
                  const targetY = Math.sin(angle) * distance;

                  return (
                    <div
                      key={i}
                      className="absolute w-3.5 h-3.5 rounded-full"
                      style={{
                        backgroundColor: b.color,
                        transform: `translate(${targetX}px, ${targetY}px) scale(0)`,
                        transition: 'transform 0.4s cubic-bezier(0.1, 0.8, 0.3, 1), opacity 0.4s',
                        animation: `burstSpread 0.5s ease-out forwards`,
                        animationDelay: `${i * 10}ms`
                      }}
                    />
                  );
                })}
              </div>
            ))}

            {/* RENDER ANGKA "+1" MELAYANG */}
            {floatingTexts.map(t => (
              <div
                key={t.id}
                className="absolute pointer-events-none z-30 font-extrabold text-2xl text-emerald-500 animate-float-up drop-shadow-md flex items-center gap-1"
                style={{
                  left: `${t.x}px`,
                  top: `${t.y}px`,
                  transform: 'translateX(-50%)',
                  animation: 'floatAndFade 0.5s ease-out forwards'
                }}
              >
                +1
              </div>
            ))}

          </div>
        )}

        {/* STATE 3: SELESAI / FINISHED STATS LAYOUT */}
        {gameState === 'FINISHED' && (
          <div className="relative z-10 p-4 md:p-6 text-center max-w-3xl flex flex-col items-center justify-center animate-pop-in w-full">
            
            {/* Badge Jaminan Kebersihan */}
            <div className="bg-yellow-400 text-white font-black px-6 py-2 rounded-full mb-4 transform -rotate-2 shadow-md">
              GAME SELESAI!
            </div>

            <h2 className="text-2xl md:text-4xl font-black text-slate-800 mb-2">
              Skor Akhir: <span className="text-blue-600 underline decoration-blue-300 decoration-wavy tabular-nums">{score}</span>
            </h2>
            
            <p className="text-base md:text-xl font-black italic text-slate-600 mb-6 md:mb-12 text-center">
              "Luar biasa! Kamu telah menjadi pahlawan kebersihan hari ini!"
            </p>

            {/* TOMBOL MAIN LAGI UTAMA */}
            <button 
              id="restart_game_btn"
              onClick={handleStartGame}
              className="bg-green-500 hover:bg-green-600 text-white text-lg md:text-2xl font-black px-8 md:px-12 py-3 md:py-4 rounded-full shadow-[0_6px_0_0_#166534] active:shadow-none active:translate-y-1 transition-all uppercase tracking-widest cursor-pointer flex items-center gap-2 md:gap-3 hover:scale-105 active:scale-95 duration-150 mt-4 md:mt-6"
            >
              <RotateCcw size={26} className="animate-spin" style={{ animationDuration: '4s' }} /> MAIN LAGI
            </button>

          </div>
        )}

      </main>

      {/* 3. BAGIAN BAWAH (Di luar Kotak Game Utama - Tips untuk Orang Tua) */}
      <section className="w-full max-w-4xl select-none animate-fade-in" id="tips_section">
        
        <div className="bg-white rounded-3xl border-4 border-orange-200 p-8 shadow-xl flex flex-col md:flex-row items-start gap-6">
          <div className="bg-orange-500 p-4 rounded-2xl text-white shadow-md shrink-0 flex items-center justify-center">
            <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div className="flex-1">
            <h3 className="text-orange-600 font-black uppercase text-base tracking-wider mb-3">Tips Penting Untuk Orang Tua</h3>
            
            <div className="space-y-4 text-slate-700 leading-relaxed font-semibold text-sm">
              <p>
                💡 <span className="text-slate-800 font-extrabold">Habit Sejak Dini:</span> Mengajarkan anak mencuci tangan secara konsisten merupakan langkah pertahanan paling efektif bagi pembentukan imunitas si kecil dari berbagai penyakit menular berbahaya.
              </p>
              <p>
                🧼 <span className="text-slate-800 font-extrabold">Aturan 20 Detik:</span> Melatih anak membersihkan 6 area penting (telapak, punggung tangan, sela-sela jari, kuku-kuku, hingga pergelangan tangan) sambil menyanyikan lagu menyenangkan agar terasa seru dan bebas stres.
              </p>
              <p>
                🏠 <span className="text-slate-800 font-extrabold">Momen Emas:</span> Ingat untuk mendisiplinkan rutinitas ini sebelum makan, sesudah buang air di toilet, setelah menyentuh mainan kotor di luar rumah, dan setelah menyentuh hewan peliharaan.
              </p>
            </div>
          </div>
        </div>

        {/* Footer info kecil Sobat Anak */}
        <p className="text-center text-[11px] text-gray-400 mt-4 leading-relaxed font-semibold">
          Dihadirkan oleh 💗 <b>Sobat Anak</b> © 2026. Semua tips disusun demi mendukung generasi Indonesia yang cerdas, sehat, dan ceria.
        </p>
      </section>

      {/* 4. MODAL DRAWER PRODUK INTERAKTIF DETAIL */}
      {isProductModalOpen && (
        <div id="product_modal" className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-fade-in">
          
          <div
  className="bg-white w-full max-w-lg overflow-hidden shadow-2xl relative border-4 border-teal-200 animate-pop-in"
  style={{
    borderRadius: "32px"
  }}
>
            
            {/* Tombol Tutup Silang Pojok Kanan */}
            <button 
              id="close_product_modal_btn"
              onClick={() => setIsProductModalOpen(false)}
              className="absolute top-4 right-4 bg-gray-100 hover:bg-rose-100 text-gray-500 hover:text-rose-600 p-2 rounded-full transition-all cursor-pointer z-10"
              title="Tutup"
            >
              <X size={20} />
            </button>

            {!isCheckoutSuccess ? (
              <div>
                
                {/* Banner Header Modal */}
                <div className="text-white p-6 relative"
                style={{
                  backgroundImage: "linear-gradient(to right, #14b8a6, #34d399)"
                  }}
                  >
                  <div className="absolute top-0 right-0 opacity-10 scale-150 transform translate-x-4 -translate-y-4">
                    <Store size={120} />
                  </div>
                  <span className="bg-emerald-600/50 text-[#FFD54F] font-bold text-[10px] tracking-widest uppercase px-3 py-1 rounded-full inline-block mb-2 border border-emerald-500">
                    SABUN ANAK PILIHAN PEDIATRI
                  </span>
                  <h2 className="text-2xl font-extrabold tracking-tight">Sabun Antiseptik Tentang Anak</h2>
                  <p className="text-teal-50/90 text-xs font-medium mt-1 leading-relaxed">
                    Perisai antibakterial alami dari ekstrak lidah buaya & chamomile untuk kulit bayi lembut bebas iritasi.
                  </p>
                </div>

                {/* Konten Inti Produk */}
                <div className="p-6 space-y-4">
                  
                  {/* Foto Mock / Pilihan Aroma */}
                  <div>
                    <label className="block text-xs font-bold text-gray-400 tracking-wider uppercase mb-2">PILIH VARIAN AROMA SEGAR</label>
                    <div className="grid grid-cols-3 gap-2">
                      {[
                        { name: 'Strawberry', color: 'border-rose-400 bg-rose-50 text-rose-800', emoji: '🍓 Sweet Strawberry' },
                        { name: 'Chamomile', color: 'border-amber-400 bg-amber-50 text-amber-800', emoji: '🌼 Calming Flower' },
                        { name: 'Apple', color: 'border-emerald-400 bg-emerald-50 text-emerald-800', emoji: '🍏 Apple Giggles' }
                      ].map((v) => (
                        <button
                          key={v.name}
                          id={`variant_btn_${v.name}`}
                          onClick={() => setSelectedProductVariant(v.name as any)}
                          className={`border-2 rounded-2xl py-2 px-1 text-center text-xs font-bold transition-all relative cursor-pointer ${
                            selectedProductVariant === v.name 
                              ? `${v.color} ring-4 ring-teal-100 scale-102` 
                              : 'border-gray-100 bg-white hover:bg-gray-50 text-gray-500'
                          }`}
                        >
                          {v.emoji}
                          {selectedProductVariant === v.name && (
                            <span className="absolute -top-1.5 -right-1.5 bg-teal-500 text-white w-4 h-4 rounded-full text-[9px] flex items-center justify-center font-bold">✓</span>
                          )}
                        </button>
                      ))}
                    </div>
                  </div>

                  {/* Informasi Ringkas Fitur */}
                  <div className="bg-teal-50/50 p-4 rounded-2xl border border-teal-100 grid grid-cols-2 gap-2 text-xs">
                    <div className="flex items-center gap-1.5 font-bold text-teal-800">
                      <span className="text-emerald-500">✔</span> pH-Balanced 5.5 Ramah Kulit
                    </div>
                    <div className="flex items-center gap-1.5 font-bold text-teal-800">
                      <span className="text-emerald-500">✔</span> Formula Tanpa Perih Di Mata
                    </div>
                    <div className="flex items-center gap-1.5 font-bold text-teal-800">
                      <span className="text-emerald-500">✔</span> Lolos Uji Hypoallergenic
                    </div>
                    <div className="flex items-center gap-1.5 font-bold text-teal-800">
                      <span className="text-emerald-500">✔</span> 100% Organik & Sertifikasi Halal
                    </div>
                  </div>

                  {/* Penjumlahan Kuantitas dan Harga */}
                  <div className="flex items-center justify-between border-t border-b border-gray-100 py-3.5">
                    <div>
                      <span className="block text-xs font-semibold text-gray-400">HARGA SATUAN</span>
                      <span className="text-xl font-extrabold text-teal-800">Rp 45.000</span>
                    </div>

                    {/* Quantity counter */}
                    <div className="flex items-center gap-2 bg-gray-100 p-1.5 rounded-2xl">
                      <button 
                        id="qty_minus_btn"
                        onClick={() => setProductQuantity(prev => Math.max(1, prev - 1))}
                        className="bg-white hover:bg-gray-50 text-gray-700 w-8 h-8 rounded-xl font-extrabold flex items-center justify-center border border-gray-100 shadow-sm cursor-pointer"
                      >
                        -
                      </button>
                      <span className="w-8 text-center text-sm font-extrabold text-gray-800">{productQuantity}</span>
                      <button 
                        id="qty_plus_btn"
                        onClick={() => setProductQuantity(prev => prev + 1)}
                        className="bg-white hover:bg-gray-50 text-gray-700 w-8 h-8 rounded-xl font-extrabold flex items-center justify-center border border-gray-100 shadow-sm cursor-pointer"
                      >
                        +
                      </button>
                    </div>
                  </div>

                  {/* Total harga */}
                  <div className="flex justify-between items-center bg-[#FFD54F]/10 p-3 rounded-2xl border border-[#FFD54F]/30">
                    <span className="text-xs font-bold text-amber-900 flex items-center gap-1">
                      <Sparkles size={14} className="text-amber-500" /> TOTAL BAYAR
                    </span>
                    <span className="text-lg font-extrabold text-amber-800">
                      Rp {(45000 * productQuantity).toLocaleString('id-ID')}
                    </span>
                  </div>

                  {/* CTA Checkout Simulasi */}
                  <button
                    id="submit_checkout_btn"
                    onClick={() => {
                      setIsCheckoutSuccess(true);
                      playWinSound(); // Suara seru berhasil
                    }}
                    className="w-full py-4 bg-[#FF8A65] hover:bg-[#ff764c] text-white font-extrabold rounded-2xl shadow-lg border-b-6 border-coral-800 flex items-center justify-center gap-2 transition-all hover:scale-102 active:scale-98 cursor-pointer"
                  >
                    Beli Sekarang via Shopee/Tokopedia 🛍️
                  </button>

                  <p className="text-center text-[10px] text-gray-400 font-medium">
                    *Membeli sabun Tentang Anak akan langsung mengarah ke keranjang belanja aman di marketplace resmi mitra kami.
                  </p>

                </div>

              </div>
            ) : (
              // TAMPILAN JIKA BERHASIL CHECKOUT (SIMULASI TERPERGOK CHEERFUL)
              <div className="p-8 text-center space-y-6 animate-pop-in">
                
                <div className="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto border-4 border-emerald-400 animate-bounce">
                  <CheckCircle size={44} />
                </div>

                <div className="space-y-2">
                  <h3 className="text-2xl font-extrabold text-teal-800">Pesanan Simulasi Terbuat! 🎉</h3>
                  <p className="text-sm text-gray-600 font-medium leading-relaxed max-w-sm mx-auto">
                    Keren sekali! Paket kebersihan berupa <b>{productQuantity}x Sabun Antiseptik ({selectedProductVariant})</b> siap dikemas untuk si kecil agar bisa mendampingi petualangan mandi bersihnya!
                  </p>
                </div>

                <div className="bg-teal-50 p-4 rounded-2xl border border-teal-100 max-w-xs mx-auto">
                  <div className="flex justify-between text-xs text-teal-900 font-bold mb-1.5">
                    <span>Total Pembayaran:</span>
                    <span>Rp {(45000 * productQuantity).toLocaleString('id-ID')}</span>
                  </div>
                  <div className="flex justify-between text-[10px] text-teal-700">
                    <span>Status Transaksi:</span>
                    <span className="font-bold text-emerald-600">Terbuka di Tab Baru ✔</span>
                  </div>
                </div>

                <div className="flex gap-2.5 max-w-sm mx-auto">
                  <button
                    id="back_to_game_from_checkout_btn"
                    onClick={() => setIsProductModalOpen(false)}
                    className="flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition-all cursor-pointer"
                  >
                    Tutup & Kembali Ke Game
                  </button>
                  <button
                    id="explore_more_products_btn"
                    onClick={() => {
                      // Buka website Tentang Anak di tab lain sebagai simulasi nyata
                      window.open("https://tentanganak.id", "_blank");
                    }}
                    className="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-200 transition-all cursor-pointer"
                  >
                    Jelajahi TentangAnak.id 🌐
                  </button>
                </div>

              </div>
            )}

          </div>

        </div>
      )}

    </div>
  );
}

// ==========================================
// SUB-KOMPONEN VECTOR SVG VARIANT KUMAN LUCU
// ==========================================

// Kuman Kiri: Si Goofy Merah
function GoofyKumanSVG({ color }: { color: string }) {
  return (
    <svg viewBox="0 0 100 100" className="w-full h-full filter drop-shadow-md overflow-visible select-none">
      <g>
        {/* Swirling Corona-like spikes */}
        {[...Array(8)].map((_, i) => {
          const deg = i * 45;
          return (
            <path
              key={i}
              d="M50 50 L50 15 A8 8 0 1 1 50 5 Z"
              fill={color}
              transform={`rotate(${deg} 50 50)`}
              className="origin-center"
            />
          );
        })}
        {/* Core Body */}
        <circle cx="55" cy="55" r="32" fill={color} stroke="#fff" strokeWidth="3" />
        
        {/* Soft Spots */}
        <circle cx="45" cy="45" r="4" fill="#000" opacity="0.1" />
        <circle cx="67" cy="53" r="5" fill="#000" opacity="0.1" />
        <circle cx="53" cy="69" r="3" fill="#000" opacity="0.1" />
        
        {/* Big Cartoon Eyes */}
        <circle cx="47" cy="50" r="8.5" fill="#fff" />
        <circle cx="48" cy="50" r="4.2" fill="#1e293b" />
        <circle cx="50" cy="48" r="2" fill="#fff" />

        <circle cx="62" cy="50" r="8.5" fill="#fff" />
        <circle cx="61" cy="50" r="4.2" fill="#1e293b" />
        <circle cx="63" cy="48" r="2" fill="#fff" />

        {/* Smile */}
        <path
          d="M46 63 Q55 73 64 63"
          stroke="#1e293b"
          strokeWidth="4.5"
          strokeLinecap="round"
          fill="none"
        />

        {/* Blush */}
        <ellipse cx="40" cy="57" rx="3.5" ry="2" fill="#f43f5e" opacity="0.5" />
        <ellipse cx="70" cy="57" rx="3.5" ry="2" fill="#f43f5e" opacity="0.5" />
      </g>
    </svg>
  );
}

// Kuman Tengah: Spiky Hijau Bertentakel Runcing
function SpikyKumanSVG({ color }: { color: string }) {
  return (
    <svg viewBox="0 0 100 100" className="w-full h-full filter drop-shadow-md overflow-visible select-none">
      <defs>
        <clipPath id="eyeball-clip">
          <circle cx="50" cy="40" r="14" />
        </clipPath>
      </defs>
      <g>
        {/* Star Blob body */}
        <path 
          d="M50 15 C58 28 66 18 75 30 C85 40 76 52 82 65 C85 75 75 80 65 82 C52 85 48 76 35 82 C22 84 18 72 15 60 C12 48 22 40 25 28 C28 15 40 25 50 15 Z" 
          fill={color} 
          stroke="#fff" 
          strokeWidth="3" 
        />
        
        {/* Pattern Dots */}
        <circle cx="34" cy="30" r="4.5" fill="#fff" opacity="0.3" />
        <circle cx="68" cy="32" r="5" fill="#fff" opacity="0.3" />
        <circle cx="30" cy="52" r="3.5" fill="#fff" opacity="0.3" />
        <circle cx="68" cy="70" r="4" fill="#fff" opacity="0.3" />

        {/* Giant Cyplops Eyeball */}
        <circle cx="50" cy="42" r="13" fill="#fff" stroke="#1e293b" strokeWidth="2" />
        <circle cx="50" cy="42" r="6" fill="#1e293b" />
        <circle cx="52" cy="39" r="2.5" fill="#fff" />
        
        {/* Sleepy eyelid */}
        <path d="M37 36 Q50 44 63 36" fill="none" stroke="#1e293b" strokeWidth="2.5" strokeLinecap="round" />

        {/* Happy wide smile */}
        <path d="M42 60 Q50 68 58 60" stroke="#1e293b" strokeWidth="4" strokeLinecap="round" fill="none" />
        
        {/* Sharp little fangs */}
        <path d="M44 61 L46 64 L48 61 Z" fill="#fff" />
        <path d="M52 61 L54 64 L56 61 Z" fill="#fff" />
      </g>
    </svg>
  );
}

// Kuman Kanan: Octopus Biru Berkaki Lembut
function OctopusKumanSVG({ color }: { color: string }) {
  return (
    <svg viewBox="0 0 100 100" className="w-full h-full filter drop-shadow-md overflow-visible select-none">
      <g>
        {/* Soft waving tentacles on bottom */}
        <path d="M22 68 Q15 88 28 88 Q35 70 35 68" fill={color} stroke="#fff" strokeWidth="3" strokeLinejoin="round" />
        <path d="M35 68 Q30 92 42 92 Q48 70 48 68" fill={color} stroke="#fff" strokeWidth="3" strokeLinejoin="round" />
        <path d="M48 68 Q52 92 60 92 Q65 70 65 68" fill={color} stroke="#fff" strokeWidth="3" strokeLinejoin="round" />
        <path d="M65 68 Q72 88 80 88 Q78 68 78 68" fill={color} stroke="#fff" strokeWidth="3" strokeLinejoin="round" />

        {/* Main Jellyfish Body */}
        <path d="M20 50 C20 20 80 20 80 50 C80 62 80 70 75 70 C70 70 65 66 50 66 C35 66 30 70 25 70 C20 70 20 62 20 50 Z" fill={color} stroke="#fff" strokeWidth="3" />
        
        {/* Spots */}
        <circle cx="50" cy="24" r="6" fill="#fff" opacity="0.3" />
        <circle cx="34" cy="30" r="4" fill="#fff" opacity="0.3" />
        <circle cx="66" cy="32" r="4.5" fill="#fff" opacity="0.3" />

        {/* Confused / Wobbly Cartoon Eyes */}
        <circle cx="38" cy="45" r="7.5" fill="#fff" />
        <circle cx="38" cy="45" r="3.2" fill="#1e293b" />
        <ellipse cx="39.5" cy="43.5" rx="1.5" ry="1" fill="#fff" />

        <circle cx="62" cy="45" r="7.5" fill="#fff" />
        <circle cx="62" cy="45" r="3.2" fill="#1e293b" />
        <ellipse cx="63.5" cy="43.5" rx="1.5" ry="1" fill="#fff" />

        {/* "O" shaped cute mouth */}
        <circle cx="50" cy="54" r="4.5" fill="#1e293b" />
        <circle cx="48.5" cy="52.5" r="1.5" fill="#fff" />
      </g>
    </svg>
  );
}

// Kuman Rahasia: Mahkota Kuning Berbintik Lucu
function CrownedKumanSVG({ color }: { color: string }) {
  return (
    <svg viewBox="0 0 100 100" className="w-full h-full filter drop-shadow-md overflow-visible select-none">
      <g>
        {/* Crown antennas */}
        <path d="M35 25 L30 5 L43 18" stroke={color} strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" fill="none" />
        <path d="M50 20 L50 2 L50 20" stroke={color} strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" fill="none" />
        <path d="M65 25 L70 5 L57 18" stroke={color} strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" fill="none" />
        
        <circle cx="30" cy="5" r="3.5" fill={color} />
        <circle cx="50" cy="2" r="3.5" fill={color} />
        <circle cx="70" cy="5" r="3.5" fill={color} />

        {/* Squishy body */}
        <ellipse cx="50" cy="54" rx="35" ry="28" fill={color} stroke="#fff" strokeWidth="3" />
        
        {/* Cheerful details */}
        <ellipse cx="32" cy="55" rx="5" ry="3" fill="#f43f5e" opacity="0.4" />
        <ellipse cx="68" cy="55" rx="5" ry="3" fill="#f43f5e" opacity="0.4" />

        {/* Left eye winking (X) */}
        <path d="M32 40 L40 46 M40 40 L32 46" stroke="#1e293b" strokeWidth="4.5" strokeLinecap="round" />
        
        {/* Right eye big round shiny */}
        <circle cx="62" cy="42" r="8" fill="#fff" />
        <circle cx="62" cy="42" r="4" fill="#1e293b" />
        <circle cx="64" cy="40" r="1.8" fill="#fff" />

        {/* Big smiley tongue mouth */}
        <path d="M44 58 C44 65 56 65 56 58" fill="#f43f5e" stroke="#1e293b" strokeWidth="3.5" strokeLinecap="round" />
      </g>
    </svg>
  );
}

// Komponen Ikon Buku Lucu Internal
function BookOpenIcon(props: React.SVGProps<SVGSVGElement>) {
  return (
    <svg
      {...props}
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
      <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
    </svg>
  );
}
