import { useEffect, useState } from 'react';
import type { Level } from '../types/game';

interface WinModalProps {
  level: Level;
  score: number;
  moves: number;
  timeLeft: number;
  onBack: () => void;
  onRestart: () => void;
}

export function WinModal({ score, moves, timeLeft, onBack, onRestart }: WinModalProps) {
  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;
  const timeDisplay = `${minutes}:${seconds.toString().padStart(2, '0')}`;
  const timeBonus = timeLeft * 10;
  const totalScore = score + timeBonus;
  const [stars, setStars] = useState<{ id: number; x: number; delay: number }[]>([]);

  useEffect(() => {
    const generatedStars = Array.from({ length: 20 }, (_, i) => ({
      id: i,
      x: Math.random() * 100,
      delay: Math.random() * 2,
    }));
    setStars(generatedStars);
  }, []);

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      {/* Animated stars background */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        {stars.map((star) => (
          <div
            key={star.id}
            className="absolute text-2xl animate-fall"
            style={{
              left: `${star.x}%`,
              animationDelay: `${star.delay}s`,
            }}
          >
            ⭐
          </div>
        ))}
      </div>

      <div className="bg-gradient-to-br from-green-400 via-emerald-400 to-green-500 rounded-3xl shadow-2xl p-6 md:p-8 max-w-md w-full animate-pop-in border-4 border-white/50 relative overflow-hidden">
        {/* Confetti decoration */}
        <div className="absolute -top-4 left-1/4 text-4xl animate-bounce-slow">🎉</div>
        <div className="absolute -top-4 right-1/4 text-4xl animate-bounce-slow delay-500">🎊</div>
        <div className="absolute top-1/2 -left-4 text-3xl animate-wiggle">✨</div>
        <div className="absolute top-1/2 -right-4 text-3xl animate-wiggle delay-300">✨</div>

        {/* Main content */}
        <div className="text-center relative z-10">
          {/* Logo Sobat Anak */}
          <div className="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden shadow-xl flex items-center justify-center bg-white p-1 border-2 border-white/50 mx-auto mb-3">
            <img 
              src="/logo-sobat-anak.png" 
              alt="Logo Sobat Anak" 
              className="w-full h-full object-contain"
            />
          </div>
          <div className="text-6xl md:text-7xl mb-3 animate-bounce-slow">🎉</div>
          <h2 className="text-2xl md:text-3xl font-bold text-white drop-shadow-md mb-2">
            Selamat!
          </h2>
          <p className="text-white/90 text-lg md:text-xl mb-5">
            Kamu berhasil menyelesaikan game!
          </p>

          {/* Stats */}
          <div className="bg-white/20 backdrop-blur-sm rounded-2xl p-4 md:p-5 mb-5">
            <div className="grid grid-cols-2 gap-3 md:gap-4">
              <div className="bg-white/20 rounded-xl p-3">
                <p className="text-white/80 text-xs uppercase">Total Skor</p>
                <p className="text-2xl md:text-3xl font-bold text-white">{totalScore}</p>
              </div>
              <div className="bg-white/20 rounded-xl p-3">
                <p className="text-white/80 text-xs uppercase">Langkah</p>
                <p className="text-2xl md:text-3xl font-bold text-white">{moves}</p>
              </div>
              <div className="bg-white/20 rounded-xl p-3 col-span-2">
                <p className="text-white/80 text-xs uppercase">Sisa Waktu</p>
                <p className="text-2xl md:text-3xl font-bold text-white">{timeDisplay}</p>
                <p className="text-white/70 text-xs mt-1">+{timeBonus} poin bonus!</p>
              </div>
            </div>
          </div>

          {/* Stars rating */}
          <div className="flex justify-center gap-1 mb-5">
            {[1, 2, 3].map((star) => (
              <span
                key={star}
                className="text-4xl md:text-5xl animate-star-pop"
                style={{ animationDelay: `${star * 0.2}s` }}
              >
                ⭐
              </span>
            ))}
          </div>

          {/* Buttons */}
          <div className="flex gap-3">
            <button
              onClick={onBack}
              className="flex-1 py-3 px-4 bg-white/20 backdrop-blur-sm text-white rounded-xl font-bold hover:bg-white/30 transition-colors border-2 border-white/30 text-sm md:text-base"
            >
              Kembali
            </button>
            <button
              onClick={onRestart}
              className="flex-1 py-3 px-4 bg-white text-green-600 rounded-xl font-bold hover:bg-white/90 transition-colors shadow-lg text-sm md:text-base"
            >
              Siap Lagi!
            </button>
          </div>
        </div>
      </div>

      <style>{`
        @keyframes pop-in {
          0% { transform: scale(0.8); opacity: 0; }
          100% { transform: scale(1); opacity: 1; }
        }
        @keyframes bounce-slow {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-10px); }
        }
        @keyframes wiggle {
          0%, 100% { transform: rotate(-5deg); }
          50% { transform: rotate(5deg); }
        }
        @keyframes fall {
          0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
          100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
        }
        @keyframes star-pop {
          0% { transform: scale(0); }
          50% { transform: scale(1.3); }
          100% { transform: scale(1); }
        }
        .animate-pop-in { animation: pop-in 0.4s ease-out; }
        .animate-bounce-slow { animation: bounce-slow 1s ease-in-out infinite; }
        .animate-wiggle { animation: wiggle 0.5s ease-in-out infinite; }
        .animate-fall { animation: fall 3s linear infinite; }
        .animate-star-pop { animation: star-pop 0.5s ease-out both; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-500 { animation-delay: 0.5s; }
      `}</style>
    </div>
  );
}
