import type { Theme, LevelConfig, Level } from '../types/game';

export const themes: Record<string, Theme> = {
  animals: {
    id: 'animals',
    name: 'Binatang',
    gradient: 'linear-gradient(135deg, #FF9A8B 0%, #FF6B88 100%)',
    image: import.meta.env.BASE_URL + 'theme-animals.png',
    items: [
      { id: 'cat', name: 'Kucing', image: import.meta.env.BASE_URL + 'card/animals/cat.png' },
      { id: 'dog', name: 'Anjing', image: import.meta.env.BASE_URL + 'card/animals/dog.png' },
      { id: 'frog', name: 'Katak', image: import.meta.env.BASE_URL + 'card/animals/frog.png' },
      { id: 'lion', name: 'Singa', image: import.meta.env.BASE_URL + 'card/animals/lion.png' },
      { id: 'monkey', name: 'Monyet', image: import.meta.env.BASE_URL + 'card/animals/monkey.png' },
      { id: 'panda', name: 'Panda', image: import.meta.env.BASE_URL + 'card/animals/panda.png' },
      { id: 'pinguin', name: 'Pinguin', image: import.meta.env.BASE_URL + 'card/animals/pinguin.png' },
      { id: 'rabbit', name: 'Kelinci', image: import.meta.env.BASE_URL + 'card/animals/rabbit.png' },
      { id: 'tiger', name: 'Harimau', image: import.meta.env.BASE_URL + 'card/animals/tiger.png' },
    ],
  },
  fruits: {
    id: 'fruits',
    name: 'Buah',
    gradient: 'linear-gradient(135deg, #A8E6CF 0%, #88D8B0 100%)',
    image: import.meta.env.BASE_URL + 'theme_fruits.png',
    items: [
      { id: 'apple', name: 'Apel', image: import.meta.env.BASE_URL + 'card/fruits/apple.png' },
      { id: 'banana', name: 'Pisang', image: import.meta.env.BASE_URL + 'card/fruits/banana.png' },
      { id: 'cherry', name: 'Ceri', image: import.meta.env.BASE_URL + 'card/fruits/cherry.png' },
      { id: 'grapes', name: 'Anggur', image: import.meta.env.BASE_URL + 'card/fruits/grapes.png' },
      { id: 'mango', name: 'Mangga', image: import.meta.env.BASE_URL + 'card/fruits/mango.png' },
      { id: 'orange', name: 'Jeruk', image: import.meta.env.BASE_URL + 'card/fruits/orange.png' },
      { id: 'pineapple', name: 'Nanas', image: import.meta.env.BASE_URL + 'card/fruits/pineapple.png' },
      { id: 'strawberry', name: 'Stroberi', image: import.meta.env.BASE_URL + 'card/fruits/strawberry.png' },
      { id: 'watermelon', name: 'Semangka', image: import.meta.env.BASE_URL + 'card/fruits/watermelon.png' },
    ],
  },
  numbers: {
    id: 'numbers',
    name: 'Angka',
    gradient: 'linear-gradient(135deg, #FFD7A8 0%, #FFB38A 100%)',
    image: import.meta.env.BASE_URL + 'theme-numbers.png',
    items: [
      { id: '1', name: 'Satu', image: import.meta.env.BASE_URL + 'card/numbers/number-1.png' },
      { id: '2', name: 'Dua', image: import.meta.env.BASE_URL + 'card/numbers/number-2.png' },
      { id: '3', name: 'Tiga', image: import.meta.env.BASE_URL + 'card/numbers/number-3.png' },
      { id: '4', name: 'Empat', image: import.meta.env.BASE_URL + 'card/numbers/number-4.png' },
      { id: '5', name: 'Lima', image: import.meta.env.BASE_URL + 'card/numbers/number-5.png' },
      { id: '6', name: 'Enam', image: import.meta.env.BASE_URL + 'card/numbers/number-6.png' },
      { id: '7', name: 'Tujuh', image: import.meta.env.BASE_URL + 'card/numbers/number-7.png' },
      { id: '8', name: 'Delapan', image: import.meta.env.BASE_URL + 'card/numbers/number-8.png' },
      { id: '9', name: 'Sembilan', image: import.meta.env.BASE_URL + 'card/numbers/number-9.png' },
    ],
  },
  shapes: {
    id: 'shapes',
    name: 'Bentuk',
    gradient: 'linear-gradient(135deg, #FFAAA5 0%, #FF8B94 100%)',
    image: import.meta.env.BASE_URL + 'theme-shapes.png',
    items: [
      { id: 'circle', name: 'Lingkaran', image: import.meta.env.BASE_URL + 'card/shapes/circle.png' },
      { id: 'triangle', name: 'Segitiga', image: import.meta.env.BASE_URL + 'card/shapes/triangle.png' },
      { id: 'square', name: 'Persegi', image: import.meta.env.BASE_URL + 'card/shapes/square.png' },
      { id: 'star', name: 'Bintang', image: import.meta.env.BASE_URL + 'card/shapes/star.png' },
      { id: 'heart', name: 'Hati', image: import.meta.env.BASE_URL + 'card/shapes/heart.png' },
      { id: 'oval', name: 'Oval', image: import.meta.env.BASE_URL + 'card/shapes/oval.png' },
      { id: 'diamond', name: 'Permata', image: import.meta.env.BASE_URL + 'card/shapes/diamond.png' },
      { id: 'trapezoid', name: 'Trapesium', image: import.meta.env.BASE_URL + 'card/shapes/trapezoid.png' },
      { id: 'pentagon', name: 'Segi Lima', image: import.meta.env.BASE_URL + 'card/shapes/pentagon.png' },
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
