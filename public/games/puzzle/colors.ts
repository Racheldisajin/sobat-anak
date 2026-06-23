/**
 * Color Palette - Sobat Anak Design System
 * Warna-warna yang dirancang untuk aplikasi yang ceria, edukatif, dan aman untuk anak-anak
 */

export const COLORS = {
  // Primary Colors
  coral: '#FF8A65',        // Warna utama - Coral
  orange: '#FF7316',       // Highlight - Orange
  yellow: '#FFD54F',       // Accent - Yellow
  skyBlue: '#4FC3F7',      // Accent - Sky Blue
  mintGreen: '#81C784',    // Success - Mint Green
  
  // Neutral & Text
  darkText: '#1E2939',     // Text utama - Dark
  
  // Background
  mainBg: '#FFF7ED',       // Background utama - Cream
  cardBg: '#FFFFFF',       // Card Background - White
  
  // Soft Variants
  blueSoft: '#EFF6FF',     // Soft Blue
  greenSoft: '#ECFDF5',    // Soft Green
  yellowSoft: '#FEF3C7',   // Soft Yellow
  
  // Status Colors
  danger: '#DC2626',       // Error/Danger - Red
  success: '#81C784',      // Success - Mint Green (same as mintGreen)
  
  // Additional useful variants
  coralLight: '#FFB399',   // Lighter Coral
  orangeLight: '#FFB366',  // Lighter Orange
  mintLight: '#B8DC9E',    // Lighter Mint Green
  skyLight: '#8FE7F0',     // Lighter Sky Blue
  
  // For shadows and borders
  coral20: 'rgba(255, 136, 85, 0.2)',
  coral10: 'rgba(255, 136, 85, 0.1)',
  orange20: 'rgba(255, 115, 22, 0.2)',
  mint20: 'rgba(145, 199, 100, 0.2)',
  mint10: 'rgba(145, 199, 100, 0.1)',
};

/**
 * Tailwind config ekstension untuk custom colors
 * Tambahkan ini ke tailwind.config.js jika menggunakan Tailwind CLI
 */
export const tailwindColorConfig = {
  extend: {
    colors: {
      'sobat-coral': COLORS.coral,
      'sobat-orange': COLORS.orange,
      'sobat-yellow': COLORS.yellow,
      'sobat-sky': COLORS.skyBlue,
      'sobat-mint': COLORS.mintGreen,
      'sobat-dark': COLORS.darkText,
      'sobat-cream': COLORS.mainBg,
      'sobat-blue-soft': COLORS.blueSoft,
      'sobat-green-soft': COLORS.greenSoft,
      'sobat-yellow-soft': COLORS.yellowSoft,
      'sobat-danger': COLORS.danger,
    }
  }
};
