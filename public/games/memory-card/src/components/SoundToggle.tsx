interface SoundToggleProps {
  isMuted: boolean;
  onToggle: () => void;
}

export function SoundToggle({ isMuted, onToggle }: SoundToggleProps) {
  return (
    <button
      onClick={onToggle}
      className="fixed bottom-4 left-4 z-50 bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-3 md:p-4 transition-all duration-300 hover:scale-110 hover:shadow-xl active:scale-95 border-2 border-amber-200"
      aria-label={isMuted ? 'Hidupkan suara' : 'Matikan suara'}
    >
      <div className="flex items-center gap-2">
        <span className="text-2xl md:text-3xl">
          {isMuted ? '🔇' : '🔊'}
        </span>
      </div>
    </button>
  );
}
