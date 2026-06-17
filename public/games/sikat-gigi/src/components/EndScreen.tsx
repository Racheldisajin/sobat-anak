import React from 'react';
import { RotateCcw } from 'lucide-react';

interface EndScreenProps {
  score: number;
  onRestart: () => void;
}

export const EndScreen: React.FC<EndScreenProps> = ({ score, onRestart }) => {
  const isPerfect = score >= 100;

  // Dynamic conditional text logic (if-else) based on the final cleanliness percentage
  let feedbackMessage = '';
  let headlineText = '';
  let crownLabel = 'HEBAT! 🌟';

  if (score === 0) {
    crownLabel = 'OH TIDAK! 👾';
    headlineText = 'Gigi Mumu Masih Kotor!';
    feedbackMessage = 'Oh tidak, kuman-kuman jahat masih menempel erat di gigi Mumu! 👾 Mumu sedih karena giginya masih terasa linu. Yuk, ambil sikat gigimu dan kita coba usir kumannya bersama-sama! Kamu pasti bisa! 🪥✨';
  } else if (score >= 1 && score <= 49) {
    crownLabel = 'USAHA BAGUS! 👍';
    headlineText = 'Ayo Gosok Lebih Bersih!';
    feedbackMessage = `Wah, usaha yang bagus! Kamu berhasil membersihkan ${score}% kuman! 🌟 Gigi Mumu sudah mulai kelihatan sedikit putih, tapi masih banyak kuman tersembunyi yang bersembunyi di sana. Yuk, main lagi dan sikat lebih bersih sampai kumannya hilang semua! 🦷`;
  } else if (score >= 50 && score <= 99) {
    crownLabel = 'HEBAT SEKALI! 🎉';
    headlineText = 'Gigi Mumu Jauh Lebih Bersih!';
    feedbackMessage = `Hebat sekali usahamu! Kamu berhasil menyapu ${score}% kuman kotor dari gigi Mumu! 🎉 Gigi Mumu jauh lebih bersih dan sehat sekarang. Ingat ya, sisa kuman yang sedikit lagi itu harus terus disikat di dunia nyata agar gigimu tetap kuat! 🪥💎`;
  } else {
    crownLabel = 'SUKSES BESAR! 🏆';
    headlineText = 'Luar Biasa! Sempurna 100%! 🏆';
    feedbackMessage = 'Gigi Monster Mumu sekarang putih berkilau dan bebas kuman! ✨';
  }
  
  return (
    <div className="flex flex-col items-center py-6 px-4 md:py-8 justify-center animate-pop-in">
      
      {/* Playful Floating Stars/Confetti background */}
      <div className="absolute top-4 left-10 text-3xl animate-float opacity-30 select-none">✨</div>
      <div className="absolute top-16 right-12 text-4xl animate-float-delayed opacity-30 select-none">✨</div>
      <div className="absolute bottom-16 left-6 text-2xl animate-float opacity-35 select-none font-bold">🎉</div>
      <div className="absolute bottom-4 right-10 text-3xl animate-float-delayed opacity-30 select-none">🦷</div>

      {/* Decorative Achievement Graphic */}
      <div className="relative mb-6">
        {/* Floating background rays */}
        <div className="absolute inset-x-0 -top-4 bottom-0 bg-yellow-200 rounded-full blur-2xl opacity-40 scale-125 animate-pulse"></div>

        {/* Big Score Bubble Container */}
        <div className="relative w-44 h-44 bg-white border-comic rounded-full flex flex-col items-center justify-center shadow-playful">

          <span className="font-fredoka text-6xl font-black text-[#F97316]">
            {score}%
          </span>
          <span className="font-quicksand text-xs font-bold text-slate-500 uppercase mt-1 tracking-wider">
            Kebersihan Gigi
          </span>

        </div>
      </div>

      {/* Kinder-friendly Feedback Headlines */}
      <h2 className="font-fredoka text-2xl md:text-3xl text-[#1E2939] font-black text-center max-w-lg mb-2 leading-tight">
        {headlineText}
      </h2>

      <p className="font-quicksand text-sm md:text-base text-slate-600 font-semibold text-center max-w-md mb-6 leading-relaxed px-2">
        {feedbackMessage}
      </p>

      {/* RESTART BUTTON WITH INFINITE SOFT SPIN ARROW */}
      <button 
  onClick={onRestart}
  className="bg-[#00C853] hover:bg-[#00A83F] text-white text-2xl font-black px-16 py-5 rounded-full shadow-[0_8px_0_#008C3A] hover:shadow-[0_8px_0_#007A32] hover:-translate-y-1 active:translate-y-1 active:shadow-[0_4px_0_#007A32] transition-all duration-200 tracking-wide cursor-pointer flex items-center justify-center gap-3 mt-6 mb-10 z-10"
>
  <RotateCcw 
    size={30} 
    className="text-white animate-spin-slow" 
    strokeWidth={3} 
  />
  <span>MAIN LAGI</span>
</button>
    </div>
  );
};
