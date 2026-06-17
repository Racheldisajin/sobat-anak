import React, { useState, useEffect } from 'react';

interface TitleScreenProps {
  onStart: () => void;
}

const TitleScreen: React.FC<TitleScreenProps> = ({ onStart }) => {
  const [mousePos, setMousePos] = useState({ x: 0, y: 0 });
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    setIsLoaded(true);
    const handleMouseMove = (e: MouseEvent) => {
      setMousePos({
        x: (e.clientX / window.innerWidth - 0.5) * 20,
        y: (e.clientY / window.innerHeight - 0.5) * 20,
      });
    };
    window.addEventListener('mousemove', handleMouseMove);
    return () => window.removeEventListener('mousemove', handleMouseMove);
  }, []);

  return (
    /* Perubahan: overflow-y-auto supaya bisa scroll di Android */
    <div className="min-h-screen flex flex-col items-center justify-center p-6 bg-[#FFF7ED] relative overflow-y-auto overflow-x-hidden select-none">
      {/* Dynamic Background Layers */}
      <div className="absolute inset-0 bg-gradient-to-b from-[#EFF6FF] via-[#ECFDF5] to-[#ECFDF5] -z-10" />
      
      {/* Interactive Parallax Background Elements */}
      <div 
        className="absolute inset-0 pointer-events-none transition-transform duration-500 ease-out"
        style={{ transform: `translate(${mousePos.x * -0.5}px, ${mousePos.y * -0.5}px)` }}
      >
        <div className="absolute top-[10%] left-[5%] text-9xl opacity-10 animate-pulse-slow">🌿</div>
        <div className="absolute top-[60%] right-[10%] text-[12rem] opacity-5 animate-bounce-slow">🌳</div>
        <div className="absolute bottom-[10%] left-[15%] text-8xl opacity-10 animate-float-slow">🌸</div>
      </div>

      {/* Floating Clouds with varying speeds */}
      <div className="absolute top-12 left-[-20%] text-9xl opacity-20 animate-drift-slow pointer-events-none">☁️</div>
      <div className="absolute top-48 right-[-20%] text-[10rem] opacity-15 animate-drift-fast pointer-events-none">☁️</div>
      <div className="absolute top-1/3 left-[-15%] text-8xl opacity-10 animate-drift-medium pointer-events-none">☁️</div>

      {/* Dynamic Particles System */}
      <div className="absolute inset-0 pointer-events-none">
        {[...Array(12)].map((_, i) => (
          <div 
            key={i} 
            className="absolute animate-swirl text-3xl opacity-30"
            style={{
              left: `${Math.random() * 100}%`,
              top: `${Math.random() * 100}%`,
              animationDelay: `${i * 0.8}s`,
              animationDuration: `${10 + Math.random() * 10}s`
            }}
          >
            {['🍃', '🌿', '🌱', '✨'][i % 4]}
          </div>
        ))}
      </div>

      {/* Main Content with Parallax and Entry Animations */}
      <div 
        className={`z-10 text-center flex flex-col items-center max-w-4xl transition-all duration-[1500ms] ${isLoaded ? 'scale-100 opacity-100 translate-y-0' : 'scale-90 opacity-0 translate-y-10'}`}
        style={{ transform: `translate(${mousePos.x}px, ${mousePos.y}px)` }}
      >
        <div className="relative mb-16 group">
            {/* Background Glows */}
            <div className="absolute -inset-10 bg-[#81C784] rounded-full blur-[100px] opacity-30 animate-pulse" />
            <div className="absolute -inset-10 bg-[#FFD54F] rounded-full blur-[80px] opacity-20 animate-pulse" style={{animationDelay: '1s'}} />
            
            {/* Main Logo Box */}
            <div className="bg-white border-[8px] md:border-[10px] border-[#81C784] p-4 md:p-6 lg:p-8 rounded-[3rem] md:rounded-[4rem] shadow-[0_20px_60px_-15px_rgba(129,199,132,0.3)] relative overflow-hidden transform group-hover:rotate-1 transition-transform duration-700">
                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-transparent via-white/40 to-transparent animate-shimmer" />
                
                {/* Title with Logo */}
                <div className="flex flex-col items-center gap-2 md:gap-4 mb-2 md:mb-4">
                    <img src="/logo-sobat-anak.png" alt="Logo Sobat Anak" className="w-14 h-14 sm:w-16 sm:h-16 md:w-24 md:h-24 lg:w-32 lg:h-32 object-contain animate-pop-in" />
                    <h1 className="text-4xl sm:text-5xl md:text-7xl lg:text-8xl xl:text-9xl font-fredoka text-[#81C784] leading-none flex flex-nowrap whitespace-nowrap">
                      {"Puzzle Ceria".split("").map((char, i) => (
                        <span 
                          key={i} 
                          className="inline-block hover:text-[#FFF54F] hover:-translate-y-2 md:hover:-translate-y-4 transition-all duration-300 cursor-default animate-letter-float"
                          style={{ animationDelay: `${i * 0.1}s` }}
                        >
                          {char}
                        </span>
                      ))}
                    </h1>
                </div>
                
                <div className="bg-[#ECFDF5] py-2 md:py-3 px-6 md:px-10 rounded-full inline-flex items-center gap-2 md:gap-3 animate-pop-in">
                  <span className="text-2xl md:text-4xl">🌱</span>
                  <p className="text-[#1E2939] font-bold uppercase tracking-[0.4em] text-xs md:text-sm lg:text-xl">
                    Ayo Susun gambar
                  </p>
                  <span className="text-xl md:text-2xl">🌱</span>
                </div>
            </div>

            {/* Logo Decor Elements */}
            <div className="absolute -top-10 md:-top-12 -right-10 md:-right-12 text-6xl md:text-7xl lg:text-8xl animate-spin-slow-bounce pointer-events-none">🌻</div>
            <div className="absolute -bottom-6 md:-bottom-8 -left-10 md:-left-12 text-5xl md:text-6xl lg:text-7xl animate-sway pointer-events-none">🌿</div>
            <div className="absolute top-1/2 -left-12 md:-left-16 text-4xl md:text-5xl lg:text-6xl opacity-40 animate-bounce-slow pointer-events-none">🦋</div>
        </div>

        {/* Action Button */}
        <button 
          onClick={onStart}
          className="group relative transform transition-all active:scale-95"
        >
          <div className="absolute inset-0 bg-[#1E2939] rounded-[2rem] sm:rounded-[3rem] blur-xl translate-y-3 sm:translate-y-4 opacity-40 group-hover:opacity-60 transition-opacity" />
          <div className="relative px-10 sm:px-16 md:px-24 py-6 sm:py-8 md:py-10 bg-gradient-to-b from-[#81C784] to-[#4FC3F7] hover:from-[#639C62] hover:to-[#3EA2D9] rounded-[2rem] sm:rounded-[3rem] text-white font-fredoka text-3xl sm:text-4xl md:text-6xl shadow-[0_10px_0_0_#4A7B52] sm:shadow-[0_15px_0_0_#4A7B52] hover:shadow-[0_5px_0_0_#4A7B52] sm:hover:shadow-[0_10px_0_0_#4A7B52] hover:translate-y-[3px] sm:hover:translate-y-[5px] active:translate-y-[10px] sm:active:translate-y-[15px] active:shadow-none transition-all duration-150 flex items-center gap-3 sm:gap-6">
            MAINKAN
            <span className="text-2xl sm:text-4xl group-hover:translate-x-2 group-hover:-rotate-12 transition-transform duration-300">🎮</span>
          </div>
        </button>

        <p className="mt-20 text-[#1E2939]/40 text-2xl font-bold tracking-tight animate-fade-in-up">
          <span className="inline-block animate-bounce">✨</span> Susun kepingan, Asah Kemampuanmu! <span className="inline-block animate-bounce">✨</span>
        </p>
      </div>

      {/* KKN Identity - Perubahan: bottom-auto agar tidak overlap di layar kecil saat scroll */}
      <div className={`mt-10 md:absolute md:bottom-8 md:right-10 text-right transition-all duration-1000 delay-500 ${isLoaded ? 'translate-x-0 opacity-40' : 'translate-x-20 opacity-0'} hover:opacity-100`}>
        <div className="flex items-center gap-4 justify-end">
          <div className="h-[2px] w-12 bg-[#81C784] rounded-full group-hover:w-20 transition-all"></div>
          <p className="text-[#1E2939] font-fredoka text-lg uppercase tracking-[0.3em]"></p>
        </div>
        <p className="text-[10px] font-bold text-[#81C784]/60 uppercase mt-1 tracking-widest">Official Educational Project 2026</p>
      </div>

      {/* Background Decorative Ground */}
      <div className="absolute bottom-[-15%] w-[140%] h-[30%] bg-gradient-to-t from-[#81C784]/30 to-transparent blur-[120px] -z-0"></div>

      <style>{`
        @keyframes drift-slow { from { transform: translateX(-10vw); } to { transform: translateX(110vw); } }
        @keyframes drift-medium { from { transform: translateX(-10vw); } to { transform: translateX(110vw); } }
        @keyframes drift-fast { from { transform: translateX(110vw); } to { transform: translateX(-10vw); } }
        
        .animate-drift-slow { animation: drift-slow 80s linear infinite; }
        .animate-drift-medium { animation: drift-medium 55s linear infinite; }
        .animate-drift-fast { animation: drift-fast 40s linear infinite; }
        
        @keyframes swirl {
          0% { transform: translateY(0) rotate(0deg) translateX(0); opacity: 0; }
          20% { opacity: 0.4; }
          80% { opacity: 0.4; }
          100% { transform: translateY(-100vh) rotate(720deg) translateX(50px); opacity: 0; }
        }
        .animate-swirl { animation: swirl linear infinite; }
        
        @keyframes letter-float {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-15px); }
        }
        .animate-letter-float { animation: letter-float 3s ease-in-out infinite; }
        
        @keyframes spin-slow-bounce {
          0%, 100% { transform: rotate(0deg) scale(1); }
          50% { transform: rotate(15deg) scale(1.1); }
        }
        .animate-spin-slow-bounce { animation: spin-slow-bounce 6s ease-in-out infinite; }
        
        @keyframes sway {
          0%, 100% { transform: rotate(-5deg); }
          50% { transform: rotate(15deg); }
        }
        .animate-sway { animation: sway 4s ease-in-out infinite; }
        
        @keyframes wiggle {
          0%, 100% { transform: rotate(0); }
          25% { transform: rotate(-10deg); }
          75% { transform: rotate(10deg); }
        }
        .animate-wiggle { animation: wiggle 0.5s ease-in-out infinite; }

        @keyframes pop-in {
          0% { transform: scale(0); opacity: 0; }
          80% { transform: scale(1.1); opacity: 1; }
          100% { transform: scale(1); opacity: 1; }
        }
        .animate-pop-in { animation: pop-in 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }

        @keyframes fade-in-up {
          from { transform: translateY(20px); opacity: 0; }
          to { transform: translateY(0); opacity: 1; }
        }
        .animate-fade-in-up { animation: fade-in-up 0.8s ease-out forwards; }

        @keyframes slide-up-elastic {
          from { transform: translateY(100px) scale(0.9); opacity: 0; }
          to { transform: translateY(0) scale(1); opacity: 1; }
        }
        .animate-slide-up-elastic { animation: slide-up-elastic 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }

        @keyframes shimmer {
          from { transform: translateX(-100%); }
          to { transform: translateX(100%); }
        }
        .animate-shimmer { animation: shimmer 3s linear infinite; }
        
        .animate-pulse-slow { animation: pulse 6s ease-in-out infinite; }
        .animate-spin-slow { animation: spin 12s linear infinite; }

        .custom-scrollbar::-webkit-scrollbar {
          width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
          background: #f0fdf4;
          border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
          background: #10b981;
          border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
          background: #059669;
        }
      `}</style>
    </div>
  );
};

export default TitleScreen;