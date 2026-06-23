import React, { useEffect, useMemo, useState } from 'react';

interface SuccessModalProps {
  onNext: () => void;
  score?: number;
}

const SuccessModal: React.FC<SuccessModalProps> = ({ onNext, score = 0 }) => {
  const [show, setShow] = useState(false);

  useEffect(() => {
    const t = setTimeout(() => setShow(true), 50);
    return () => clearTimeout(t);
  }, []);

  const getStars = () => {
    if (score > 3500) return '⭐⭐⭐';
    if (score > 2000) return '⭐⭐';
    return '⭐';
  };

  const ecoElements = useMemo(
    () =>
      Array.from({ length: 12 }, (_, i) => ({
        id: i,
        emoji: ['🍃', '🌿', '🌱', '✨', '🌸'][i % 5],
        left: Math.random() * 100,
        top: Math.random() * 100,
        delay: Math.random() * 5,
        duration: 4 + Math.random() * 3,
      })),
    []
  );

  return (
    <div className="fixed inset-0 bg-[#1E2939]/75 backdrop-blur-md flex items-center justify-center z-[500] p-4 overflow-hidden">
      {/* Flying Eco Elements */}
      <div className="absolute inset-0 pointer-events-none">
        {ecoElements.map((item) => (
          <div
            key={item.id}
            className="absolute text-xl md:text-2xl animate-eco-float"
            style={{
              left: `${item.left}%`,
              top: `${item.top}%`,
              animationDelay: `${item.delay}s`,
              animationDuration: `${item.duration}s`,
            }}
          >
            {item.emoji}
          </div>
        ))}
      </div>

      {/* Main Modal Container */}
      <div
        className={`
          bg-white
          rounded-[2rem] md:rounded-[2.5rem]
          border-[5px] md:border-[7px] border-[#81C784]
          px-5 py-6 md:px-8 md:py-7
          max-w-[420px] md:max-w-[520px]
          w-full
          max-h-[90vh]
          flex flex-col items-center justify-center
          overflow-y-auto text-center
          shadow-[0_24px_50px_rgba(0,0,0,0.35)]
          transition-all duration-700 transform
          ${show ? 'scale-100 opacity-100 translate-y-0' : 'scale-75 opacity-0 translate-y-10'}
        `}
      >
        {/* Trophy Section */}
        <div className="relative inline-block mb-3 md:mb-4 shrink-0">
          <div className="absolute -inset-5 bg-[#FFF54F] rounded-full blur-2xl opacity-30 animate-pulse" />
          <div className="text-6xl md:text-7xl animate-trophy-bounce drop-shadow-2xl">
            🏆
          </div>
          <div className="absolute -top-1 -right-1 text-2xl md:text-3xl animate-spin-slow">
            🌟
          </div>
        </div>

        <h2 className="text-3xl md:text-4xl font-fredoka text-[#1E2939] mb-2 tracking-tight">
          Luar Biasa!
        </h2>

        <div className="text-2xl md:text-3xl mb-4 tracking-[0.25em] animate-bounce-slow">
          {getStars()}
        </div>

        {/* Score Box */}
        <div className="bg-[#ECFDF5] px-4 py-4 md:px-6 md:py-5 rounded-[1.5rem] md:rounded-[2rem] mb-5 border-2 border-dashed border-[#81C784]/30 w-full">
          <p className="text-[10px] md:text-sm font-black text-[#81C784]/70 uppercase tracking-widest mb-1">
            Total Skor
          </p>

          <p className="text-4xl md:text-5xl font-fredoka text-[#81C784] mb-2">
            {score}
          </p>

          <p className="text-xs md:text-base text-[#1E2939] font-bold leading-relaxed italic">
            "Keren, puzzlenya beres!."
          </p>
        </div>

        <button
          onClick={onNext}
          className="
            group w-full
            bg-[#81C784] hover:bg-[#639C62]
            text-white font-fredoka
            text-xl md:text-2xl
            py-4 md:py-5 px-6
            rounded-[1.25rem] md:rounded-[1.5rem]
            shadow-[0_7px_0_0_#4A7B52]
            hover:translate-y-[3px]
            active:translate-y-[7px] active:shadow-none
            transition-all
            flex items-center justify-center gap-3
            shrink-0
          "
        >
          SIAP LAGI!
          <span className="text-2xl md:text-3xl group-hover:rotate-12 transition-transform">
            🚀
          </span>
        </button>
      </div>

      <style>{`
        @keyframes trophy-bounce {
          0%, 100% {
            transform: translateY(0) rotate(0);
          }
          50% {
            transform: translateY(-12px) rotate(4deg);
          }
        }

        @keyframes bounce-slow {
          0%, 100% {
            transform: translateY(0);
          }
          50% {
            transform: translateY(-6px);
          }
        }

        @keyframes eco-float {
          0% {
            transform: translate(0, 0) rotate(0deg);
            opacity: 0;
          }
          20% {
            opacity: 1;
          }
          100% {
            transform: translate(40px, -100vh) rotate(360deg);
            opacity: 0;
          }
        }

        @keyframes spin-slow {
          from {
            transform: rotate(0deg);
          }
          to {
            transform: rotate(360deg);
          }
        }

        .animate-trophy-bounce {
          animation: trophy-bounce 3s infinite ease-in-out;
        }

        .animate-bounce-slow {
          animation: bounce-slow 2s infinite ease-in-out;
        }

        .animate-eco-float {
          animation: eco-float 5s linear infinite;
        }

        .animate-spin-slow {
          animation: spin-slow 8s linear infinite;
        }

        ::-webkit-scrollbar {
          width: 0px;
          background: transparent;
        }
      `}</style>
    </div>
  );
};

export default SuccessModal;