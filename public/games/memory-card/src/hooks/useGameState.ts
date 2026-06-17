import { useState, useCallback, useEffect, useRef } from 'react';
import type { GameCard, Level, ThemeName, GameState, ThemeItem } from '../types/game';
import { themes, levelConfigs } from '../data/gameData';
import { audioManager } from '../utils/audio';

function shuffleArray<T>(array: T[]): T[] {
  const shuffled = [...array];
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
  }
  return shuffled;
}

function generateCards(level: Level, themeName: ThemeName): GameCard[] {
  const theme = themes[themeName];
  const levelConfig = levelConfigs[level];
  const pairCount = levelConfig.cardCount / 2;

  // Select items based on level
  const selectedItems: ThemeItem[] = shuffleArray(theme.items).slice(0, pairCount);

  // Create pairs
  const cardPairs = [...selectedItems, ...selectedItems];
  const shuffledCards = shuffleArray(cardPairs);

  return shuffledCards.map((item, index) => ({
    uid: `card-${Date.now()}-${index}`,
    pairId: item.id,
    name: item.name,
    image: item.image,
    isFlipped: false,
    isMatched: false,
  }));
}

export function useGameState() {
  const [state, setState] = useState<GameState>({
    currentPage: 'level',
    selectedLevel: null,
    selectedTheme: null,
    cards: [],
    flippedCards: [],
    matchedCards: [],
    score: 0,
    moves: 0,
    timeLeft: 60,
    isGameStarted: false,
    isBgMusicMuted: false,
    isPreviewPhase: false,
    isGameCompleted: false,
    isWin: false,
    showConfirmModal: false,
    showReadyModal: false,
    countdown: 0,
  });

  const timerRef = useRef<number | null>(null);
  const countdownRef = useRef<number | null>(null);

  // Play menu bg music on mount
  useEffect(() => {
    audioManager.playBackgroundMusic('menu');
  }, []);

  // Handle page changes for background music
  useEffect(() => {
    if (state.currentPage === 'level' || state.currentPage === 'theme') {
      audioManager.playBackgroundMusic('menu');
    } else if (state.currentPage === 'game' && !state.isGameCompleted) {
      audioManager.playBackgroundMusic('game');
    }
  }, [state.currentPage, state.isGameCompleted]);

  const clearTimers = useCallback(() => {
    if (timerRef.current) {
      clearInterval(timerRef.current);
      timerRef.current = null;
    }
    if (countdownRef.current) {
      clearInterval(countdownRef.current);
      countdownRef.current = null;
    }
  }, []);

  useEffect(() => {
    return () => clearTimers();
  }, [clearTimers]);

  const selectLevel = useCallback((level: Level) => {
    audioManager.playSound("click");
    setState(prev => ({
      ...prev,
      selectedLevel: level,
      currentPage: 'theme',
    }));
  }, []);

  const selectTheme = useCallback((themeName: ThemeName) => {
    audioManager.playSound("click");
    setState(prev => ({
      ...prev,
      selectedTheme: themeName,
      showConfirmModal: true,
    }));
  }, []);

  const confirmTheme = useCallback(() => {
    audioManager.playSound("click");
    setState(prev => ({
      ...prev,
      showConfirmModal: false,
      showReadyModal: true,
    }));
  }, []);

  const closeModal = useCallback(() => {
    audioManager.playSound("click");
    setState(prev => ({
      ...prev,
      showConfirmModal: false,
      showReadyModal: false,
    }));
  }, []);

  const startGame = useCallback(() => {
    if (!state.selectedLevel || !state.selectedTheme) return;

    audioManager.playSound("click");
    clearTimers();

    // Generate cards
    const newCards = generateCards(state.selectedLevel, state.selectedTheme);

    // Start countdown from 3
    setState(prev => ({
      ...prev,
      cards: newCards,
      matchedCards: [],
      flippedCards: [],
      score: 0,
      moves: 0,
      timeLeft: 60,
      isGameStarted: false,
      isPreviewPhase: false,
      isGameCompleted: false,
      isWin: false,
      showConfirmModal: false,
      showReadyModal: false,
      countdown: 3,
      currentPage: 'game',
    }));

    // Start countdown interval
    countdownRef.current = window.setInterval(() => {
      setState(prev => {
        if (prev.countdown <= 1) {
          if (countdownRef.current) clearInterval(countdownRef.current);
          // Countdown finished - show preview
          return {
            ...prev,
            countdown: 0,
            isPreviewPhase: true,
            cards: prev.cards.map(c => ({ ...c, isFlipped: true })),
          };
        }
        return { ...prev, countdown: prev.countdown - 1 };
      });
    }, 1000);
  }, [state.selectedLevel, state.selectedTheme, clearTimers]);

  const startTimer = useCallback(() => {
    clearTimers();
    timerRef.current = window.setInterval(() => {
      setState(prev => {
        if (prev.timeLeft <= 1) {
          clearTimers();
          audioManager.stopBackgroundMusic();
          audioManager.playSound("lose");
          return {
            ...prev,
            timeLeft: 0,
            isGameCompleted: true,
            isWin: false,
          };
        }
        return { ...prev, timeLeft: prev.timeLeft - 1 };
      });
    }, 1000);
  }, [clearTimers]);

  const endPreview = useCallback(() => {
    setState(prev => ({
      ...prev,
      isPreviewPhase: false,
      cards: prev.cards.map(c => ({ ...c, isFlipped: false })),
    }));

    setTimeout(() => {
      startTimer();
    }, 300);
  }, [startTimer]);

  const flipCard = useCallback((cardUid: string) => {
    setState(prev => {
      if (prev.isPreviewPhase || prev.isGameCompleted) return prev;

      const card = prev.cards.find(c => c.uid === cardUid);
      if (!card || card.isFlipped || card.isMatched) return prev;
      if (prev.flippedCards.length >= 2) return prev;

      audioManager.playSound("flip");

      const newCards = prev.cards.map(c =>
        c.uid === cardUid ? { ...c, isFlipped: true } : c
      );

      const newFlippedCards = [...prev.flippedCards, cardUid];

      if (newFlippedCards.length === 2) {
        const [firstId, secondId] = newFlippedCards;
        const firstCard = newCards.find(c => c.uid === firstId)!;
        const secondCard = newCards.find(c => c.uid === secondId)!;

        if (firstCard.pairId === secondCard.pairId) {
          const matchedCards = [...prev.matchedCards, firstId, secondId];
          const isWin = matchedCards.length === prev.cards.length;

          audioManager.playSound("match");

          if (isWin) {
            setTimeout(() => {
              clearTimers();
              audioManager.stopBackgroundMusic();
              audioManager.playSound("win");
              setState(s => ({
                ...s,
                isGameCompleted: true,
                isWin: true,
              }));
            }, 500);
          }

          return {
            ...prev,
            cards: newCards.map(c =>
              c.uid === firstId || c.uid === secondId ? { ...c, isMatched: true } : c
            ),
            flippedCards: [],
            matchedCards,
            score: prev.score + 100,
            moves: prev.moves + 1,
          };
        } else {
          audioManager.playSound("wrong");
          setTimeout(() => {
            setState(s => ({
              ...s,
              cards: s.cards.map(c =>
                c.uid === firstId || c.uid === secondId ? { ...c, isFlipped: false } : c
              ),
              flippedCards: [],
            }));
          }, 800);

          return {
            ...prev,
            cards: newCards,
            flippedCards: newFlippedCards,
            moves: prev.moves + 1,
          };
        }
      }

      return {
        ...prev,
        cards: newCards,
        flippedCards: newFlippedCards,
      };
    });
  }, [clearTimers]);

  const restartGame = useCallback(() => {
    if (state.selectedLevel && state.selectedTheme) {
      audioManager.playSound("click");
      startGame();
    }
  }, [state.selectedLevel, state.selectedTheme, startGame]);

  const goBack = useCallback(() => {
    audioManager.playSound("click");
    clearTimers();
    setState(prev => {
      if (prev.currentPage === 'game') {
        return {
          ...prev,
          currentPage: 'theme',
          cards: [],
          flippedCards: [],
          matchedCards: [],
          score: 0,
          moves: 0,
          timeLeft: 60,
          isGameStarted: false,
          isPreviewPhase: false,
          isGameCompleted: false,
          isWin: false,
          countdown: 0,
        };
      }
      if (prev.currentPage === 'theme') {
        return { ...prev, currentPage: 'level', selectedLevel: null, selectedTheme: null };
      }
      return prev;
    });
  }, [clearTimers]);

  const toggleMusic = useCallback(() => {
    audioManager.playSound("click");
    setState(prev => {
      const newMuted = !prev.isBgMusicMuted;
      audioManager.setBgMusicMuted(newMuted);
      
      // If unmuting, play the appropriate background music based on current page
      if (!newMuted) {
        if (prev.currentPage === 'game' && !prev.isGameCompleted) {
          audioManager.playBackgroundMusic('game');
        } else {
          audioManager.playBackgroundMusic('menu');
        }
      }
      
      return { ...prev, isBgMusicMuted: newMuted };
    });
  }, []);

  const restartFromModal = useCallback(() => {
    restartGame();
  }, [restartGame]);

  const backToThemeFromModal = useCallback(() => {
    audioManager.playSound("click");
    clearTimers();
    setState(prev => ({
      ...prev,
      currentPage: 'theme',
      cards: [],
      flippedCards: [],
      matchedCards: [],
      score: 0,
      moves: 0,
      timeLeft: 60,
      isGameStarted: false,
      isPreviewPhase: false,
      isGameCompleted: false,
      isWin: false,
      countdown: 0,
    }));
  }, [clearTimers]);

  return {
    state,
    actions: {
      selectLevel,
      selectTheme,
      confirmTheme,
      closeModal,
      startGame,
      flipCard,
      endPreview,
      restartGame,
      goBack,
      toggleMusic,
      restartFromModal,
      backToThemeFromModal,
    },
  };
}
