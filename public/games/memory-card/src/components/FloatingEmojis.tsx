import { useEffect, useState } from 'react';
import { floatingEmojis } from '../data/gameData';

interface FloatingEmoji {
  id: number;
  emoji: string;
  x: number;
  y: number;
  animationDelay: number;
  animationDuration: number;
}

export function FloatingEmojis() {
  const [emojis, setEmojis] = useState<FloatingEmoji[]>([]);

  useEffect(() => {
    const generated: FloatingEmoji[] = Array.from({ length: 20 }, (_, i) => ({
      id: i,
      emoji: floatingEmojis[Math.floor(Math.random() * floatingEmojis.length)],
      x: Math.random() * 100,
      y: Math.random() * 100,
      animationDelay: Math.random() * 5,
      animationDuration: 8 + Math.random() * 7,
    }));
    setEmojis(generated);
  }, []);

  return (
    <div className="fixed inset-0 overflow-hidden pointer-events-none z-0">
      {emojis.map((emoji) => (
        <div
          key={emoji.id}
          className="floating-emoji absolute text-2xl md:text-3xl lg:text-4xl opacity-30"
          style={{
            left: `${emoji.x}%`,
            top: `${emoji.y}%`,
            animationDelay: `${emoji.animationDelay}s`,
            animationDuration: `${emoji.animationDuration}s`,
          }}
        >
          {emoji.emoji}
        </div>
      ))}
      <style>{`
        @keyframes float {
          0%, 100% {
            transform: translateY(0) translateX(0) rotate(0deg);
          }
          25% {
            transform: translateY(-30px) translateX(10px) rotate(5deg);
          }
          50% {
            transform: translateY(-15px) translateX(-10px) rotate(-5deg);
          }
          75% {
            transform: translateY(-25px) translateX(5px) rotate(3deg);
          }
        }
        .floating-emoji {
          animation: float ease-in-out infinite;
        }
      `}</style>
    </div>
  );
}
