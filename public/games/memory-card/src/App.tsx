import { useGameState } from './hooks/useGameState';
import { LevelPage } from './components/LevelPage';
import { ThemePage } from './components/ThemePage';
import { GamePage } from './components/GamePage';
import { ConfirmModal } from './components/ConfirmModal';
import { ReadyModal } from './components/ReadyModal';
import { WinModal } from './components/WinModal';
import { LoseModal } from './components/LoseModal';

function App() {
  const { state, actions } = useGameState();

  return (
    <div className="font-comic min-h-screen">
      {/* Level Page */}
      {state.currentPage === 'level' && (
        <LevelPage
          onSelectLevel={actions.selectLevel}
          isBgMusicMuted={state.isBgMusicMuted}
          onToggleMusic={actions.toggleMusic}
        />
      )}

      {/* Theme Page */}
      {state.currentPage === 'theme' && state.selectedLevel && (
        <ThemePage
          onSelectTheme={actions.selectTheme}
          onBack={actions.goBack}
          isBgMusicMuted={state.isBgMusicMuted}
          onToggleMusic={actions.toggleMusic}
        />
      )}

      {/* Confirm Modal */}
      {state.showConfirmModal && state.selectedTheme && (
        <ConfirmModal
          theme={state.selectedTheme}
          onConfirm={actions.confirmTheme}
          onClose={actions.closeModal}
        />
      )}

      {/* Ready Modal */}
      {state.showReadyModal && state.selectedLevel && state.selectedTheme && (
        <ReadyModal
          level={state.selectedLevel}
          theme={state.selectedTheme}
          onStart={actions.startGame}
        />
      )}

      {/* Countdown Overlay (3-2-1) */}
      {state.currentPage === 'game' && state.countdown > 0 && !state.isPreviewPhase && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex flex-col items-center justify-center z-50">
          <div className="w-32 h-32 md:w-40 md:h-40 bg-white rounded-full flex items-center justify-center shadow-2xl animate-pop-in">
            <span className="text-6xl md:text-7xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500 animate-countdown-pop">
              {state.countdown}
            </span>
          </div>
          <p className="text-white text-xl mt-4 font-medium">Bersiap!</p>
          <style>{`
            @keyframes countdown-pop {
              0% { transform: scale(1.5); opacity: 0; }
              50% { transform: scale(0.9); }
              100% { transform: scale(1); opacity: 1; }
            }
            @keyframes pop-in {
              0% { transform: scale(0.8); opacity: 0; }
              100% { transform: scale(1); opacity: 1; }
            }
            .animate-countdown-pop { animation: countdown-pop 0.5s ease-out; }
            .animate-pop-in { animation: pop-in 0.3s ease-out; }
          `}</style>
        </div>
      )}

      {/* Game Page */}
      {state.currentPage === 'game' && state.selectedLevel && state.selectedTheme && (
        <GamePage
          level={state.selectedLevel}
          themeName={state.selectedTheme}
          cards={state.cards}
          timeLeft={state.timeLeft}
          score={state.score}
          moves={state.moves}
          isPreviewPhase={state.isPreviewPhase}
          isGameCompleted={state.isGameCompleted}
          matchedCards={state.matchedCards}
          onBack={actions.goBack}
          onRestart={actions.restartGame}
          onFlipCard={actions.flipCard}
          onEndPreview={actions.endPreview}
          isBgMusicMuted={state.isBgMusicMuted}
          onToggleMusic={actions.toggleMusic}
        />
      )}

      {/* Win Modal */}
      {state.isGameCompleted && state.isWin && state.selectedLevel && (
        <WinModal
          level={state.selectedLevel}
          score={state.score}
          moves={state.moves}
          timeLeft={state.timeLeft}
          onBack={actions.backToThemeFromModal}
          onRestart={actions.restartFromModal}
        />
      )}

      {/* Lose Modal */}
      {state.isGameCompleted && !state.isWin && state.selectedLevel && (
        <LoseModal
          level={state.selectedLevel}
          score={state.score}
          moves={state.moves}
          onBack={actions.backToThemeFromModal}
          onRestart={actions.restartFromModal}
        />
      )}
    </div>
  );
}

export default App;
