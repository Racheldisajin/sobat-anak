import { useEffect, useRef, useState } from 'react';
import { GameState } from './types';
import { StartScreen } from './components/StartScreen';
import { GameplayScreen } from './components/GameplayScreen';
import { EndScreen } from './components/EndScreen';
import { ParentTipsSection } from './components/ParentTipsSection';
import { Timer, Trophy, Volume2, VolumeX } from 'lucide-react';
import { setMutedGlobal, playTriumphFanfare, playBubblePopSound } from './data';

export default function App() {
  const [gameState, setGameState] = useState<GameState>('START');
  const [score, setScore] = useState<number>(0);
  const [timeLeft, setTimeLeft] = useState<number>(30);
  const [cleanPercentage, setCleanPercentage] = useState<number>(0);
  const [isMuted, setIsMuted] = useState<boolean>(false);
  const bgmRef = useRef<HTMLAudioElement | null>(null);
  
  const [highScore, setHighScore] = useState<number>(() => {
    try {
      const stored = localStorage.getItem('sobat_anak_high_score');
      return stored ? parseInt(stored, 10) : 49;
    } catch {
      return 49;
    }
  });

  useEffect(() => {
  const bgm = new Audio(import.meta.env.BASE_URL + 'audio/backsound-sikat-gigi.mp3');
  bgm.loop = true;
  bgm.volume = 0.25;
  bgm.muted = isMuted;

  bgmRef.current = bgm;

  return () => {
    bgm.pause();
    bgm.currentTime = 0;
  };
}, []);

useEffect(() => {
  if (bgmRef.current) {
    bgmRef.current.muted = isMuted;
  }
}, [isMuted]);

  // Toggle mute sound
const toggleMute = () => {
  setIsMuted((prev) => {
    const newVal = !prev;
    setMutedGlobal(newVal);

    if (bgmRef.current) {
      bgmRef.current.muted = newVal;

      if (newVal) {
        bgmRef.current.pause();
      } else {
        playBacksound(true);
      }
    }

    return newVal;
  });
};

  // Sync clean percentage with high score dynamically
  const handleCleanPercentageChange = (pct: number) => {
    setCleanPercentage(pct);
    if (pct > highScore) {
      setHighScore(pct);
      try {
        localStorage.setItem('sobat_anak_high_score', pct.toString());
      } catch (e) {
        // ignore
      }
    }
  };

  const isTimeLow = timeLeft <= 7 && gameState === 'GAMEPLAY';

  //play stop backsund
const playBacksound = (forcePlay = false) => {
  if (!bgmRef.current) return;
  if (!forcePlay && isMuted) return;

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

  // Mulai game
const handleStartGame = () => {
  playBubblePopSound();

  if (bgmRef.current) {
    bgmRef.current.currentTime = 0;
  }

  playBacksound(true);

  setScore(0);
  setCleanPercentage(0);
  setTimeLeft(30);
  setGameState('GAMEPLAY');
};

  // Game berakhir (Waktu habis / Bersih 100%)
  const handleGameEnd = (finalScore: number) => {
    setScore(finalScore);
    playTriumphFanfare();
    setGameState('END');
  };

  // Ulangi bermain
  const handleRestartGame = () => {
    playBubblePopSound();

    if (bgmRef.current) {
      bgmRef.current.currentTime = 0;
    }

    playBacksound(true);

    setScore(0);
    setCleanPercentage(0);
    setTimeLeft(30);
    setGameState('GAMEPLAY');
  };

  return (
    <div className="min-h-screen bg-[#FFF7ED] text-[#1E2939] pb-10 px-4 flex flex-col items-center">
      
      {/* HEADER UTAMA WEBSITE - Frameless Elegant Layout matching the design system */}
      <div className="w-full max-w-4xl flex items-center justify-between mb-4 mt-2">
        
      {/* Brand Logo Sobat Anak Image */}
      <div className="flex items-center gap-4">
        <img
          src={import.meta.env.BASE_URL + "logo sobat anak.png"}
          alt="Sobat Anak"
          className="h-16 w-auto object-contain shrink-0"
        />

        {/* Orange Vertical Divider */}
        <div className="h-10 w-[2.5px] bg-[#F97316]/80 shrink-0"></div>

        <span className="text-sm font-black whitespace-nowrap"
              style={{ color: "#1e2939" }}>
          Mom & Baby Care
        </span>
      </div>

        {/* Mute Toggle Button */}
        <div className="flex items-center gap-2">
  <button 
    id="toggle_sound_btn"
    onClick={toggleMute}
    className={`p-3 rounded-2xl border-2 border-white shadow-sm transition-all duration-300 hover:scale-105 active:scale-95 flex items-center justify-center cursor-pointer ${!isMuted ? 'bg-orange-100 text-orange-700' : 'bg-gray-200 text-gray-500'}`}
    title={!isMuted ? "Matikan Suara" : "Aktifkan Suara"}
  >
    {!isMuted ? (
      <Volume2 size={20} className="animate-pulse" />
    ) : (
      <VolumeX size={20} />
    )}
  </button>
</div>

      </div>

      {/* 1. BAGIAN ATAS: INFORMASI WAKTU, JUDUL & SKOR (Precision Symmetrical 3-Card Bento Layout) */}
      <section className="w-full max-w-4xl mt-1 mb-4 md:mb-6 px-1 grid grid-cols-3 gap-2.5 md:gap-5">
        
        {/* Card 1: Timer countdown - Dynamic Red/Orange and Shaking */}
        <div className={`bg-white border-2 md:border-3 rounded-2xl md:rounded-3xl p-2 md:p-4 flex items-center justify-center md:justify-start shadow-lg relative h-16 md:h-28 select-none transition-all duration-200 ${
          isTimeLow 
            ? 'border-red-500 animate-shake-timer bg-red-50/25' 
            : 'border-orange-500 hover:scale-[1.01]'
        }`}>
          <div className="flex items-center space-x-1.5 md:space-x-3.5">
            <div className={`w-8 h-8 md:w-14 md:h-14 rounded-xl md:rounded-2xl flex items-center justify-center border shrink-0 transition-colors ${
              isTimeLow 
                ? 'bg-red-100 border-red-350' 
                : 'bg-[#FFF0E6] border-[#FDBA74] md:border-comic-thin'
            }`}>
              <Timer className={`w-4 h-4 md:w-7 md:h-7 transition-colors ${isTimeLow ? 'text-[#EF4444] animate-pulse' : 'text-[#F97316]'}`} strokeWidth={2.5} />
            </div>
            <div>
              <p className={`font-quicksand text-[8px] md:text-[11px] font-extrabold uppercase tracking-wider md:tracking-widest leading-none transition-colors ${
                isTimeLow ? 'text-[#EF4444]' : 'text-[#F97316]'
              }`}>
                Waktu
              </p>
              <div className="flex items-baseline mt-0.5 md:mt-1">
                <span className={`font-fredoka text-2xl md:text-5xl font-black leading-none transition-colors ${
                isTimeLow ? 'text-[#EF4444]' : 'text-[#F97316]'
              }`}>
                {timeLeft}
              </span>
              <span className={`font-fredoka text-sm md:text-xl font-black ml-1 md:ml-2 leading-none transition-colors ${
                isTimeLow ? 'text-[#EF4444]' : 'text-[#F97316]'
              }`}>
                detik
              </span>
              </div>
            </div>
          </div>
        </div>

      {/* Card 2: Center Sobat Anak mini game banner - Synced with Tap Tap Kuman */}
      <div className="bg-blue-500 text-white rounded-3xl p-4 flex flex-col justify-center items-center text-center shadow-lg border-4 border-white transform hover:scale-[1.02] transition-transform h-16 md:h-28">
        <p className="text-[10px] font-bold tracking-widest uppercase text-blue-100 leading-3">
          SOBAT ANAK
        </p>
        <h3 className="text-lg md:text-xl font-black tracking-tight uppercase leading-none">
          MINI GAME
        </h3>
      </div>

        {/* Card 3: Score display with highscore tracking - Blue border */}
      <div className="bg-white border-4 border-blue-500 rounded-3xl p-3 md:p-4 flex items-center justify-between shadow-lg relative h-16 md:h-28 select-none transition-transform duration-200 hover:scale-[1.02]">
        <div className="flex items-center space-x-2 md:space-x-4">
          <div className="w-9 h-9 md:w-14 md:h-14 bg-blue-100 rounded-2xl flex items-center justify-center border-2 border-blue-200 shrink-0">
            <Trophy className="w-5 h-5 md:w-7 md:h-7 text-blue-500" strokeWidth={2.8} />
          </div>

          <div>
            <p className="text-[10px] md:text-sm font-black text-blue-500 uppercase tracking-widest leading-none">
              Skor
            </p>

            <span className="text-2xl md:text-5xl font-black text-blue-500 leading-none mt-1 block">
              {cleanPercentage}
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

      {/* 2. BAGIAN TENGAH: KOTAK GAME UTAMA (Elegant White Border with Inside Inset Shadow) */}
      <main className="w-full max-w-4xl bg-white border-8 border-white rounded-3xl p-5 md:p-8 shadow-2xl relative">
        


        {/* Outer State Controller Viewport - Removed duplicate layers / border wrapper */}
        <div className="w-full min-h-107.5 flex flex-col justify-center relative overflow-hidden">
          {gameState === 'START' && (
            <StartScreen onStart={handleStartGame} />
          )}

          {gameState === 'GAMEPLAY' && (
            <GameplayScreen 
              onGameEnd={handleGameEnd} 
              setTimeLeftParent={setTimeLeft}
              setCleanPercentageParent={handleCleanPercentageChange}
            />
          )}

          {gameState === 'END' && (
            <EndScreen score={score} onRestart={handleRestartGame} />
          )}
        </div>

      </main>

      {/* 3. BAGIAN BAWAH: TIPS UNTUK ORANG TUA (Outside main frame) */}
      <section className="w-full max-w-4xl mt-8 select-none animate-fade-in">
        <ParentTipsSection />
      </section>

      {/* Child-friendly Footer credit credits */}
      <footer className="w-full max-w-4xl mt-8 text-center px-4">
        <p className="font-quicksand text-xs font-bold text-slate-400">
          &copy; {new Date().getFullYear()} Sobat Anak Parenting Hub. Dibuat khusus untuk mendukung tumbuh kembang anak secara interaktif dan menyenangkan. 🍊🦷🌱
        </p>
      </footer>

    </div>
  );
}
