import type { Level, ThemeName } from '../types/game';
import { themes, levelConfigs } from '../data/gameData';
import { Play } from 'lucide-react';

interface ReadyModalProps {
  level: Level;
  theme: ThemeName;
  onStart: () => void;
}

export function ReadyModal({ level, theme, onStart }: ReadyModalProps) {
  const themeData = themes[theme];
  const levelConfig = levelConfigs[level];

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div
        className="rounded-3xl shadow-2xl p-6 md:p-8 max-w-md w-full text-center animate-pop-in border-4 border-white/80"
        style={{ background: themeData.gradient }}
      >
        {/* Logo Sobat Anak */}
        <div className="w-12 h-12 md:w-16 md:h-16 rounded-full overflow-hidden shadow-xl flex items-center justify-center bg-white p-1 border-2 border-white/50 mx-auto mb-3">
          <img 
            src="/logo-sobat-anak.png" 
            alt="Logo Sobat Anak" 
            className="w-full h-full object-contain"
          />
        </div>
        <h3 className="text-2xl md:text-3xl font-bold text-white drop-shadow-md mb-3">
          Siap Main?
        </h3>

        <div className="bg-white/20 backdrop-blur-sm rounded-2xl p-4 md:p-5 mb-4">
          <div className="flex justify-center gap-3 mb-3">
            <img 
              src={themeData.items[0].image} 
              alt={themeData.items[0].name} 
              className="w-12 h-12 md:w-16 md:h-16 object-contain"
            />
            <img 
              src={themeData.items[1].image} 
              alt={themeData.items[1].name} 
              className="w-12 h-12 md:w-16 md:h-16 object-contain"
            />
            <img 
              src={themeData.items[2].image} 
              alt={themeData.items[2].name} 
              className="w-12 h-12 md:w-16 md:h-16 object-contain"
            />
          </div>
          <p className="text-white text-lg md:text-xl font-bold">
            {themeData.name} - {levelConfig.name}
          </p>
          <p className="text-white/80 mt-1 text-sm md:text-base">
            {levelConfig.cardCount} kartu dalam {levelConfig.gridCols}x{levelConfig.gridRows} grid
          </p>
        </div>

        <div className="bg-white/10 rounded-xl p-3 mb-5">
          <p className="text-white/90 text-sm md:text-base flex items-center justify-center gap-2">
            <span className="text-lg">⏱️</span>
            Waktu: 1 menit
          </p>
        </div>

        {/* Start Button */}
        <button
          onClick={onStart}
          className="w-full py-4 px-6 bg-white rounded-2xl font-bold text-lg md:text-xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95 flex items-center justify-center gap-3"
          style={{ color: themeData.gradient.includes('green') ? '#16a34a' : '#ea580c' }}
        >
          <Play className="w-6 h-6 md:w-7 md:h-7" />
          Mulai!
        </button>
      </div>

      <style>{`
        @keyframes countdown-pop {
          0% {
            transform: scale(1.5);
            opacity: 0;
          }
          50% {
            transform: scale(0.9);
          }
          100% {
            transform: scale(1);
            opacity: 1;
          }
        }
        .animate-countdown-pop {
          animation: countdown-pop 0.5s ease-out forwards;
        }
        @keyframes pop-in {
          0% {
            transform: scale(0.8);
            opacity: 0;
          }
          100% {
            transform: scale(1);
            opacity: 1;
          }
        }
        .animate-pop-in {
          animation: pop-in 0.3s ease-out forwards;
        }
      `}</style>
    </div>
  );
}
