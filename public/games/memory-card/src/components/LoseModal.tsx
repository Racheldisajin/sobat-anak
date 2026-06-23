import type { Level } from '../types/game';

interface LoseModalProps {
  level: Level;
  score: number;
  moves: number;
  onBack: () => void;
  onRestart: () => void;
}

export function LoseModal({ score, moves, onBack, onRestart }: LoseModalProps) {
  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div className="bg-gradient-to-br from-red-400 via-rose-400 to-red-500 rounded-3xl shadow-2xl p-6 md:p-8 max-w-md w-full animate-pop-in border-4 border-white/50 relative overflow-hidden">
        {/* Decoration */}
        <div className="absolute -top-4 left-1/4 text-3xl animate-pulse">💔</div>
        <div className="absolute -top-4 right-1/4 text-3xl animate-pulse delay-500">❌</div>

        {/* Main content */}
        <div className="text-center relative z-10">
          {/* Logo Sobat Anak */}
          <div className="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden shadow-xl flex items-center justify-center bg-white p-1 border-2 border-white/50 mx-auto mb-3">
            <img 
              src={import.meta.env.BASE_URL + "logo-sobat-anak.png"} 
              alt="Logo Sobat Anak" 
              className="w-full h-full object-contain"
            />
          </div>
          <div className="text-6xl md:text-7xl mb-3 animate-pulse">💪</div>
          <h2 className="text-2xl md:text-3xl font-bold text-white drop-shadow-md mb-2">
            Waktu Habis!
          </h2>
          <p className="text-white/90 text-lg md:text-xl mb-5">
            Jangan menyerah, coba lagi!
          </p>

          {/* Stats */}
          <div className="bg-white/20 backdrop-blur-sm rounded-2xl p-4 md:p-5 mb-5">
            <div className="grid grid-cols-2 gap-3 md:gap-4">
              <div className="bg-white/20 rounded-xl p-3">
                <p className="text-white/80 text-xs uppercase">Skor</p>
                <p className="text-2xl md:text-3xl font-bold text-white">{score}</p>
              </div>
              <div className="bg-white/20 rounded-xl p-3">
                <p className="text-white/80 text-xs uppercase">Langkah</p>
                <p className="text-2xl md:text-3xl font-bold text-white">{moves}</p>
              </div>
            </div>
          </div>

          {/* Encouraging message */}
          <div className="bg-white/10 rounded-xl p-3 mb-5">
            <p className="text-white text-sm md:text-base">
              🌟 Kamu hampir sampai! Ayo coba sekali lagi!
            </p>
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
              className="flex-1 py-3 px-4 bg-white text-red-600 rounded-xl font-bold hover:bg-white/90 transition-colors shadow-lg text-sm md:text-base"
            >
              Ulangi!
            </button>
          </div>
        </div>
      </div>

      <style>{`
        @keyframes pop-in {
          0% { transform: scale(0.8); opacity: 0; }
          100% { transform: scale(1); opacity: 1; }
        }
        .animate-pop-in { animation: pop-in 0.4s ease-out; }
        .delay-500 { animation-delay: 0.5s; }
      `}</style>
    </div>
  );
}
