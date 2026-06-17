export type GameState = 'START' | 'GAMEPLAY' | 'END';

export interface Tooth {
  id: string; // e.g. "top-1", "bottom-2"
  position: 'top' | 'bottom';
  label: string; // e.g., "Gigi Seri", "Gigi Graham"
}

export interface Stain {
  id: string;
  toothId: string;
  x: number; // percentage coordinate (0-100) inside tooth
  y: number; // percentage coordinate (0-100) inside tooth
  opacity: number; // 0.0 to 1.0 (clean to dirty)
  initialOpacity: number;
  size: number; // brush diameter or diameter of the stain graphic
  type: 'bacteria' | 'yellow' | 'cookie' | 'sparkle'; // variety of stains
  color: string; // visual tint for the cartoon element
  angle: number; // comic rotation alignment
}

export interface ParentTip {
  id: number;
  title: string;
  icon: string;
  text: string;
}

export interface ProductRecommendation {
  id: number;
  name: string;
  category: string;
  image: string; // custom illustrated placeholder or emoji placeholder
  description: string;
  isPromo?: boolean;
}
