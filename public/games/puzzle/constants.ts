import { PuzzleData, DifficultyLevel } from './types';

// Konfigurasi ukuran puzzle per level
export const DIFFICULTY_CONFIG: Record<DifficultyLevel, { rows: number; cols: number; pieces: number }> = {
  mudah: { rows: 2, cols: 4, pieces: 8 },
  sedang: { rows: 3, cols: 4, pieces: 12 },
  sulit: { rows: 4, cols: 4, pieces: 16 },
};

// Puzzle dasar (tanpa rows dan cols, akan diisi sesuai level)
const BASE_PUZZLES = [
  { id: 1, title: "susu ", image:"/sob1.png", difficulty: 'susu' as const },
  { id: 2, title: "dot bayi ", image: "/sob2.png", difficulty: 'dot bayi' as const },
  { id: 3, title: "set mandi ", image: "/sob3.png", difficulty: 'set mandi' as const },
];

// Fungsi untuk mendapatkan puzzle dengan konfigurasi sesuai level
export const getPuzzlesByDifficulty = (difficulty: DifficultyLevel): PuzzleData[] => {
  const config = DIFFICULTY_CONFIG[difficulty];
  return BASE_PUZZLES.map(puzzle => ({
    ...puzzle,
    rows: config.rows,
    cols: config.cols,
  }));
};

export const PUZZLES: PuzzleData[] = [
  {
    id: 1,
    title: "obat ",
    image:"/sob1.png",
    rows: 3,
    cols: 4,
    difficulty: 'obat'
  },
  {
    id: 2,
    title: "dot bayi ",
    image: "/sob2.jpg",
    rows: 3,
    cols: 4,
    difficulty: 'dot bayi'
  },
  {
    id: 3,
    title: "set mandi ",
    image: "/sob3.png",
    rows: 3,
    cols: 4,
    difficulty: 'set mandi'
  }
];

export const GAME_TIME_SECONDS = 60; 
export const SNAP_THRESHOLD = 40; 

/**
 * PERBAIKAN UNTUK RESPONSIF:
 * Menentukan ukuran kepingan puzzle berdasarkan lebar layar.
 * Jika layar < 768px (Mobile), kita gunakan ukuran 80px agar tidak terlalu lebar.
 * Jika layar > 768px (Desktop), tetap 100px atau 120px.
 */
export const pieceSize = typeof window !== 'undefined' && window.innerWidth < 768 ? 80 : 120;