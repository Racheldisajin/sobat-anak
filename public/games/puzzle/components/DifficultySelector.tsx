import React, { useState, useEffect } from 'react';
import { DifficultyLevel } from '../types';

interface DifficultySelectorProps {
  onSelect: (difficulty: DifficultyLevel) => void;
  onBack: () => void;
}

const DifficultySelector: React.FC<DifficultySelectorProps> = ({ onSelect, onBack }) => {
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  const difficulties = [
    {
      level: 'mudah' as DifficultyLevel,
      label: 'Mudah',
      pieces: 8,
      color: 'bg-[#ECFDF5] text-[#81C784] border-[#81C784]',
      hoverColor: 'hover:bg-[#81C784] hover:text-white',
      icon: '😊',
      description: '8 keping puzzle',
      borderShadow: 'shadow-[0_8px_0_0_#81C784]',
      hoverShadow: 'hover:shadow-[0_4px_0_0_#81C784]',
    },
    {
      level: 'sedang' as DifficultyLevel,
      label: 'Sedang',
      pieces: 12,
      color: 'bg-[#FFF7ED] text-[#F97316] border-[#F97316]',
      hoverColor: 'hover:bg-[#F97316] hover:text-white',
      icon: '😃',
      description: '12 keping puzzle',
      borderShadow: 'shadow-[0_8px_0_0_#F97316]',
      hoverShadow: 'hover:shadow-[0_4px_0_0_#F97316]',
    },
    {
      level: 'sulit' as DifficultyLevel,
      label: 'Sulit',
      pieces: 16,
      color: 'bg-[#FEF2F2] text-[#EF4444] border-[#EF4444]',
      hoverColor: 'hover:bg-[#EF4444] hover:text-white',
      icon: '🤩',
      description: '16 keping puzzle',
      borderShadow: 'shadow-[0_8px_0_0_#EF4444]',
      hoverShadow: 'hover:shadow-[0_4px_0_0_#EF4444]',
    },
  ];

  return (
    <div className="min-h-screen bg-[#FFF7ED] relative overflow-hidden flex flex-col items-center py-12 px-6">
      {/* Dynamic Background Decorations */}
      <div className="absolute inset-0 bg-gradient-to-br from-[#ECFDF5] via-white to-[#EFF6FF] -z-10" />
      <div className="absolute inset-0 opacity-[0.03] pointer-events-none -z-5" style={{ backgroundImage: 'url("https://www.transparenttextures.com/patterns/cubes.png")' }}></div>
      
      {/* Animated Floating Nature Elements */}
      <div className="absolute top-[10%] left-[5%] text-7xl animate-float-slow opacity-20 select-none">🌿</div>
      <div className="absolute bottom-[10%] right-[5%] text-8xl animate-float-medium opacity-20 select-none">🌳</div>
      <div className="absolute top-[30%] right-[10%] text-5xl animate-bounce-slow opacity-30 select-none">🦋</div>
      <div className="absolute bottom-[30%] left-[8%] text-6xl animate-pulse opacity-10 select-none">🌸</div>

      <div className="max-w-4xl w-full z-10">
        {/* Header */}
        <div className={`flex flex-col md:flex-row items-center mb-12 transition-all duration-1000 ${mounted ? 'translate-y-0 opacity-100' : '-translate-y-10 opacity-0'}`}>
          <button 
            onClick={onBack}
            className="group mb-6 md:mb-0 bg-white p-4 rounded-[2rem] shadow-lg border-b-8 border-[#ECFDF5] hover:border-b-4 hover:translate-y-1 active:border-b-0 active:translate-y-2 transition-all text-[#81C784] flex items-center justify-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          
          <div className="flex-1 text-center">
            <h1 className="text-5xl md:text-7xl font-fredoka bg-gradient-to-r from-[#81C784] via-[#81C784] to-[#4FC3F7] bg-clip-text text-transparent drop-shadow-sm">
              Pilih Level
            </h1>
            <p className="text-[#1E2939]/40 font-bold tracking-[0.3em] uppercase mt-2 text-sm">Tentukan tingkat kesulitan!</p>
          </div>

          <div className="hidden md:block w-16" />
        </div>

        {/* Difficulty Cards Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {difficulties.map((diff, index) => (
            <div 
              key={diff.level}
              onClick={() => onSelect(diff.level)}
              className={`group relative bg-white p-8 rounded-[3rem] shadow-lg hover:shadow-xl transition-all cursor-pointer transform hover:-translate-y-3 hover:rotate-1 border-4 ${diff.color} transition-all duration-500 ${mounted ? 'scale-100 opacity-100' : 'scale-50 opacity-0'}`}
              style={{ animationDelay: `${index * 150}ms` }}
            >
              <div className="absolute -z-10 inset-0 bg-current opacity-10 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" />
              
              <div className="text-center">
                <div className="text-7xl mb-4">{diff.icon}</div>
                <h3 className="text-4xl font-fredoka mb-2">{diff.label}</h3>
                <div className={`${diff.color} inline-block px-6 py-2 rounded-full text-xl font-bold mb-6`}>
                  {diff.description}
                </div>
                
                <div className={`w-full py-4 rounded-[2rem] bg-white font-fredoka text-2xl ${diff.color.split(' ')[1]} ${diff.hoverColor} ${diff.borderShadow} ${diff.hoverShadow} hover:translate-y-1 active:translate-y-2 active:shadow-none transition-all`}>
                  Pilih
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Footer Info */}
        <div className={`mt-16 text-center transition-all duration-1000 delay-700 ${mounted ? 'opacity-40' : 'opacity-0'}`}>
           <p className="text-[#1E2939] font-bold flex items-center justify-center gap-3">
             <span className="w-8 h-px bg-[#81C784]"></span>
             Pilih level untuk melanjutkan
             <span className="w-8 h-px bg-[#81C784]"></span>
           </p>
        </div>
      </div>

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
        .animate-float-slow { animation: float-slow 8s infinite ease-in-out; }
        .animate-float-medium { animation: float-medium 6s infinite ease-in-out; }
        .animate-bounce-slow { animation: bounce-slow 4s infinite ease-in-out; }
      `}</style>
    </div>
  );
};

export default DifficultySelector;
