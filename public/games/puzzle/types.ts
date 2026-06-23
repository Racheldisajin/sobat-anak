
export type GameState = 'TITLE' | 'DIFFICULTY_SELECT' | 'LEVEL_SELECT' | 'PLAYING' | 'FINISHED';

export type DifficultyLevel = 'mudah' | 'sedang' | 'sulit';

export interface PuzzleData {
  id: number;
  title: string;
  image: string;
  rows: number;
  cols: number;
  difficulty: 'dot bayi' | 'obat' | 'Sulit';
}

export interface PieceState {
  id: string;
  row: number;
  col: number;
  currentPos: { x: number; y: number };
  targetPos: { x: number; y: number };
  isLocked: boolean;
  zIndex: number;
}
