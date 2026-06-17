import type { Level } from '../types/game';
import { levelConfigs } from '../data/gameData';
import { FloatingEmojis } from './FloatingEmojis';
import { SoundToggle } from './SoundToggle';
import { Sparkles, Star } from 'lucide-react';

interface LevelPageProps {
  onSelectLevel: (level: Level) => void;
  isBgMusicMuted: boolean;
  onToggleMusic: () => void;
}

export function LevelPage({ onSelectLevel, isBgMusicMuted, onToggleMusic }: LevelPageProps) {
  return (
    <div className="min-h-screen bg-[#FFF5E6] flex flex-col items-center justify-center p-4 relative overflow-hidden">
      <FloatingEmojis />
      <SoundToggle isMuted={isBgMusicMuted} onToggle={onToggleMusic} />

      <div className="relative z-10 w-full max-w-lg mx-auto">
        {/* Logo Sobat Anak */}
        <div className="flex justify-center mb-4 md:mb-6">
          <div className="relative">
            <div className="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden shadow-2xl flex items-center justify-center animate-bounce-slow bg-white p-2 border-4 border-amber-300">
              <img 
                src="/logo-sobat-anak.png" 
                alt="Logo Sobat Anak" 
                className="w-full h-full object-contain"
                onError={(e) => {
                  const target = e.target as HTMLImageElement;
                  target.style.display = 'none';
                  target.nextElementSibling!.classList.remove('hidden');
                }}
              />
              <span className="text-5xl md:text-6xl hidden">🃏</span>
            </div>
            <Sparkles className="absolute -top-2 -right-2 w-6 h-6 md:w-8 md:h-8 text-yellow-400 animate-pulse" />
            <Star className="absolute -bottom-1 -left-2 w-5 h-5 md:w-6 md:h-6 text-amber-400 animate-pulse delay-200" />
          </div>
        </div>

        {/* Title */}
        <h1 className="text-3xl md:text-5xl lg:text-6xl font-bold text-center mb-8 md:mb-10 text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 drop-shadow-lg">
          Memory Fun Cards
        </h1>

        {/* Level Buttons */}
        <div className="space-y-4 md:space-y-5">
          {(['easy', 'medium', 'hard'] as Level[]).map((level) => (
            <button
              key={level}
              onClick={() => onSelectLevel(level)}
              className="w-full py-4 md:py-5 px-6 md:px-8 rounded-2xl md:rounded-3xl text-white font-bold text-xl md:text-2xl shadow-lg hover:shadow-2xl transform transition-all duration-300 hover:scale-105 active:scale-95 border-4 border-white/30"
              style={{
                background: level === 'easy'
                  ? 'linear-gradient(135deg, #4ade80 0%, #22c55e 100%)'
                  : level === 'medium'
                  ? 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)'
                  : 'linear-gradient(135deg, #f87171 0%, #ef4444 100%)',
              }}
            >
              <div className="flex items-center justify-center gap-3">
                <span className="text-2xl md:text-3xl">
                  {level === 'easy' ? '' : level === 'medium' ? '' : ''}
                </span>
                <span>{levelConfigs[level].name}</span>
                <span className="text-sm md:text-base opacity-80">
                  ({levelConfigs[level].cardCount} kartu)
                </span>
              </div>
            </button>
          ))}
        </div>

        {/* Decorative elements */}
        <div className="absolute -bottom-10 -left-10 w-20 h-20 text-6xl opacity-20 rotate-12">🎴</div>
        <div className="absolute -top-5 -right-5 w-16 h-16 text-5xl opacity-20 -rotate-12">🎲</div>
      </div>

      <style>{`
        @keyframes bounce-slow {
          0%, 100% {
            transform: translateY(0);
          }
          50% {
            transform: translateY(-10px);
          }
        }
        .animate-bounce-slow {
          animation: bounce-slow 3s ease-in-out infinite;
        }
      `}</style>
    </div>
  );
}
