import React from 'react';
import { playBubblePopSound } from '../data';

interface StartScreenProps {
  onStart: () => void;
}

export const StartScreen: React.FC<StartScreenProps> = ({ onStart }) => {
  const handleStartClick = () => {
    playBubblePopSound();
    onStart();
  };

  return (
    <div className="flex flex-col items-center text-center justify-center py-6 px-4 md:py-10 select-none relative w-full overflow-hidden">
      
      {/* Floating Toothbrush & Decorative Elements */}
      <div className="absolute top-4 left-6 text-4xl md:text-5xl animate-float select-none z-10" title="Sikat Gigi Melayang">
        🪥
      </div>
      <div className="absolute top-12 right-12 text-3xl md:text-4xl animate-float-delayed opacity-40 select-none z-10">
        🫧
      </div>
      <div className="absolute bottom-8 left-10 text-3xl opacity-45 animate-float select-none z-10">
        🦷
      </div>
      <div className="absolute bottom-16 right-10 text-3xl opacity-30 animate-float-delayed select-none z-10">
        ✨
      </div>



      {/* Cute Illustration Badge of Blue Monster Mumu to match exactly what is shown inside the gameplay */}
      <div className="relative mb-6 select-none z-10 w-80 h-56 bg-[#E0F7FA] border-4 border-slate-800 rounded-3xl shadow-playful flex flex-col items-center justify-center p-3 overflow-hidden animate-float">
        
        {/* Playful Eyes protruding slightly on top of the mouth - exactly matching the screenshot! */}
        <div className="absolute top-2.5 flex space-x-10 z-10">
          {/* Left Eye */}
          <div className="w-12 h-12 bg-[#3B82F6] border-[3px] border-slate-800 rounded-full flex items-center justify-center shadow-sm">
            <div className="w-7.5 h-7.5 bg-white rounded-full flex items-center justify-center border-2 border-slate-800 overflow-hidden">
              <div className="w-3.5 h-3.5 bg-slate-800 rounded-full"></div>
            </div>
          </div>
          {/* Right Eye */}
          <div className="w-12 h-12 bg-[#3B82F6] border-[3px] border-slate-800 rounded-full flex items-center justify-center shadow-sm">
            <div className="w-7.5 h-7.5 bg-white rounded-full flex items-center justify-center border-2 border-slate-800 overflow-hidden">
              <div className="w-3.5 h-3.5 bg-slate-800 rounded-full"></div>
            </div>
          </div>
        </div>

        {/* Wide Mouth background */}
        <div className="relative w-full h-40 mt-8 bg-[#4E0C1B] rounded-3x1 border-[3px] border-slate-800 flex flex-col justify-between items-center py-2.5 px-3 overflow-hidden">
          
          {/* Pink Tongue */}
          <div className="absolute bottom-1 w-28 h-8 bg-[#FF8A65] rounded-t-full opacity-85 border-2 border-slate-800 border-b-0"></div>

          {/* Top Teeth Row */}
          <div className="flex justify-around w-full max-w-60 z-20">
            {/* Tooth 1 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-b-lg flex items-center justify-center">
              <div className="absolute -bottom-1 w-3 h-3 bg-yellow-400 border border-slate-800 rounded-full opacity-90"></div>
            </div>
            {/* Tooth 2 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-b-lg flex items-center justify-center">
              <div className="absolute -bottom-1.5 w-3.5 h-3.5 bg-purple-500 border border-slate-800 rounded-full opacity-90"></div>
            </div>
            {/* Tooth 3 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-b-lg"></div>
            {/* Tooth 4 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-b-lg flex items-center justify-center">
              <div className="absolute -bottom-1 w-3 h-3 bg-[#81C784] border border-slate-800 rounded-full opacity-90"></div>
            </div>
            {/* Tooth 5 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-b-lg"></div>
          </div>

          {/* Bottom Teeth Row */}
          <div className="flex justify-around w-full max-w-60 z-20">
            {/* Tooth 1 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-t-lg"></div>
            {/* Tooth 2 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-t-lg flex items-center justify-center">
              <div className="absolute -top-1 w-3.5 h-3.5 bg-amber-700 border border-slate-800 rounded-full opacity-90"></div>
            </div>
            {/* Tooth 3 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-t-lg"></div>
            {/* Tooth 4 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-t-lg flex items-center justify-center">
              <div className="absolute -top-1 w-3 h-3 bg-yellow-500 border border-slate-800 rounded-full opacity-90"></div>
            </div>
            {/* Tooth 5 */}
            <div className="relative w-[14%] h-7 bg-white border-2 border-slate-800 rounded-t-lg"></div>
          </div>

        </div>

      </div>
      
      {/* Exact Typography Stylings - Bold Slate Capitalized Heading */}
      <h1 className="font-fredoka text-[38px] md:text-[52px] text-[#1E2939] font-black uppercase tracking-tight mb-3 leading-none drop-shadow-sm z-10 animate-pulse">
        SIKAT GIGI MUMU
      </h1>
      
      {/* Description aligned to design system */}
      <p className="font-quicksand text-base md:text-[17px] font-bold text-slate-500 max-w-lg leading-relaxed mb-10 px-4 z-10">
        Bantu bersihkan kuman & noda di gigi Mumu dengan menyikat mereka secepat mungkin!
      </p>

      {/* Identical Green 3D Pill Button with Play Icon */}
      <button 
        onClick={handleStartClick}
        className="active:translate-y-1 active:border-b-2 cursor-pointer text-white font-fredoka font-black text-xl tracking-wider uppercase rounded-full bg-[#00C853] hover:bg-[#00E676] border-b-[6px] border-[#009624] px-14 py-4.5 flex items-center justify-center space-x-3.5 transition-all duration-100 shadow-[0_6px_20px_rgba(0,200,83,0.25)] z-10"
      >
        <span className="text-[17px] leading-none mb-0.5">▶</span>
        <span className="leading-none tracking-widest text-[18px]">Mulai Game</span>
      </button>

    </div>
  );
};
