
import React, { useEffect, useState, useMemo } from 'react';
import { getPuzzlesByDifficulty, DIFFICULTY_CONFIG } from '../constants';
import { PuzzleData, DifficultyLevel } from '../types';

interface LevelSelectorProps {
  difficulty: DifficultyLevel;
  onSelect: (puzzle: PuzzleData) => void;
  onBack: () => void;
}

const LevelSelector: React.FC<LevelSelectorProps> = ({ difficulty, onSelect, onBack }) => {
  const [mounted, setMounted] = useState(false);
  const [pendingPuzzle, setPendingPuzzle] = useState<PuzzleData | null>(null);
  
  useEffect(() => {
    setMounted(true);
  }, []);

  const getDifficultyConfig = (diff: DifficultyLevel) => {
    switch (diff) {
      case 'mudah': 
        return {
          primary: '#81C784',
          light: '#ECFDF5',
          styles: 'bg-[#ECFDF5] text-[#81C784] border-[#81C784]',
        };
      case 'sedang': 
        return {
          primary: '#F97316',
          light: '#FFF7ED',
          styles: 'bg-[#FFF7ED] text-[#F97316] border-[#F97316]',
        };
      case 'sulit': 
        return {
          primary: '#EF4444',
          light: '#FEF2F2',
          styles: 'bg-[#FEF2F2] text-[#EF4444] border-[#EF4444]',
        };
      default: 
        return {
          primary: '#94a3b8',
          light: '#f1f5f9',
          styles: 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
  };

  const puzzles = useMemo(() => getPuzzlesByDifficulty(difficulty), [difficulty]);
  const difficultyConfig = DIFFICULTY_CONFIG[difficulty];
  const config = getDifficultyConfig(difficulty);

  const handleCardClick = (puzzle: PuzzleData) => {
    setPendingPuzzle(puzzle);
  };

  const confirmSelection = () => {
    if (pendingPuzzle) {
      onSelect(pendingPuzzle);
    }
  };

  const cancelSelection = () => {
    setPendingPuzzle(null);
  };

  return (
    <div className="min-h-screen bg-[#FFF7ED] relative overflow-hidden flex flex-col items-center py-12 px-6">
      {/* Dynamic Background Decorations */}
      <div className="absolute inset-0 bg-gradient-to-br from-[#ECFDF5] via-white to-[#EFF6FF] -z-10" />
      <div className="absolute inset-0 opacity-[0.03] pointer-events-none -z-5" style={{ backgroundImage: 'url("https://www.transparenttextures.com/patterns/cubes.png")' }}></div>
      
      {/* Animated Floating Nature Elements */}
      <div className="absolute top-20 left-[10%] text-6xl animate-float-slow opacity-20 select-none">🍃</div>
      <div className="absolute bottom-20 right-[10%] text-7xl animate-float-medium opacity-20 select-none">🌿</div>
      <div className="absolute top-40 right-[15%] text-4xl animate-bounce-slow opacity-30 select-none">🦋</div>
      <div className="absolute bottom-40 left-[5%] text-5xl animate-pulse opacity-10 select-none">🌸</div>

      <div className="max-w-6xl w-full z-10">
        {/* Aesthetic Header */}
        <div className={`flex flex-col md:flex-row items-center mb-16 transition-all duration-1000 ${mounted ? 'translate-y-0 opacity-100' : '-translate-y-10 opacity-0'}`}>
          <button 
            onClick={onBack}
            className="group mb-6 md:mb-0 bg-white p-5 rounded-[2rem] shadow-lg border-b-8 border-[#ECFDF5] hover:border-b-4 hover:translate-y-1 active:border-b-0 active:translate-y-2 transition-all text-[#81C784] flex items-center justify-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          
          <div className="flex-1 text-center">
            <h1 className="text-6xl md:text-7xl font-fredoka bg-gradient-to-r from-[#81C784] via-[#81C784] to-[#4FC3F7] bg-clip-text text-transparent drop-shadow-sm">
              Pilih Gambar
            </h1>
            <p className="text-[#1E2939]/40 font-bold tracking-[0.3em] uppercase mt-2 text-sm">Tentukan pilihanmu!</p>
          </div>

          <div className="hidden md:block w-20" /> {/* Spacer for centering */}
        </div>

        {/* Level Grid with Staggered Animations */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
          {puzzles.map((puzzle, index) => (
            <div 
              key={puzzle.id}
              onClick={() => handleCardClick(puzzle)}
              className={`group relative bg-white p-5 rounded-[3.5rem] shadow-[0_20px_50px_-15px_rgba(0,0,0,0.1)] transition-all cursor-pointer transform hover:-translate-y-4 hover:rotate-1 border-4 transition-all duration-500 ${mounted ? 'scale-100 opacity-100' : 'scale-50 opacity-0'}`}
              style={{ 
                transitionDelay: `${index * 150}ms`,
                '--primary-color': config.primary,
                '--light-color': config.light,
                borderColor: 'white'
              } as React.CSSProperties}
              onMouseEnter={(e) => {
                e.currentTarget.style.borderColor = config.primary;
                const overlay = e.currentTarget.querySelector('.overlay-color') as HTMLElement;
                if (overlay) overlay.style.backgroundColor = `${config.primary}20`;
                const ring = e.currentTarget.querySelector('.img-ring') as HTMLElement;
                if (ring) ring.style.boxShadow = `0 0 0 8px ${config.light}50`;
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.borderColor = 'white';
                const overlay = e.currentTarget.querySelector('.overlay-color') as HTMLElement;
                if (overlay) overlay.style.backgroundColor = 'transparent';
              }}
            >
              {/* Image Container */}
              <div className="relative aspect-[4/3] rounded-[2.5rem] overflow-hidden mb-6 shadow-inner img-ring">
                <img 
                  src={puzzle.image} 
                  alt={puzzle.title} 
                  className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out" 
                />
                
                {/* Difficulty Badge */}
                <div className={`absolute top-5 right-5 px-5 py-2 rounded-2xl text-sm font-black uppercase tracking-widest border-2 shadow-lg backdrop-blur-md ${config.styles}`}>
                    {difficulty === 'mudah' ? 'Mudah' : difficulty === 'sedang' ? 'Sedang' : 'Sulit'}
                </div>

                {/* Overlay on Hover */}
                <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center overlay-color">
                    <div className="bg-white/90 p-6 rounded-full scale-50 group-hover:scale-100 transition-transform duration-500 shadow-2xl">
                        <svg className="w-10 h-10" fill="currentColor" viewBox="0 0 24 24" style={{ color: config.primary }}>
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </div>
                </div>
              </div>

              {/* Content Area */}
              <div className="px-4 pb-4">
                <h3 className="text-3xl font-fredoka text-[#1E2939] mb-3 transition-colors" style={{ color: '#1E2939' }} onMouseEnter={(e) => e.currentTarget.style.color = config.primary} onMouseLeave={(e) => e.currentTarget.style.color = '#1E2939'}>
                  {puzzle.title}
                </h3>
                
                <div className="flex justify-between items-center p-4 rounded-3xl border" style={{ backgroundColor: `${config.light}50`, borderColor: config.light }}>
                  <div className="flex flex-col">
                    <span className="text-[10px] font-black uppercase tracking-widest" style={{ color: config.primary }}>Tingkat Kesulitan</span>
                    <span className="text-[#1E2939] font-fredoka text-xl">{difficultyConfig.pieces} Keping</span>
                  </div>
                  
                  <div className="w-12 h-12 rounded-2xl text-white flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform" style={{ backgroundColor: config.primary }}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M12 4v16m8-8H4" />
                    </svg>
                  </div>
                </div>
              </div>

              {/* Decorative elements behind the card on hover */}
              <div className="absolute -z-10 inset-0 blur-3xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style={{ backgroundColor: `${config.primary}10` }} />
            </div>
          ))}
        </div>

        {/* Footer Info */}
        <div className={`mt-20 text-center transition-all duration-1000 delay-700 ${mounted ? 'opacity-40' : 'opacity-0'}`}>
           <p className="text-[#1E2939] font-bold flex items-center justify-center gap-3">
             <span className="w-8 h-px" style={{ backgroundColor: config.primary }}></span>
             Pilih satu untuk mulai menyusun puzzle
             <span className="w-8 h-px" style={{ backgroundColor: config.primary }}></span>
           </p>
        </div>
      </div>

      {/* Confirmation Modal */}
      {pendingPuzzle && (
<div className="fixed inset-0 z-[1000] flex items-center justify-center p-6 bg-[#1E2939]/70 backdrop-blur-xl animate-fade-in">
          <div className="bg-white rounded-[4rem] p-12 max-w-xl w-full text-center shadow-[0_40px_100px_-20px_rgba(0,0,0,0.6)] animate-pop" style={{ borderWidth: '12px', borderStyle: 'solid', borderColor: config.primary }}>
            
            <div className="relative inline-block mb-10">
                <div className="absolute -inset-8 rounded-full blur-2xl opacity-50 animate-pulse" style={{ backgroundColor: config.light }} />
                <div className="text-8xl animate-bounce-slow">🤔</div>
            </div>

            <h2 className="text-4xl md:text-5xl font-fredoka text-[#1E2939] mb-4 leading-tight">
                Apakah kamu yakin ingin memilih gambar ini?
            </h2>
            
            <div className="p-6 rounded-[2.5rem] mb-12 border-2 border-dashed flex items-center gap-6" style={{ backgroundColor: config.light, borderColor: config.light }}>
                <div className="w-24 h-24 rounded-3xl overflow-hidden shadow-lg flex-shrink-0">
                    <img src={pendingPuzzle.image} className="w-full h-full object-cover" alt="Preview" />
                </div>
                <div className="text-left">
                    <p className="text-[#1E2939] font-fredoka text-2xl">{pendingPuzzle.title}</p>
                    <p className="font-bold text-sm uppercase tracking-widest" style={{ color: config.primary }}>{pendingPuzzle.difficulty} • {pendingPuzzle.rows * pendingPuzzle.cols} Keping</p>
                </div>
            </div>

            <div className="grid grid-cols-2 gap-6">
                <button 
                  onClick={cancelSelection}
                  className="bg-slate-100 hover:bg-slate-200 text-slate-500 font-fredoka text-3xl py-6 rounded-[2rem] shadow-[0_8px_0_0_#cbd5e1] hover:shadow-[0_4px_0_0_#cbd5e1] hover:translate-y-[4px] active:translate-y-[8px] active:shadow-none transition-all"
                >
                  BATAL
                </button>
                <button 
                  onClick={confirmSelection}
                  className="group text-white font-fredoka text-3xl py-6 rounded-[2rem] shadow-[0_8px_0_0_#4A7B52] hover:translate-y-[4px] active:translate-y-[8px] active:shadow-none transition-all flex items-center justify-center gap-3"
                  style={{ backgroundColor: config.primary }}
                >
                  MULAI!
                  <span className="text-4xl group-hover:rotate-12 transition-transform">🚀</span>
                </button>
            </div>
          </div>
        </div>
      )}

      <style>{`
        @keyframes float-slow {
          0%, 100% { transform: translateY(0) rotate(0); }
          50% { transform: translateY(-30px) rotate(10deg); }
        }
        @keyframes float-medium {
          0%, 100% { transform: translateY(0) rotate(0); }
          50% { transform: translateY(-20px) rotate(-15deg); }
        }
        @keyframes bounce-slow {
          0%, 100% { transform: translateY(0) scale(1); }
          50% { transform: translateY(-15px) scale(1.1); }
        }
        @keyframes pop {
          0% { transform: scale(0.5); opacity: 0; }
          70% { transform: scale(1.1); opacity: 1; }
          100% { transform: scale(1); opacity: 1; }
        }
        @keyframes fade-in {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        .animate-float-slow { animation: float-slow 8s infinite ease-in-out; }
        .animate-float-medium { animation: float-medium 6s infinite ease-in-out; }
        .animate-bounce-slow { animation: bounce-slow 4s infinite ease-in-out; }
        .animate-pop { animation: pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
      `}</style>
    </div>
  );
};

export default LevelSelector;
