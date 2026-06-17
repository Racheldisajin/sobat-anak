import { useEffect } from 'react';
import type { GameCard, Level, ThemeName } from '../types/game';
import { themes, levelConfigs } from '../data/gameData';
import { Card } from './Card';
import { SoundToggle } from './SoundToggle';
import { ArrowLeft, RotateCcw, Clock } from 'lucide-react';

interface GamePageProps {
  level: Level;
  themeName: ThemeName;
  cards: GameCard[];
  timeLeft: number;
  score: number;
  moves: number;
  isPreviewPhase: boolean;
  isGameCompleted: boolean;
  matchedCards: string[];
  onBack: () => void;
  onRestart: () => void;
  onFlipCard: (cardUid: string) => void;
  onEndPreview: () => void;
  isBgMusicMuted: boolean;
  onToggleMusic: () => void;
}

export function GamePage({
  level,
  themeName,
  cards,
  timeLeft,
  score,
  moves,
  isPreviewPhase,
  isGameCompleted,
  matchedCards,
  onBack,
  onRestart,
  onFlipCard,
  onEndPreview,
  isBgMusicMuted,
  onToggleMusic,
}: GamePageProps) {
  const theme = themes[themeName];
  const levelConfig = levelConfigs[level];

  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;
  const timeDisplay = `${minutes}:${seconds.toString().padStart(2, '0')}`;

  const progress = matchedCards.length / 2;
  const totalPairs = levelConfig.cardCount / 2;

  /**
   * Layout khusus per level
   * Tujuannya agar level sedang 12 kartu tidak terlalu besar dan tidak terpotong.
   */
  const gameLayout = {
    easy: {
      boardWidth: 'max-w-[860px]',
      boardHeight: 'max-h-[680px]',
      gap: 'gap-4 md:gap-5 lg:gap-6',
      cardSize: 'large' as const,
    },
    medium: {
      boardWidth: 'max-w-[900px]',
      boardHeight: 'max-h-[690px]',
      gap: 'gap-3 md:gap-4',
      cardSize: 'medium' as const,
    },
    hard: {
      boardWidth: 'max-w-[1080px]',
      boardHeight: 'max-h-[700px]',
      gap: 'gap-3 md:gap-4',
      cardSize: 'small' as const,
    },
  };

  const currentLayout = gameLayout[level];

  useEffect(() => {
    if (isPreviewPhase && !isGameCompleted) {
      const timeout = setTimeout(() => {
        onEndPreview();
      }, 3000);

      return () => clearTimeout(timeout);
    }
  }, [isPreviewPhase, isGameCompleted, onEndPreview]);

  return (
    <div className="h-screen bg-[#FFF5E6] flex flex-col overflow-hidden relative">
      <SoundToggle isMuted={isBgMusicMuted} onToggle={onToggleMusic} />

      {/* Header */}
      <div className="flex-shrink-0 bg-white/85 backdrop-blur-sm shadow-md px-3 py-2 md:px-4 md:py-3">
        <div className="flex items-center justify-between max-w-6xl mx-auto">
          {/* Back Button */}
          <button
            onClick={onBack}
            className="bg-white/90 backdrop-blur-sm rounded-xl shadow p-2 md:p-2.5 transition-all duration-300 hover:scale-105 active:scale-95 border-2 border-amber-200"
            aria-label="Kembali"
          >
            <ArrowLeft className="w-4 h-4 md:w-5 md:h-5 text-amber-600" />
          </button>

          {/* Title with Logo */}
          <div className="text-center flex items-center justify-center gap-2">
            <div className="w-8 h-8 md:w-10 md:h-10 rounded-full overflow-hidden shadow flex items-center justify-center bg-white p-0.5 border border-amber-200">
              <img
                src="/logo-sobat-anak.png"
                alt="Logo Sobat Anak"
                className="w-full h-full object-contain"
              />
            </div>

            <div>
              <h1 className="text-lg sm:text-xl md:text-2xl font-bold text-gray-700 uppercase leading-tight">
                {theme.name}
              </h1>
              <p className="text-xs md:text-sm text-gray-500 leading-tight">
                {levelConfig.name} - {levelConfig.cardCount} Kartu
              </p>
            </div>
          </div>

          {/* Timer */}
          <div
            className={`
              flex items-center gap-1 md:gap-2 px-2 md:px-3 py-1.5 md:py-2 rounded-xl shadow
              ${timeLeft <= 30 ? 'bg-red-100 text-red-600 animate-pulse' : 'bg-white text-gray-700'}
            `}
          >
            <Clock className="w-4 h-4 md:w-5 md:h-5" />
            <span className="font-bold text-sm md:text-base">{timeDisplay}</span>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 flex overflow-hidden">
        {/* Game Grid Area */}
        <div className="flex-1 flex items-center justify-center px-3 py-3 md:px-5 md:py-4 overflow-hidden">
          <div
            className={`
              w-full h-full
              ${currentLayout.boardWidth}
              ${currentLayout.boardHeight}
              grid
              ${currentLayout.gap}
              mx-auto
            `}
            style={{
              gridTemplateColumns: `repeat(${levelConfig.gridCols}, minmax(0, 1fr))`,
              gridTemplateRows: `repeat(${levelConfig.gridRows}, minmax(0, 1fr))`,
            }}
          >
            {cards.map((card) => (
              <Card
                key={card.uid}
                card={card}
                size={currentLayout.cardSize}
                onClick={() => onFlipCard(card.uid)}
                disabled={isPreviewPhase || isGameCompleted}
              />
            ))}
          </div>
        </div>

        {/* Sidebar Desktop */}
        <div className="hidden sm:flex flex-shrink-0 w-36 md:w-44 lg:w-48 bg-white/80 backdrop-blur-sm shadow-lg p-3 md:p-4 flex-col justify-between border-l border-amber-100">
          <div className="space-y-4 md:space-y-6">
            {/* Score */}
            <div className="bg-gradient-to-br from-amber-100 to-orange-100 rounded-2xl p-3 md:p-4 shadow-inner">
              <p className="text-xs md:text-sm text-gray-500 uppercase tracking-wide">
                Skor
              </p>
              <p className="text-2xl md:text-3xl lg:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500">
                {score}
              </p>
            </div>

            {/* Moves */}
            <div className="bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl p-3 md:p-4 shadow-inner">
              <p className="text-xs md:text-sm text-gray-500 uppercase tracking-wide">
                Langkah
              </p>
              <p className="text-2xl md:text-3xl lg:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-purple-500">
                {moves}
              </p>
            </div>

            {/* Progress */}
            <div className="bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl p-3 md:p-4 shadow-inner">
              <p className="text-xs md:text-sm text-gray-500 uppercase tracking-wide">
                Progres
              </p>
              <p className="text-2xl md:text-3xl lg:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-emerald-500">
                {progress}/{totalPairs}
              </p>
            </div>
          </div>

          {/* Restart Button */}
          <button
            onClick={onRestart}
            className="w-full py-2.5 md:py-3 px-3 md:px-4 rounded-xl bg-gradient-to-r from-amber-400 to-orange-400 text-white font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 active:scale-95 border-2 border-white/30 flex items-center justify-center gap-2"
          >
            <RotateCcw className="w-4 h-4 md:w-5 md:h-5" />
            <span className="text-sm md:text-base">Mulai Ulang</span>
          </button>
        </div>
      </div>

      {/* Mobile Bottom Bar */}
      <div className="sm:hidden flex-shrink-0 bg-white/80 backdrop-blur-sm shadow-lg p-2 flex items-center justify-around border-t border-amber-100">
        <div className="text-center">
          <p className="text-xs text-gray-400">Skor</p>
          <p className="text-lg font-bold text-orange-500">{score}</p>
        </div>

        <div className="text-center">
          <p className="text-xs text-gray-400">Langkah</p>
          <p className="text-lg font-bold text-blue-500">{moves}</p>
        </div>

        <div className="text-center">
          <p className="text-xs text-gray-400">Progres</p>
          <p className="text-lg font-bold text-green-500">
            {progress}/{totalPairs}
          </p>
        </div>

        <button
          onClick={onRestart}
          className="p-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-400 text-white shadow"
        >
          <RotateCcw className="w-5 h-5" />
        </button>
      </div>

      {/* Preview Overlay */}
      {isPreviewPhase && (
        <div className="absolute inset-0 bg-black/30 flex items-center justify-center z-40">
          <div className="bg-white/95 rounded-2xl p-4 md:p-6 shadow-2xl text-center animate-pop-in">
            <p className="text-lg md:text-xl font-bold text-gray-700">
              Perhatikan baik-baik!
            </p>
            <p className="text-4xl md:text-5xl mt-2 animate-pulse">👀</p>
          </div>

          <style>{`
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
              animation: pop-in 0.3s ease-out;
            }
          `}</style>
        </div>
      )}
    </div>
  );
}