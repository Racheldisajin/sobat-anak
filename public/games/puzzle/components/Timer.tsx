
import React, { useState, useEffect } from 'react';

interface TimerProps {
  initialSeconds: number;
  isActive: boolean;
  onTimeUp: () => void;
}

const Timer: React.FC<TimerProps> = ({ initialSeconds, isActive, onTimeUp }) => {
  const [seconds, setSeconds] = useState(initialSeconds);

  useEffect(() => {
    let interval: any = null;
    if (isActive && seconds > 0) {
      interval = setInterval(() => {
        setSeconds((prev) => prev - 1);
      }, 1000);
    } else if (seconds === 0) {
      onTimeUp();
      clearInterval(interval);
    }
    return () => clearInterval(interval);
  }, [isActive, seconds, onTimeUp]);

  const formatTime = (s: number) => {
    const mins = Math.floor(s / 60);
    const secs = s % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <div className={`px-4 py-2 rounded-full font-bold text-xl flex items-center gap-2 shadow-md transition-colors ${seconds < 30 ? 'bg-red-500 text-white animate-pulse' : 'bg-emerald-500 text-white'}`}>
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {formatTime(seconds)}
    </div>
  );
};

export default Timer;
