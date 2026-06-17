import type { Theme, LevelConfig, Level } from '../types/game';

export const themes: Record<string, Theme> = {
  animals: {
    id: 'animals',
    name: 'Binatang',
    gradient: 'linear-gradient(135deg, #FF9A8B 0%, #FF6B88 100%)',
    image: '/theme-animals.png',
    items: [
      { id: 'cat', name: 'Kucing', image: '/card/animals/cat.png' },
      { id: 'dog', name: 'Anjing', image: '/card/animals/dog.png' },
      { id: 'frog', name: 'Katak', image: '/card/animals/frog.png' },
      { id: 'lion', name: 'Singa', image: '/card/animals/lion.png' },
      { id: 'monkey', name: 'Monyet', image: '/card/animals/monkey.png' },
      { id: 'panda', name: 'Panda', image: '/card/animals/panda.png' },
      { id: 'pinguin', name: 'Pinguin', image: '/card/animals/pinguin.png' },
      { id: 'rabbit', name: 'Kelinci', image: '/card/animals/rabbit.png' },
      { id: 'tiger', name: 'Harimau', image: '/card/animals/tiger.png' },
    ],
  },
  fruits: {
    id: 'fruits',
    name: 'Buah',
    gradient: 'linear-gradient(135deg, #A8E6CF 0%, #88D8B0 100%)',
    image: '/theme_fruits.png',
    items: [
      { id: 'apple', name: 'Apel', image: '/card/fruits/apple.png' },
      { id: 'banana', name: 'Pisang', image: '/card/fruits/banana.png' },
      { id: 'cherry', name: 'Ceri', image: '/card/fruits/cherry.png' },
      { id: 'grapes', name: 'Anggur', image: '/card/fruits/grapes.png' },
      { id: 'mango', name: 'Mangga', image: '/card/fruits/mango.png' },
      { id: 'orange', name: 'Jeruk', image: '/card/fruits/orange.png' },
      { id: 'pineapple', name: 'Nanas', image: '/card/fruits/pineapple.png' },
      { id: 'strawberry', name: 'Stroberi', image: '/card/fruits/strawberry.png' },
      { id: 'watermelon', name: 'Semangka', image: '/card/fruits/watermelon.png' },
    ],
  },
  numbers: {
    id: 'numbers',
    name: 'Angka',
    gradient: 'linear-gradient(135deg, #FFD7A8 0%, #FFB38A 100%)',
    image: '/theme-numbers.png',
    items: [
      { id: '1', name: 'Satu', image: '/card/numbers/number-1.png' },
      { id: '2', name: 'Dua', image: '/card/numbers/number-2.png' },
      { id: '3', name: 'Tiga', image: '/card/numbers/number-3.png' },
      { id: '4', name: 'Empat', image: '/card/numbers/number-4.png' },
      { id: '5', name: 'Lima', image: '/card/numbers/number-5.png' },
      { id: '6', name: 'Enam', image: '/card/numbers/number-6.png' },
      { id: '7', name: 'Tujuh', image: '/card/numbers/number-7.png' },
      { id: '8', name: 'Delapan', image: '/card/numbers/number-8.png' },
      { id: '9', name: 'Sembilan', image: '/card/numbers/number-9.png' },
    ],
  },
  shapes: {
    id: 'shapes',
    name: 'Bentuk',
    gradient: 'linear-gradient(135deg, #FFAAA5 0%, #FF8B94 100%)',
    image: '/theme-shapes.png',
    items: [
      { id: 'circle', name: 'Lingkaran', image: '/card/shapes/circle.png' },
      { id: 'triangle', name: 'Segitiga', image: '/card/shapes/triangle.png' },
      { id: 'square', name: 'Persegi', image: '/card/shapes/square.png' },
      { id: 'star', name: 'Bintang', image: '/card/shapes/star.png' },
      { id: 'heart', name: 'Hati', image: '/card/shapes/heart.png' },
      { id: 'oval', name: 'Oval', image: '/card/shapes/oval.png' },
      { id: 'diamond', name: 'Permata', image: '/card/shapes/diamond.png' },
      { id: 'trapezoid', name: 'Trapesium', image: '/card/shapes/trapezoid.png' },
      { id: 'pentagon', name: 'Segi Lima', image: '/card/shapes/pentagon.png' },
    ],
  },
};

export const levelConfigs: Record<Level, LevelConfig> = {
  easy: {
    name: 'Mudah',
    cardCount: 8,
    gridCols: 4,
    gridRows: 2,
  },
  medium: {
    name: 'Sedang',
    cardCount: 12,
    gridCols: 4,
    gridRows: 3,
  },
  hard: {
    name: 'Sulit',
    cardCount: 18,
    gridCols: 6,
    gridRows: 3,
  },
};

export const floatingEmojis = ['🐱', '🍎', '⭐', '🐰', '🎈', '🌈', '🦋', '🌸', '🎮', '🎨', '🎪', '🎠'];
