
import React, { useState, useRef, useEffect } from 'react';
import TitleScreen from './components/TitleScreen';
import DifficultySelector from './components/DifficultySelector';
import LevelSelector from './components/LevelSelector';
import GameBoard from './components/GameBoard';
import { GameState, PuzzleData, DifficultyLevel } from './types';

const App: React.FC = () => {
  const [gameState, setGameState] = useState<GameState>('TITLE');
  const [selectedDifficulty, setSelectedDifficulty] = useState<DifficultyLevel | null>(null);
  const [selectedPuzzle, setSelectedPuzzle] = useState<PuzzleData | null>(null);
  const [isMuted, setIsMuted] = useState(false);
  const audioRef = useRef<HTMLAudioElement | null>(null);

  // Initialize audio
  useEffect(() => {
    // Note: User needs to insert their own manual path here as requested.
    // For now, we use a placeholder nature ambient music.
    audioRef.current = new Audio('https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3');
    audioRef.current.loop = true;
    audioRef.current.volume = 0.5;

    return () => {
      if (audioRef.current) {
        audioRef.current.pause();
        audioRef.current = null;
      }
    };
  }, []);

  const toggleMute = () => {
    if (audioRef.current) {
      audioRef.current.muted = !audioRef.current.muted;
      setIsMuted(audioRef.current.muted);
    }
  };

  const startGame = () => {
    if (audioRef.current && audioRef.current.paused) {
      audioRef.current.play().catch(e => console.log("Audio playback blocked by browser. Click anywhere to play."));
    }
    setGameState('DIFFICULTY_SELECT');
  };

  const handleSelectDifficulty = (difficulty: DifficultyLevel) => {
    setSelectedDifficulty(difficulty);
    setGameState('LEVEL_SELECT');
  };

  const handleSelectLevel = (puzzle: PuzzleData) => {
    setSelectedPuzzle(puzzle);
    setGameState('PLAYING');
  };

  const handleExitGame = () => {
    setGameState('LEVEL_SELECT');
    setSelectedPuzzle(null);
  };

  const handleWin = () => {
    setGameState('LEVEL_SELECT');
    setSelectedPuzzle(null);
  };

  return (
    <div className="w-screen h-screen select-none">
      {/* Global Mute Toggle */}
      <button 
        onClick={toggleMute}
        className="fixed bottom-6 left-6 z-[110] bg-white/80 p-4 rounded-2xl shadow-lg border-2 border-[#ECFDF5] hover:bg-[#ECFDF5] transition-all text-[#81C784] active:scale-95"
      >
        {isMuted ? (
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
          </svg>
        ) : (
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
          </svg>
        )}
      </button>

      {gameState === 'TITLE' && <TitleScreen onStart={startGame} />}
      
      {gameState === 'DIFFICULTY_SELECT' && (
        <DifficultySelector 
          onSelect={handleSelectDifficulty} 
          onBack={() => setGameState('TITLE')} 
        />
      )}
      
      {gameState === 'LEVEL_SELECT' && selectedDifficulty && (
        <LevelSelector 
          difficulty={selectedDifficulty}
          onSelect={handleSelectLevel} 
          onBack={() => setGameState('DIFFICULTY_SELECT')} 
        />
      )}

      {gameState === 'PLAYING' && selectedPuzzle && (
        <GameBoard 
          puzzle={selectedPuzzle} 
          onExit={handleExitGame} 
          onWin={handleWin}
        />
      )}
    </div>
  );
};

export default App;
