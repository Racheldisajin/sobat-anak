export type Level = 'easy' | 'medium' | 'hard';
export type ThemeName = 'animals' | 'fruits' | 'numbers' | 'shapes';

export interface ThemeItem {
  id: string;
  name: string;
  image: string;
}

export interface GameCard {
  uid: string;
  pairId: string;
  name: string;
  image: string;
  isFlipped: boolean;
  isMatched: boolean;
}

export interface Theme {
  id: ThemeName;
  name: string;
  gradient: string;
  items: ThemeItem[];
  image?: string;
}

export interface LevelConfig {
  name: string;
  cardCount: number;
  gridCols: number;
  gridRows: number;
}

export type GamePage = 'level' | 'theme' | 'game';

export interface GameState {
  currentPage: GamePage;
  selectedLevel: Level | null;
  selectedTheme: ThemeName | null;
  cards: GameCard[];
  flippedCards: string[];
  matchedCards: string[];
  score: number;
  moves: number;
  timeLeft: number;
  isGameStarted: boolean;
  isBgMusicMuted: boolean;
  isPreviewPhase: boolean;
  isGameCompleted: boolean;
  isWin: boolean;
  showConfirmModal: boolean;
  showReadyModal: boolean;
  countdown: number;
}
