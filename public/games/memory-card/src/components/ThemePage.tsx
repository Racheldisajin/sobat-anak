import type { ThemeName } from '../types/game';
import { themes } from '../data/gameData';
import { FloatingEmojis } from './FloatingEmojis';
import { SoundToggle } from './SoundToggle';
import { ArrowLeft, Play } from 'lucide-react';

interface ThemePageProps {
  onSelectTheme: (theme: ThemeName) => void;
  onBack: () => void;
  isBgMusicMuted: boolean;
  onToggleMusic: () => void;
}

export function ThemePage({ onSelectTheme, onBack, isBgMusicMuted, onToggleMusic }: ThemePageProps) {
  return (
    <div className="min-h-screen bg-[#FFF5E6] flex flex-col items-center justify-center p-4 relative overflow-hidden">
      <FloatingEmojis />
      <SoundToggle isMuted={isBgMusicMuted} onToggle={onToggleMusic} />

      {/* Back Button */}
      <button
        onClick={onBack}
        className="fixed top-4 left-4 z-50 bg-white/90 backdrop-blur-sm rounded-xl shadow-lg p-3 transition-all duration-300 hover:scale-110 hover:shadow-xl active:scale-95 border-2 border-amber-200 flex items-center gap-2"
        aria-label="Kembali"
      >
        <ArrowLeft className="w-5 h-5 text-amber-600" />
        <span className="text-amber-600 font-medium hidden sm:inline">Kembali</span>
      </button>

      {/* Title */}
      <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold text-center mb-8 md:mb-10 text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 relative z-10 mt-14 md:mt-4">
        Pilih Tema
      </h1>

      {/* Theme Cards Grid */}
      <div className="grid grid-cols-2 gap-4 md:gap-6 p-2 relative z-10 w-full max-w-xl mx-auto">
        {Object.values(themes).map((theme) => (
          <div
            key={theme.id}
            className="relative group cursor-pointer"
            onClick={() => onSelectTheme(theme.id)}
          >
            <div
              className="rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border-4 border-white/50 hover:border-white group-hover:scale-105 transform"
              style={{ background: theme.gradient }}
            >
              {/* Theme Tag */}
              <div className="absolute top-2 left-2 md:top-3 md:left-3 z-10">
                <span className="bg-white/90 backdrop-blur-sm px-3 py-1 md:px-4 md:py-1.5 rounded-full text-sm md:text-base font-bold text-gray-700 shadow">
                  {theme.name}
                </span>
              </div>

              {/* Image Container */}
              <div className="h-32 md:h-40 lg:h-48 flex items-center justify-center relative overflow-hidden">
                <img 
                  src={theme.image} 
                  alt={theme.name}
                  className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                />

                {/* Play Button Overlay */}
                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                  <div className="bg-white/95 rounded-full p-3 md:p-4 shadow-xl transform scale-50 group-hover:scale-100 transition-transform duration-300">
                    <Play className="w-6 h-6 md:w-8 md:h-8 text-gray-700 fill-current" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
