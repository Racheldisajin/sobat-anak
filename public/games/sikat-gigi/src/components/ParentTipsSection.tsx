import React from 'react';

export const ParentTipsSection: React.FC = () => {
  return (
    <div className="bg-white rounded-3xl border-4 border-orange-200 p-8 shadow-xl flex flex-col md:flex-row items-start gap-6">
      
      {/* Icon Info */}
      <div className="bg-orange-500 p-4 rounded-2xl text-white shadow-md shrink-0 flex items-center justify-center">
        <svg 
          className="w-10 h-10" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24" 
          xmlns="http://www.w3.org/2000/svg"
        >
          <path 
            strokeLinecap="round" 
            strokeLinejoin="round" 
            strokeWidth="2.5" 
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>

      {/* Isi Tips */}
      <div className="flex-1">
        <h3 className="text-orange-600 font-black uppercase text-base tracking-wider mb-3">
          Tips Penting Untuk Orang Tua
        </h3>
        
        <div className="space-y-4 text-slate-700 leading-relaxed font-semibold text-sm">
          <p>
            💡 <span className="text-slate-800 font-extrabold">Habit Sejak Dini:</span> Mengajarkan anak menyikat gigi secara konsisten semenjak tumbuh gigi pertamanya merupakan langkah pertahanan paling efektif bagi pencegahan kuman jahat, plak, dan infeksi linu.
          </p>

          <p>
            🧼 <span className="text-slate-800 font-extrabold">Aturan 2 Menit:</span> Melatih anak membersihkan seluruh area gigi seperti depan, sisi dalam, dan bagian kunyah minimal 2 menit sambil menyanyikan lagu menyenangkan agar si kecil tetap gembira.
          </p>

          <p>
            🏠 <span className="text-slate-800 font-extrabold">Momen Emas:</span> Ingat untuk membiasakan rutinitas menyikat gigi pada pagi hari, setelah sarapan, dan terutama sebelum tidur agar bakteri mulut tidak aktif berkembang biak.
          </p>
        </div>
      </div>
    </div>
  );
};