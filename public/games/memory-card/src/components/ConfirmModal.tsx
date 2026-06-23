import { X } from 'lucide-react';
import type { ThemeName } from '../types/game';
import { themes } from '../data/gameData';

interface ConfirmModalProps {
  theme: ThemeName;
  onConfirm: () => void;
  onClose: () => void;
}

export function ConfirmModal({ theme, onConfirm, onClose }: ConfirmModalProps) {
  const themeData = themes[theme];

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div
        className="bg-white rounded-3xl shadow-2xl p-6 md:p-8 max-w-md w-full transform animate-pop-in border-4 border-white/80"
        style={{ background: themeData.gradient }}
      >
        <div className="flex flex-col items-center mb-4">
          {/* Logo Sobat Anak */}
          <div className="w-12 h-12 md:w-16 md:h-16 rounded-full overflow-hidden shadow-xl flex items-center justify-center bg-white p-1 border-2 border-white/50 mb-3">
            <img 
              src={import.meta.env.BASE_URL + "logo-sobat-anak.png"} 
              alt="Logo Sobat Anak" 
              className="w-full h-full object-contain"
            />
          </div>
          <div className="flex justify-between items-center w-full">
            <h3 className="text-xl md:text-2xl font-bold text-white drop-shadow-md">Konfirmasi</h3>
            <button
              onClick={onClose}
              className="bg-white/20 backdrop-blur-sm rounded-full p-2 hover:bg-white/30 transition-colors"
            >
              <X className="w-5 h-5 text-white" />
            </button>
          </div>
        </div>

        <div className="bg-white/20 backdrop-blur-sm rounded-2xl p-4 md:p-6 mb-6">
          <p className="text-white text-lg md:text-xl text-center font-medium">
            Kamu memilih tema:
          </p>
          <p className="text-white text-2xl md:text-3xl text-center font-bold mt-2 flex items-center justify-center gap-2">
            <img 
              src={themeData.image} 
              alt={themeData.name} 
              className="w-12 h-12 md:w-16 md:h-16 rounded-full object-cover border-2 border-white shadow"
            />
            {themeData.name}
          </p>
        </div>

        <div className="flex gap-3">
          <button
            onClick={onClose}
            className="flex-1 py-3 px-4 bg-white/20 backdrop-blur-sm text-white rounded-xl font-bold hover:bg-white/30 transition-colors border-2 border-white/30"
          >
            Ganti
          </button>
          <button
            onClick={onConfirm}
            className="flex-1 py-3 px-4 bg-white text-gray-700 rounded-xl font-bold hover:bg-white/90 transition-colors shadow-lg"
          >
            Lanjutkan
          </button>
        </div>
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
          animation: pop-in 0.3s ease-out forwards;
        }
      `}</style>
    </div>
  );
}
