import React, { useState, useEffect, useRef } from "react";
import { Timer, Trophy, Play, RotateCcw, Sun, Volume2, VolumeX } from "lucide-react";
import { motion, AnimatePresence } from "motion/react";

// Tipe data untuk objek makanan/minuman yang jatuh
interface CatchItem {
  id: number;
  name: string;
  type: "healthy" | "unhealthy";
  img: string;
  fallbackSvg: string;
  x: number; // posisi horizontal dalam persen (0 - 92)
  y: number; // posisi vertikal dalam piksel (-60 - 450)
  speed: number;
}

// Data template item beserta custom SVG fallback yang ceria & ramah anak
const ITEM_TEMPLATES = [
  {
    name: "Apel Segar",
    img: "/apel.png",
    type: "healthy" as const,
    fallbackSvg: `data:image/svg+xml;utf8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="g-apel" cx="40%" cy="40%" r="60%"><stop offset="0%" stop-color="#FFAD90"/><stop offset="60%" stop-color="#FF8A65"/><stop offset="100%" stop-color="#E05B30"/></radialGradient></defs><path d="M50 35 C52 20, 60 18, 62 15 C58 15, 51 22, 49 28" stroke="#8D6E63" stroke-width="4" stroke-linecap="round" fill="none"/><path d="M51 24 C55 15, 68 15, 65 24 C58 28, 53 26, 51 24" fill="#81C784" stroke="#FFF" stroke-width="2"/><path d="M30 40 C20 40, 20 75, 45 82 C48 83, 52 83, 55 82 C80 75, 80 40, 70 40 C60 40, 53 45, 50 45 C47 45, 40 40, 30 40 Z" fill="url(#g-apel)" stroke="#FFFFFF" stroke-width="3"/><ellipse cx="38" cy="52" rx="6" ry="10" fill="#FFFFFF" opacity="0.4" transform="rotate(-15 38 52)"/><text x="50" y="65" font-family="sans-serif" font-weight="bold" font-size="20" fill="white" text-anchor="middle" opacity="0.9">🍎</text></svg>`)}`
  },
  {
    name: "Wortel Manis",
    img: "/wortel.png",
    type: "healthy" as const,
    fallbackSvg: `data:image/svg+xml;utf8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><linearGradient id="g-wortel" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#FFB74D"/><stop offset="100%" stop-color="#F57C00"/></linearGradient></defs><path d="M50 30 C45 20, 40 10, 35 15 C40 20, 46 25, 48 30" fill="none" stroke="#81C784" stroke-width="6" stroke-linecap="round"/><path d="M50 30 C50 15, 53 5,  56 8  C55 18, 52 25, 51 30" fill="none" stroke="#81C784" stroke-width="6" stroke-linecap="round"/><path d="M50 30 C55 20, 65 12, 70 18 C62 23, 54 27, 52 30" fill="none" stroke="#81C784" stroke-width="6" stroke-linecap="round"/><path d="M35 32 C45 30, 55 30, 65 32 C68 40, 55 75, 50 88 C45 75, 32 40, 35 32 Z" fill="url(#g-wortel)" stroke="#FFFFFF" stroke-width="3"/><path d="M38 45 L48 45" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/><path d="M52 55 L62 55" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/><path d="M42 65 L50 65" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/><text x="50" y="55" font-family="sans-serif" font-weight="bold" font-size="20" fill="white" text-anchor="middle" opacity="0.9">🥕</text></svg>`)}`
  },
  {
    name: "Susu Segar",
    img: "/susu.png",
    type: "healthy" as const,
    fallbackSvg: `data:image/svg+xml;utf8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><linearGradient id="g-suku" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#EFF6FF"/><stop offset="100%" stop-color="#4FC3F7"/></linearGradient></defs><rect x="30" y="32" width="40" height="50" rx="4" fill="url(#g-suku)" stroke="#FFFFFF" stroke-width="3"/><path d="M30 32 L50 18 L70 32 Z" fill="#4FC3F7" stroke="#FFFFFF" stroke-width="3" stroke-linejoin="round"/><rect x="44" y="14" width="12" height="6" rx="2" fill="#0288D1" stroke="#FFFFFF" stroke-width="1.5"/><path d="M30 55 Q 40 48, 50 55 T 70 55 L 70 78 C 70 80, 70 82, 68 82 L 32 82 C 30 82, 30 80, 30 78 Z" fill="#02a8f3" opacity="0.8"/><circle cx="43" cy="42" r="3" fill="#1E2939"/><circle cx="57" cy="42" r="3" fill="#1E2939"/><path d="M48 45 Q 50 48, 52 45" fill="none" stroke="#1E2939" stroke-width="2" stroke-linecap="round"/><text x="50" y="70" font-family="sans-serif" font-weight="bold" font-size="10" fill="white" text-anchor="middle">MILK</text></svg>`)}`
  },
  {
    name: "Permen Manis",
    img: "/permen.png",
    type: "unhealthy" as const,
    fallbackSvg: `data:image/svg+xml;utf8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="g-candy" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#FFF"/><stop offset="40%" stop-color="#FF8A65"/><stop offset="100%" stop-color="#E25C37"/></radialGradient></defs><rect x="47" y="52" width="6" height="38" rx="3" fill="#D7CCC8" stroke="#FFFFFF" stroke-width="2"/><circle cx="50" cy="35" r="26" fill="url(#g-candy)" stroke="#FFFFFF" stroke-width="3"/><path d="M50 35 Q60 20 62 35 T 50 48 T 38 35 T 50 22" fill="none" stroke="#FFD54F" stroke-width="4.5" stroke-linecap="round"/><path d="M42 58 L58 58 L50 52 Z" fill="#4FC3F7" stroke="#FFFFFF" stroke-width="2"/><text x="50" y="42" font-family="sans-serif" font-weight="bold" font-size="20" fill="white" text-anchor="middle" opacity="0.9">🍭</text></svg>`)}`
  },
  {
    name: "Donat Cokelat",
    img: "/donat.png",
    type: "unhealthy" as const,
    fallbackSvg: `data:image/svg+xml;utf8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="32" fill="#8D6E63" stroke="#FFFFFF" stroke-width="3"/><path d="M50 18 C68 18, 82 32, 82 50 C82 62, 70 76, 50 82 C30 76, 18 62, 18 50 C18 32, 32 18, 50 18 Z" fill="#5D4037"/><circle cx="50" cy="50" r="12" fill="#FFF7ED" stroke="#FFFFFF" stroke-width="3"/><rect x="36" y="32" width="6" height="2.5" rx="1" fill="#FFD54F" transform="rotate(30 36 32)"/><rect x="62" y="30" width="6" height="2.5" rx="1" fill="#4FC3F7" transform="rotate(-40 62 30)"/><rect x="64" y="62" width="6" height="2.5" rx="1" fill="#FF8A65" transform="rotate(15 64 62)"/><rect x="30" y="58" width="6" height="2.5" rx="1" fill="#81C784" transform="rotate(70 30 58)"/><rect x="48" y="26" width="6" height="2.5" rx="1" fill="#FF8A65" transform="rotate(110 48 26)"/><rect x="52" y="70" width="6" height="2.5" rx="1" fill="#FFD54F" transform="rotate(45 52 70)"/><text x="50" y="57" font-family="sans-serif" font-weight="bold" font-size="20" fill="white" text-anchor="middle" opacity="0.9">🍩</text></svg>`)}`
  },
  {
    name: "Soda Manis",
    img: "/soda.png",
    type: "unhealthy" as const,
    fallbackSvg: `data:image/svg+xml;utf8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><linearGradient id="g-soda" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#4E342E"/><stop offset="100%" stop-color="#211512"/></linearGradient></defs><path d="M44 18 L56 18 L54 30 L62 42 L62 82 C62 85, 58 88, 50 88 C42 88, 38 85, 38 82 L38 42 L46 30 Z" fill="url(#g-soda)" stroke="#FFFFFF" stroke-width="3" stroke-linejoin="round"/><path d="M38 48 L62 48 L62 64 L38 64 Z" fill="#DC2626" stroke="#FFFFFF" stroke-width="1.5"/><path d="M42 56 Q 50 51, 58 56" fill="none" stroke="#FFD54F" stroke-width="2"/><rect x="44" y="14" width="12" height="6" rx="1.5" fill="#D2143A" stroke="#FFFFFF" stroke-width="2"/><path d="M41 42 L41 80" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" opacity="0.3"/><text x="50" y="74" font-family="sans-serif" font-weight="bold" font-size="20" fill="white" text-anchor="middle" opacity="0.9">🥤</text></svg>`)}`
  }
];

export default function App() {

  // Game States
  const [gameState, setGameState] = useState<"start" | "playing" | "end">("start");
  const [score, setScore] = useState(0);
  const [timeLeft, setTimeLeft] = useState(30);
  const [basketMood, setBasketMood] = useState<"happy" | "cool" | "surprised">("happy");
  const [basketX, setBasketX] = useState(35); // Posisi horizontal basket dalam persen (0 - 85)
  const [items, setItems] = useState<CatchItem[]>([]);
  const [lastCrash, setLastCrash] = useState<{ x: number; y: number; text: string; color: string } | null>(null);
  const [isMuted, setIsMuted] = useState(false);
  const bgmRef = useRef<HTMLAudioElement | null>(null);
  const [highScore, setHighScore] = useState(() => {
    const saved = localStorage.getItem("highscore_sobat_anak");
    return saved ? parseInt(saved, 10) : 0;
  });

  // References
  const containerRef = useRef<HTMLDivElement>(null);
  const basketXRef = useRef(35);
  const speedScaleRef = useRef(1);
  const scoreRef = useRef(0);
  const timeLeftRef = useRef(30);
  const itemsRef = useRef<CatchItem[]>([]);

  // Sync references
  useEffect(() => {
    basketXRef.current = basketX;
  }, [basketX]);

  useEffect(() => {
    scoreRef.current = score;
  }, [score]);

  useEffect(() => {
    timeLeftRef.current = timeLeft;
  }, [timeLeft]);

  useEffect(() => {
    if (score > highScore) {
      setHighScore(score);
      localStorage.setItem("highscore_sobat_anak", score.toString());
    }
  }, [score, highScore]);

useEffect(() => {
  if (gameState !== "start") return;

  const moods: Array<"happy" | "cool" | "surprised"> = [
    "happy",
    "cool",
    "surprised",
  ];

  const moodInterval = setInterval(() => {
    const randomMood = moods[Math.floor(Math.random() * moods.length)];
    setBasketMood(randomMood);
  }, 1500);

  return () => clearInterval(moodInterval);
}, [gameState]);

  useEffect(() => {
  const bgm = new Audio("/audio/backsound-keranjang-sehat.mp3");

  bgm.loop = true;
  bgm.volume = 0.25;
  bgm.muted = isMuted;

  bgmRef.current = bgm;

  return () => {
    bgm.pause();
  };
}, []);

const playBacksound = () => {
  if (!bgmRef.current) return;
  if (isMuted) return;

  bgmRef.current.muted = false;
  bgmRef.current.play().catch(() => {
    // Browser kadang menolak audio kalau belum ada interaksi user
  });
};

const pauseBacksound = () => {
  if (!bgmRef.current) return;

  bgmRef.current.pause();
};

const toggleMute = () => {
  setIsMuted((prev) => {
    const newMuted = !prev;

    if (bgmRef.current) {
      bgmRef.current.muted = newMuted;

      if (newMuted) {
        bgmRef.current.pause();
      } else if (gameState === "playing") {
        bgmRef.current.play().catch(() => {});
      }
    }

    return newMuted;
  });
};

  // Audio Synthesizer (Standard Web Audio API)
  const playSound = (type: "healthy" | "unhealthy") => {
    if (isMuted) return;
    try {
      const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
      if (!AudioContextClass) return;
      const ctx = new AudioContextClass();

      if (type === "healthy") {
        // Nada tinggi ceria "Tring!"
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = "sine";
        
        // Melodi arpeggio tipis
        osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
        osc.frequency.setValueAtTime(880.00, ctx.currentTime + 0.08); // A5
        
        gain.gain.setValueAtTime(0.12, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
        
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.35);
      } else {
        // Suara rendah thud/pop "Oops!"
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = "sawtooth";
        
        osc.frequency.setValueAtTime(140.00, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(70.00, ctx.currentTime + 0.25);
        
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.25);
        
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.25);
      }
    } catch (e) {
      console.warn("Web Audio API diblokir oleh interaksi browser atau tidak didukung:", e);
    }
  };

  // Game Loop Timer countdown (1 second interval)
  useEffect(() => {
    if (gameState !== "playing") return;

    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          pauseBacksound();
          setGameState("end");
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [gameState]);

  // Main Event Loop (requestAnimationFrame) untuk koordinat jatuh & tumbukan
  useEffect(() => {
    if (gameState !== "playing") return;

    let animationFrameId: number;
    let lastTime = performance.now();
    let spawnAccumulator = 0;
    let idCounter = 0;

    // Menghitung interval spawn secara adaptif (makin lama durasi, spawn makin cepat)
    const getSpawnInterval = () => {
      const elapsed = 30 - timeLeftRef.current; // 0 hingga 30
      const ratio = elapsed / 30; // 0 hingga 1
      return Math.max(750, 1600 - ratio * 850); // Bergerak dari 1600ms turun ke 750ms
    };

    // Menghitung base speed jatuh yang berangsur bertambah cepat dan ramah anak
    const getBaseSpeed = () => {
      const elapsed = 30 - timeLeftRef.current;
      const ratio = elapsed / 30;
      // Diatur agar ramah anak (2.2px sampai 4.5px per frame pada 60fps)
      return 2.2 + ratio * 2.3; 
    };

    const loop = (now: number) => {
      const delta = now - lastTime;
      lastTime = now;

      // Amankan jika ada frame-drop masif agar tidak loncat instan
      const clampedDelta = Math.min(100, delta);
      const frameFactor = clampedDelta / 16.67; // Faktor relatif terhadap 60fps

      // 1. Spawning Makanan Acak dari atas
      spawnAccumulator += clampedDelta;
      const spawnInterval = getSpawnInterval();
      if (spawnAccumulator >= spawnInterval) {
        spawnAccumulator = 0;
        
        // Memilih template acak secara bergantian
        const template = ITEM_TEMPLATES[Math.floor(Math.random() * ITEM_TEMPLATES.length)];
        // Kecepatan dihitung dengan deviasi kecil acak agar bervariasi menyenangkan
        const speed = getBaseSpeed() + (Math.random() * 1.0);
        
        const newItem: CatchItem = {
          id: idCounter++,
          name: template.name,
          type: template.type,
          img: template.img,
          fallbackSvg: template.fallbackSvg,
          x: Math.random() * 90, // Batas yang aman agar tidak mepet keluar pinggir
          y: -70, // Mulai dari atas di luar kotak bermain secara rapi
          speed: speed
        };

        itemsRef.current = [...itemsRef.current, newItem];
        setItems(itemsRef.current);
      }

      // 2. Kalkulasi pergerakan jatuh dan validasi collision (Overlapping)
      const currentItems = itemsRef.current;
      const activeItems: CatchItem[] = [];
      const rect = containerRef.current?.getBoundingClientRect();
      const containerWidth = rect ? rect.width : 800; // Default fallback width
      
      // Ukuran elemen yang seimbang sesuai grid responsive
      const itemWidthPx = containerWidth * 0.09; 
      const itemHeightPx = 54;
      const basketWidthPx = containerWidth * 0.16;
      const basketHeightPx = 55;
      const basketYTop = 450 - basketHeightPx - 15; // Jarak aman dasar bermain (y: 380px)
      const basketYBottom = 450 - 15;

      currentItems.forEach((item) => {
        // Kalikan kecepatan jatuh dengan frameFactor demi kelancaran dan kestabilan antar perangkat (60Hz vs 120Hz+)
        const nextY = item.y + (item.speed * frameFactor);

        // Hitung batas bounding-box
        const itemLeft = (item.x / 100) * containerWidth;
        const itemRight = itemLeft + itemWidthPx;
        const itemTop = nextY;
        const itemBottom = nextY + itemHeightPx;

        const basketLeft = (basketXRef.current / 100) * containerWidth;
        const basketRight = basketLeft + basketWidthPx;

        // Bounding Box Overlap Validation
        const isXOverlap = itemRight >= basketLeft && itemLeft <= basketRight;
        const isYOverlap = itemBottom >= basketYTop && itemTop <= basketYBottom;

        if (isXOverlap && isYOverlap) {
          // Deteksi tumbukan berhasil!
          if (item.type === "healthy") {
            setScore((s) => s + 10);
            playSound("healthy");
            // Koordinat splash untuk visual feedback pop-up (+/- angka murni tanpa teks asupan gizi)
            setLastCrash({
              x: item.x,
              y: Math.max(10, (item.y / 450) * 100),
              text: "+10",
              color: "text-emerald-500 font-black"
            });
          } else {
            setScore((s) => s - 5);
            playSound("unhealthy");
            setLastCrash({
              x: item.x,
              y: Math.max(10, (item.y / 450) * 100),
              text: "-5",
              color: "text-rose-500 font-extrabold"
            });
          }
          // Hapus pop visual feedback setelah setengah detik
          setTimeout(() => setLastCrash(null), 600);
        } else if (nextY > 450) {
          // Jatuh melewati dasar, hilang asinkronus
        } else {
          activeItems.push({ ...item, y: nextY });
        }
      });

      itemsRef.current = activeItems;
      setItems(activeItems);

      animationFrameId = requestAnimationFrame(loop);
    };

    animationFrameId = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(animationFrameId);
  }, [gameState]);

  // Desktop Controls: Tracking horizontal cursor movement
  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (gameState !== "playing") return;
    const rect = e.currentTarget.getBoundingClientRect();
    const cursorX = e.clientX - rect.left; // Koordinat relatif terhadap sisi kiri container
    const pct = (cursorX / rect.width) * 100;
    
    // Set agar posisi mouse berada di tengah-tengah keranjang (lebar keranjang ~16%)
    let newX = pct - 8;
    if (newX < 0) newX = 0;
    if (newX > 84) newX = 84; // Batas kanan agar tidak terpotong
    setBasketX(newX);
  };

  // Mobile/Tablet Controls: Touch drag-and-swipe event
  const handleTouchMove = (e: React.TouchEvent<HTMLDivElement>) => {
    if (gameState !== "playing") return;
    
    // Cegah behavior scroll layar pada mobile agar kontrol mulus
    if (e.cancelable) {
      e.preventDefault();
    }

    const rect = containerRef.current?.getBoundingClientRect();
    if (!rect) return;

    const touch = e.touches[0];
    const touchX = touch.clientX - rect.left;
    const pct = (touchX / rect.width) * 100;

    let newX = pct - 8;
    if (newX < 0) newX = 0;
    if (newX > 84) newX = 84;
    setBasketX(newX);
  };

  // Mulai game baru
const handleStartGame = () => {
  setScore(0);
  setTimeLeft(30);
  setBasketX(35);
  itemsRef.current = [];
  setItems([]);

  setGameState("playing");
  playBacksound();
};

  // Kembali main lagi
const handleRestart = () => {
  setScore(0);
  setTimeLeft(30);
  setBasketX(35);
  itemsRef.current = [];
  setItems([]);

  setGameState("playing");
  playBacksound();
};

  // Evaluasi teks & ikon akhir berdasarkan skor yang didapat anak secara cerdas
  const getEvaluationContent = () => {
    if (score <= 0) {
      return {
        colorClass: "bg-orange-100 text-orange-600 border-orange-200",
        desc: `Keranjangmu masih kosong dari makanan sehat! 🛒`
      };
    } else if (score >= 5 && score <= 50) {
      return {
        colorClass: "bg-blue-100 text-blue-600 border-blue-200",
        desc: `Yuk, main lagi dan pilih makanan yang lebih sehat! 🍎`
      };
    } else if (score >= 55 && score <= 125) {
      return {
        colorClass: "bg-emerald-100 text-emerald-600 border-emerald-200",
        desc: `Selalu pilih makanan sehat seperti ini juga ya agar tubuhmu tumbuh kuat! 💪`
      };
    } else {
      return {
        colorClass: "bg-yellow-100 text-amber-700 border-yellow-300",
        desc: `Keranjangmu bersih total dari jajanan tidak sehat! 🥦`,
        shopLink: false
      };
    }
  };

  const evalInfo = getEvaluationContent();

  return (
    <div className="min-h-screen bg-bg-main flex flex-col font-sans selection:bg-brand-coral/20">
      <main className="grow w-full max-w-4xl mx-auto px-4 py-6 md:py-8 flex flex-col">
            <motion.div
              key="tab-game"
              initial={{ opacity: 0, scale: 0.98 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.98 }}
              className="flex flex-col items-center select-none"
            >
              
              {/* PANEL INFORMASI GAMEPLAY (BAGIAN ATAS - Di Luar Kotak Game Utama) */}
              <div className="w-full max-w-4xl flex flex-col gap-4 mb-4">
                
                {/* Row 1: Logo & Sound Toggle */}
                <div className="w-full max-w-4xl flex items-center justify-between mb-4 mt-2">

                  {/* Brand Logo Sobat Anak Image */}
                  <div className="flex items-center gap-4">
                    <img
                      src="/logo sobat anak.png"
                      alt="Sobat Anak"
                      className="h-16 w-auto object-contain shrink-0"
                    />

                    {/* Orange Vertical Divider */}
                    <div className="h-10 w-[2.5px] bg-brand-orange/80 shrink-0"></div>

                    <span
                      className="text-sm font-black whitespace-nowrap"
                      style={{ color: "#1e2939", fontWeight: 900 }}
                    >
                      Mom & Baby Care
                    </span>
                  </div>

                  {/* Mute Toggle Button */}
                  <button
                    onClick={toggleMute}
                    className={`p-3 rounded-2xl border-2 border-white shadow-sm transition-all duration-300 hover:scale-105 active:scale-95 flex items-center justify-center cursor-pointer ${
                      !isMuted ? "bg-orange-100 text-orange-700" : "bg-gray-200 text-gray-500"
                    }`}
                    title={!isMuted ? "Matikan Suara" : "Aktifkan Suara"}
                  >
                    {!isMuted ? (
                      <Volume2 size={20} className="animate-pulse" />
                    ) : (
                      <VolumeX size={20} />
                    )}
                  </button>

                </div>
                {/* Row 2: Three dynamic child-friendly panels */}
                <section className="w-full max-w-4xl mt-1 mb-4 md:mb-6 grid grid-cols-3 gap-2.5 md:gap-5">
                  {/* Timer Card */}
                  {/* Timer Card */}
                  <div className="bg-white border-4 border-orange-500 rounded-3xl p-3 md:p-4 flex items-center justify-center md:justify-start shadow-lg relative h-16 md:h-28 select-none transition-all duration-200 hover:scale-[1.02]">
                    <div className="flex items-center space-x-1.5 md:space-x-3.5">
                      <div className="w-8 h-8 md:w-14 md:h-14 rounded-2xl flex items-center justify-center border-2 bg-[#FFF0E6] border-[#FDBA74] shrink-0">
                        <Timer className="w-4 h-4 md:w-7 md:h-7 text-brand-orange" strokeWidth={2.5} />
                      </div>

                      <div>
                        <p className="text-[10px] md:text-[11px] font-extrabold uppercase tracking-wider md:tracking-widest leading-none text-brand-orange">
                          Waktu Tersisa
                        </p>

                        <div className="flex items-baseline mt-0.5 md:mt-1">
                          <span className="text-2xl md:text-5xl font-black leading-none text-brand-orange font-display">
                            {timeLeft}
                          </span>
                          <span className="text-sm md:text-xl font-black ml-1 md:ml-2 leading-none text-brand-orange font-display">
                            detik
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Center Mini Game Banner */}
                  <div className="bg-blue-500 text-white rounded-3xl p-3 md:p-4 flex flex-col justify-center items-center text-center shadow-lg border-4 border-white transform hover:scale-[1.02] transition-transform h-16 md:h-28">
                    <p className="text-[10px] font-bold tracking-widest uppercase text-blue-100 leading-3">
                      SOBAT ANAK
                    </p>
                    <h3 className="text-lg md:text-xl font-black tracking-tight uppercase leading-none font-display">
                      MINI GAME
                    </h3>
                  </div>

                  {/* Score Card */}
                  <div className="bg-white border-4 border-blue-500 rounded-3xl p-3 md:p-4 flex items-center justify-between shadow-lg relative h-16 md:h-28 select-none transition-transform duration-200 hover:scale-[1.02]">
                    <div className="flex items-center space-x-2 md:space-x-4">
                      <div className="w-9 h-9 md:w-14 md:h-14 bg-blue-100 rounded-2xl flex items-center justify-center border-2 border-blue-200 shrink-0">
                        <Trophy className="w-5 h-5 md:w-7 md:h-7 text-blue-500" strokeWidth={2.8} />
                      </div>

                      <div>
                        <p className="text-[10px] md:text-sm font-black text-blue-500 uppercase tracking-widest leading-none">
                          Skor Kamu
                        </p>

                        <span className="text-2xl md:text-5xl font-black text-blue-500 leading-none mt-1 block font-display">
                          {score}
                        </span>
                    </div>
                  </div>

                    <div className="absolute bottom-1.5 right-2 md:bottom-3 md:right-4 leading-none">
                      <p className="text-[8px] md:text-xs font-black text-blue-500 uppercase tracking-wide leading-none">
                        <span className="hidden sm:inline">Rekor: </span>{highScore}
                      </p>
                    </div>
                  </div>

</section>

              </div>

              {/* KOTAK GAME UTAMA (BAGIAN TENGAH - Responsive & Absolute Overflow-Hidden Window) */}
              <div
                ref={containerRef}
                onMouseMove={handleMouseMove}
                onTouchMove={handleTouchMove}
                id="main-game-arena"
                className="w-full h-112.5 bg-[#EFF6FF] rounded-3xl border-8 border-white shadow-2xl relative overflow-hidden transition-all duration-300 game-shadow mx-auto"
                style={{ touchAction: "none" }}
              >
                
                {/* DEKORASI BACKGROUND PLAYGROUND ANAK */}
                <div className="absolute inset-0 bg-[#E0F2FE]/40 pointer-events-none">
                  {/* Langit Biru Cerah Lembut */}
                  <div className="absolute top-0 inset-x-0 h-40 bg-linear-to-b from-[#BAE6FD]/35 to-transparent"></div>
                  
                  {/* Grid Tekstur Tipis yang Ramah */}
                  <div className="absolute inset-0 bg-[radial-gradient(#4FC3F7_5%,transparent_5%)] bg-size[24px_24px] opacity-15"></div>
                  
                  {/* Ornamen Awan Melayang */}
                  <div className="absolute top-8 left-[12%] opacity-40 animate-pulse">
                    <div className="bg-white rounded-full w-20 h-8"></div>
                    <div className="bg-white rounded-full w-12 h-12 -mt-7 ml-4"></div>
                  </div>
                  <div className="absolute top-12 right-[18%] opacity-30">
                    <div className="bg-white rounded-full w-24 h-9"></div>
                    <div className="bg-white rounded-full w-14 h-14 -mt-10 ml-6"></div>
                  </div>

                  {/* Ornamen Matahari Ceria */}
                  <div className="absolute top-6 right-8 text-amber-300 opacity-60">
                    <Sun className="w-10 h-10 animate-spin" style={{ animationDuration: "14s" }} />
                  </div>

                  {/* Ornamen Rumput Hijau Lembut di Dasar */}
                  <div className="absolute bottom-0 inset-x-0 h-4 bg-brand-mint/45 border-t border-brand-mint/20"></div>
                </div>

                <AnimatePresence mode="wait">
                  
                  {/* STATE 1: START SCREEN */}
                  {gameState === "start" && (
                    <motion.div
                      key="screen-start"
                      initial={{ opacity: 0, scale: 0.96 }}
                      animate={{ opacity: 1, scale: 1 }}
                      exit={{ opacity: 0 }}
                      className="absolute inset-0 z-10 flex flex-col items-center justify-center p-4 md:p-8 text-center bg-white/70 backdrop-blur-[2px]"
                    >
                      {/* Playful Basket Orbit Animation */}
                      <div className="basket-start-stage mb-5 md:mb-6">
                        {/* Orbit icons */}
                        <div className="orbit-ring orbit-ring-one">
                          <img src="/apel.png" alt="Apel" className="orbit-item orbit-item-1" />
                          <img src="/wortel.png" alt="Wortel" className="orbit-item orbit-item-2" />
                          <img src="/susu.png" alt="Susu" className="orbit-item orbit-item-3" />
                        </div>

                        <div className="orbit-ring orbit-ring-two">
                          <img src="/permen.png" alt="Permen" className="orbit-item orbit-item-4" />
                          <img src="/donat.png" alt="Donat" className="orbit-item orbit-item-5" />
                          <img src="/soda.png" alt="Soda" className="orbit-item orbit-item-6" />
                        </div>

                      {/* Character Basket - disamakan dengan basket gameplay */}
                      <div className="basket-character">
                        <svg
                          viewBox="0 0 120 80"
                          className="basket-svg"
                        >
                          <defs>
                            <linearGradient id="start-basket-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                              <stop offset="0%" stopColor="#FFAD90" />
                              <stop offset="100%" stopColor="#FF8A65" />
                            </linearGradient>
                          </defs>

                          {/* Basket Rim */}
                          <rect
                            x="5"
                            y="15"
                            width="110"
                            height="12"
                            rx="6"
                            fill="#F97316"
                            stroke="#FFFFFF"
                            strokeWidth="3"
                          />

                          {/* Basket Woven Body */}
                          <path
                            d="M15 27 L25 72 C26 75, 30 78, 35 78 L85 78 C90 78, 94 75, 95 72 L105 27 Z"
                            fill="url(#start-basket-grad)"
                            stroke="#FFFFFF"
                            strokeWidth="3"
                          />

                          {/* Grid Patterns */}
                          <path
                            d="M30 27 L40 78 M50 27 L52 78 M70 27 L68 78 M90 27 L80 78"
                            stroke="#FFFFFF"
                            strokeWidth="2"
                            opacity="0.3"
                          />
                          <path
                            d="M18 38 L102 38 M21 52 L99 52 M24 66 L96 66"
                            stroke="#FFFFFF"
                            strokeWidth="2"
                            opacity="0.3"
                          />

                          {/* Smile Face */}
                          <circle cx="45" cy="48" r="4.5" fill="#1E2939" />
                          <circle cx="75" cy="48" r="4.5" fill="#1E2939" />
                          <circle cx="37" cy="53" r="3.5" fill="#EF4444" opacity="0.4" />
                          <circle cx="83" cy="53" r="3.5" fill="#EF4444" opacity="0.4" />
                          <path
                            d="M56 50 Q60 55, 64 50"
                            fill="none"
                            stroke="#1E2939"
                            strokeWidth="3"
                            strokeLinecap="round"
                          />
                        </svg>
                      </div>

                      </div> 
                      
                      <h1 className="text-3xl md:text-5xl font-black font-display text-slate-800 tracking-tight leading-tight mb-4 uppercase">
                        Keranjang Sehat
                      </h1>

                      <p className="text-slate-600 font-bold text-sm md:text-base mb-6 md:mb-8 max-w-md leading-relaxed">
                        Tangkap makanan sehat seperti apel, wortel, dan susu. Hindari permen, donat, dan soda agar skormu tetap tinggi!
                      </p>

                      <button
                        onClick={handleStartGame}
                        className="bg-green-500 hover:bg-green-600 text-white text-lg md:text-2xl font-black font-display px-8 md:px-12 py-3 md:py-4 rounded-full shadow-[0_6px_0_0_#166534] active:shadow-none active:translate-y-1 transition-all uppercase tracking-widest cursor-pointer flex items-center gap-2 md:gap-3 hover:scale-105 active:scale-95 duration-150"
                      >
                        <Play size={24} fill="currentColor" />
                        Mulai Game
                      </button>

                      <p className="text-[10px] md:text-xs text-slate-400 font-black uppercase tracking-wider mt-5">
                        Gunakan Mouse (PC) / Geser Sentuh (Mobile)
                      </p>
                    </motion.div>
                  )}

                  {/* STATE 2: GAMEPLAY SCREEN */}
                  {gameState === "playing" && (
                    <div key="screen-play" className="absolute inset-0">
                      
                      {/* Petunjuk Awal Layau */}
                      <div className="absolute top-4 left-4 z-10 p-2 pointer-events-none transition-opacity duration-300 max-w-42.5 hidden sm:block">
                        <p className="text-[10px] text-brand-coral font-bold uppercase tracking-wider leading-none">Healthy (+10)</p>
                        <p className="text-[9px] text-slate-500 font-semibold mt-1">Apel, Wortel, Susu 🥛</p>
                        <p className="text-[10px] text-red-500 font-bold uppercase tracking-wider leading-none mt-2">Avoid (-5)</p>
                        <p className="text-[9px] text-slate-500 font-semibold mt-1">Permen, Donat, Soda 🥤</p>
                      </div>

                      {/* Visual Feedback Pop-Up Tumbukan (lastCrash) */}
                      {lastCrash && (
                        <div
                          className="absolute pointer-events-none animate-ping z-10 text-center font-display text-lg"
                          style={{
                            left: `${lastCrash.x}%`,
                            top: `${lastCrash.y}%`,
                            transition: "all 0.1s ease-out"
                          }}
                        >
                          <span className={`bg-white border-2 border-slate-100 rounded-full px-3 py-1 shadow-md ${lastCrash.color}`}>
                            {lastCrash.text}
                          </span>
                        </div>
                      )}

                      {/* AREA JATUH OBJEK */}
                      {items.map((item) => (
                        <div
                          key={item.id}
                          className="absolute pointer-events-none flex flex-col items-center"
                          style={{
                            left: `${item.x}%`,
                            top: `${item.y}px`,
                            width: "60px",
                            height: "60px"
                          }}
                        >
                          <div className="w-14 h-14 flex items-center justify-center item-bounce">
                            <img
                              src={item.img}
                              alt={item.name}
                              onError={(e) => {
                                e.currentTarget.src = item.fallbackSvg;
                              }}
                              className="w-14 h-14 object-contain filter drop-shadow-[0_4px_4px_rgba(0,0,0,0.15)]"
                              referrerPolicy="no-referrer"
                            />
                          </div>
                        </div>
                      ))}

                      {/* CHARACTER: KERANJANG BELANJA (Memanfaatkan Pointer/Touch Events) */}
                      <div
                        className="absolute cursor-grab active:cursor-grabbing w-[16%] h-13.75 bottom-3.75 z-10 select-none"
                        style={{
                          left: `${basketX}%`
                        }}
                      >
                        {/* Woven Basket Drawing */}
                        <svg viewBox="0 0 120 80" className="w-full h-full filter drop-shadow-[0_6px_6px_rgba(0,0,0,0.16)]">
                          <defs>
                            <linearGradient id="basket-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                              <stop offset="0%" stop-color="#FFAD90" />
                              <stop offset="100%" stop-color="#FF8A65" />
                            </linearGradient>
                          </defs>
                          {/* Basket Rim */}
                          <rect x="5" y="15" width="110" height="12" rx="6" fill="#F97316" stroke="#FFFFFF" stroke-width="3" />
                          {/* Basket Woven Body */}
                          <path d="M15 27 L25 72 C26 75, 30 78, 35 78 L85 78 C90 78, 94 75, 95 72 L105 27 Z" fill="url(#basket-grad)" stroke="#FFFFFF" stroke-width="3" />
                          {/* Grid patterns */}
                          <path d="M30 27 L40 78 M50 27 L52 78 M70 27 L68 78 M90 27 L80 78" stroke="#FFFFFF" stroke-width="2" opacity="0.3" />
                          <path d="M18 38 L102 38 M21 52 L99 52 M24 66 L96 66" stroke="#FFFFFF" stroke-width="2" opacity="0.3" />
                          {/* Smile Face */}
                          <circle cx="45" cy="48" r="4.5" fill="#1E2939" />
                          <circle cx="75" cy="48" r="4.5" fill="#1E2939" />
                          <circle cx="37" cy="53" r="3.5" fill="#EF4444" opacity="0.4" />
                          <circle cx="83" cy="53" r="3.5" fill="#EF4444" opacity="0.4" />
                          <path d="M56 50 Q60 55, 64 50" fill="none" stroke="#1E2939" stroke-width="3" stroke-linecap="round" />
                        </svg>
                      </div>

                    </div>
                  )}

                  {/* STATE 3: END STATE SCREEN */}
                  {gameState === "end" && (
                    <motion.div
                      key="screen-end"
                      initial={{ opacity: 0, scale: 0.95 }}
                      animate={{ opacity: 1, scale: 1 }}
                      exit={{ opacity: 0 }}
                      className="absolute inset-0 z-20 flex flex-col items-center justify-center p-6 bg-white text-center"
                    >
                      {/* Badge Game Selesai Kuning */}
                      <div className="bg-brand-yellow text-white font-display text-sm md:text-base font-black px-7 py-3 rounded-full shadow-[0_5px_0_0_#FFB300] tracking-widest uppercase mb-6 -rotate-1 inline-block select-none">
                        GAME SELESAI!
                      </div>

                      {/* Skor Akhir dengan Text Biru dan Garis Gelombang Pas */}
                      <h3 className="font-display text-3xl md:text-5xl font-black text-text-primary mb-5 flex items-center justify-center gap-2 select-none">
                        <span>Skor Akhir:</span>
                        <div className="relative inline-block px-1 select-none">
                          <span className="text-[#2B82F6] font-display text-4xl md:text-6xl font-black">
                            {score}
                          </span>
                          <svg className="absolute top-[85%] left-0 w-full h-3 overflow-visible pointer-events-none" viewBox="0 0 100 12" preserveAspectRatio="none">
                            <path d="M 0,6 Q 12.5,0 25,6 T 50,6 T 75,6 T 100,6" fill="none" stroke="#2B82F6" strokeWidth="4.5" strokeLinecap="round" />
                            <path d="M 0,11 Q 12.5,5 25,11 T 50,11 T 75,11 T 100,11" fill="none" stroke="#60A5FA" strokeWidth="3" strokeLinecap="round" opacity="0.6" />
                          </svg>
                        </div>
                      </h3>

                      {/* Kutipan */}
                      <div className="max-w-xl mb-10 text-center space-y-3 select-none">

                        <p className="text-base md:text-lg text-slate-700 font-sans font-bold italic leading-relaxed">
                          "{evalInfo.desc}"
                        </p>
                      </div>

                      {/* Main Lagi (Dengan Ikon Restart Spinning Berkelanjutan) */}
                      <button
                        onClick={handleRestart}
                        className="bg-[#00D056] hover:bg-[#00B94C] active:translate-y-1 active:shadow-none hover:scale-105 text-white text-xl md:text-2xl font-black font-display px-12 py-4.5 rounded-full shadow-[0_6px_0_0_#059669] transition-all uppercase tracking-widest flex items-center gap-3 cursor-pointer select-none"
                      >
                        <RotateCcw className="w-6 h-6 spin-slow shrink-0" />
                        <span>MAIN LAGI</span>
                      </button>

                    </motion.div>
                  )}

                </AnimatePresence>

              </div>

              {/* AREA KHUSUS "TIPS UNTUK ORANG TUA" (BAGIAN BAWAH - Di Luar Kotak Game Utama) */}
              <section className="w-full max-w-4xl mt-8 select-none animate-fade-in">
                <div className="bg-white rounded-3xl border-4 border-orange-200 p-8 shadow-xl flex flex-col md:flex-row items-start gap-6">
                  
                  {/* Icon Info */}
                  <div className="bg-orange-500 p-4 rounded-2xl text-white shadow-md shrink-0 flex items-center justify-center">
                    <svg
                      className="w-10 h-10"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2.5"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>

                  {/* Isi Tips */}
                  <div className="flex-1">
                    <h3 className="text-orange-600 font-black uppercase text-base tracking-wider mb-3">
                      Tips Penting Untuk Orang Tua
                    </h3>

                    <div className="space-y-4 text-slate-700 leading-relaxed font-semibold text-sm">
                      <p>
                        💡 <span className="text-slate-800 font-extrabold">Sajikan dengan Bentuk Lucu:</span> Anak-anak sangat responsif terhadap estetika visual makanan mereka. Cobalah menggunakan pencetak buah berbentuk hewan imut atau potongan karakter unik kesukaan si Kecil.
                      </p>

                      <p>
                        🥦 <span className="text-slate-800 font-extrabold">Biasakan Makan Sehat:</span> Ajak si Kecil mengenal buah, sayur, dan susu sebagai pilihan makanan bergizi agar tubuhnya tumbuh kuat, aktif, dan ceria setiap hari.
                      </p>

                      <p>
                        🏠 <span className="text-slate-800 font-extrabold">Momen Emas:</span> Libatkan anak saat memilih buah atau sayur di rumah maupun pasar agar mereka merasa senang dan lebih tertarik mencoba makanan sehat.
                      </p>
                    </div>
                  </div>
                </div>
              </section>
            </motion.div>
      </main>
    </div>
  );
}
