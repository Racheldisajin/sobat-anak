import { Tooth, Stain, ParentTip, ProductRecommendation } from './types';

// Let's model 12 teeth: 6 top teeth, 6 bottom teeth
export const TEETH_DATA: Tooth[] = [
  { id: 'top-1', position: 'top', label: 'Taring Kiri' },
  { id: 'top-2', position: 'top', label: 'Seri Samping' },
  { id: 'top-3', position: 'top', label: 'Seri Tengah Kiri' },
  { id: 'top-4', position: 'top', label: 'Seri Tengah Kanan' },
  { id: 'top-5', position: 'top', label: 'Seri Samping' },
  { id: 'top-6', position: 'top', label: 'Taring Kanan' },
  
  { id: 'bottom-1', position: 'bottom', label: 'Taring Kiri' },
  { id: 'bottom-2', position: 'bottom', label: 'Seri Samping' },
  { id: 'bottom-3', position: 'bottom', label: 'Seri Tengah Kiri' },
  { id: 'bottom-4', position: 'bottom', label: 'Seri Tengah Kanan' },
  { id: 'bottom-5', position: 'bottom', label: 'Seri Samping' },
  { id: 'bottom-6', position: 'bottom', label: 'Taring Kanan' },
];

/**
 * Generates initial stains on top of the teeth deterministically
 * so that they look visually clean, spaced, and highly playable.
 */
export const generateStains = (): Stain[] => {
  const stains: Stain[] = [];
  
  // Custom stains coordinate layouts to space them beautifully
  const stainConfigs = [
    // Top Teeth Stains
    { toothId: 'top-1', x: 50, y: 35, type: 'bacteria' as const, size: 28, color: '#C5E1A5' }, // light green kuman
    { toothId: 'top-2', x: 40, y: 40, type: 'yellow' as const, size: 26, color: '#FFD54F' }, // yellow plaque
    { toothId: 'top-3', x: 60, y: 45, type: 'cookie' as const, size: 22, color: '#A1887F' }, // cookie crumb
    { toothId: 'top-3', x: 30, y: 25, type: 'bacteria' as const, size: 18, color: '#81C784' }, // cute germ
    { toothId: 'top-4', x: 45, y: 35, type: 'yellow' as const, size: 24, color: '#FFCA28' },
    { toothId: 'top-5', x: 55, y: 40, type: 'bacteria' as const, size: 20, color: '#9CCC65' },
    { toothId: 'top-6', x: 50, y: 30, type: 'cookie' as const, size: 26, color: '#8D6E63' },
    
    // Bottom Teeth Stains
    { toothId: 'bottom-1', x: 45, y: 60, type: 'yellow' as const, size: 28, color: '#FFD54F' },
    { toothId: 'bottom-2', x: 55, y: 55, type: 'cookie' as const, size: 20, color: '#795548' },
    { toothId: 'bottom-3', x: 35, y: 65, type: 'bacteria' as const, size: 24, color: '#66BB6A' },
    { toothId: 'bottom-4', x: 50, y: 50, type: 'yellow' as const, size: 22, color: '#FFE082' },
    { toothId: 'bottom-4', x: 70, y: 70, type: 'bacteria' as const, size: 18, color: '#9CCC65' },
    { toothId: 'bottom-5', x: 45, y: 60, type: 'cookie' as const, size: 24, color: '#8D6E63' },
    { toothId: 'bottom-6', x: 50, y: 65, type: 'bacteria' as const, size: 25, color: '#4CAF50' },
  ];

  stainConfigs.forEach((config, idx) => {
    stains.push({
      id: `stain-${idx + 1}`,
      toothId: config.toothId,
      x: config.x,
      y: config.y,
      opacity: 1.0,
      initialOpacity: 1.0,
      size: config.size,
      type: config.type,
      color: config.color,
      angle: Math.floor(Math.random() * 360),
    });
  });

  return stains;
};

export const PARENT_TIPS: ParentTip[] = [
  {
    id: 1,
    title: 'Dongeng "Ksatria Sikat Gigi"',
    icon: '🧚‍♂️',
    text: 'Bujuk si kecil dengan bercerita bahwa sikat gigi adalah pedang ajaib dan busanya adalah gelembung pelindung untuk mengalahkan monster kuman jahat yang bersembunyi di giginya.',
  },
  {
    id: 2,
    title: 'Konsistensi Lewat Lagu Gembira',
    icon: '🎶',
    text: 'Sikat gigi yang baik dianjurkan selama 2 menit. Putar lagu bernada riang atau lagu sikat gigi khusus anak-anak sebagai timer alami agar mereka tidak bosan menggosok semua bagian.',
  },
  {
    id: 3,
    title: 'Bersikat Bersama (Role Modelling)',
    icon: '👩‍👦',
    text: 'Anak-anak adalah peniru yang hebat. Buat ritual sebelum tidur di mana Ayah, Bunda, dan anak menggosok gigi bersama di depan cermin dengan ekspresi wajah yang ceria dan lucu.',
  },
  {
    id: 4,
    title: 'Biarkan Anak Memilih Alat Mandinya',
    icon: '🪥',
    text: 'Ajak sang anak ke supermarket untuk memilih sendiri sikat gigi dengan karakter kartun kesayangannya serta pasta gigi berbagai rasa buah lembut yang aman bagi anak-anak.',
  }
];

export const PRODUCT_RECOM: ProductRecommendation[] = [
  {
    id: 11,
    name: 'Sobat Anak Stroberi Ceria',
    category: 'Pasta Gigi Anak (F&X)',
    image: '🍓',
    description: 'Pasta gigi beraroma stroberi manis dengan proteksi ganda Xylitol berkualitas medis. Aman tertelan dalam batas wajar bagi buah hati.',
    isPromo: true,
  },
  {
    id: 12,
    name: 'Sikat Bulu Jerapah Lentur',
    category: 'Sikat Gigi Balita',
    image: '🦒',
    description: 'Gagang silikon anti-slip berbentuk jerapah lucu dengan bulu sikat super micro-fine (0.01mm) yang ramah bagi gusi sensitif si kecil.',
  },
  {
    id: 13,
    name: 'Sobat Anak Anggur Pelangi',
    category: 'Pasta Gigi Non-Deterjen',
    image: '🍇',
    description: 'Formula pasta gelembung super lembut tanpa deterjen (SLS-free). Memberikan sensasi kesegaran anggur alami dengan kalsium aktif perlindungan gigi.',
    isPromo: false,
  }
];

// Reusable audio elements state context representation to synthesize brush scratch "Srek srek srek"
let lastSoundTime = 0;
let isMutedGlobal = false;

export const setMutedGlobal = (val: boolean) => {
  isMutedGlobal = val;
};

export const getMutedGlobal = () => {
  return isMutedGlobal;
};

/**
 * Memainkan efek suara lucu "Pop/Bloop" gelembung gelembung sabun secara digital / sintetis
 * ketika tombol di-klik atau interaksi menu.
 */
export const playBubblePopSound = () => {
  if (isMutedGlobal) return;
  const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
  if (!AudioContextClass) return;
  try {
    const ctx = new AudioContextClass();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    
    osc.type = 'sine';
    // Slide pitch up quickly to sound like a bubbly pop
    osc.frequency.setValueAtTime(140, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.12);
    
    gain.gain.setValueAtTime(0.2, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
    
    osc.connect(gain);
    gain.connect(ctx.destination);
    
    osc.start();
    osc.stop(ctx.currentTime + 0.13);
    setTimeout(() => ctx.close().catch(() => {}), 150);
  } catch (err) {
    console.debug(err);
  }
};

/**
 * Memainkan suara gemerlap "Ting-Ting!" manis ketika kuman dibasmi sampai bersih berkilau.
 */
export const playSparkleSound = () => {
  if (isMutedGlobal) return;
  const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
  if (!AudioContextClass) return;
  try {
    const ctx = new AudioContextClass();
    const now = ctx.currentTime;
    
    const playNote = (delay: number, freq: number, dur: number) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(freq, now + delay);
      
      gain.gain.setValueAtTime(0.0, now);
      gain.gain.setValueAtTime(0.0, now + delay);
      gain.gain.linearRampToValueAtTime(0.12, now + delay + 0.015);
      gain.gain.exponentialRampToValueAtTime(0.001, now + delay + dur);
      
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now + delay);
      osc.stop(now + delay + dur + 0.02);
    };
    
    // Play cute double chime twinkle notes
    playNote(0, 1400, 0.12);
    playNote(0.08, 1900, 0.16);
    
    setTimeout(() => ctx.close().catch(() => {}), 400);
  } catch (err) {
    console.debug(err);
  }
};

/**
 * Memainkan harmoni kemenangan ("Triumph Fanfare") bernada riang gembira
 * berstruktur tangga nada major chord saat permainan berhasil diselesaikan dengan baik.
 */
export const playTriumphFanfare = () => {
  if (isMutedGlobal) return;
  const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
  if (!AudioContextClass) return;
  try {
    const ctx = new AudioContextClass();
    const now = ctx.currentTime;
    
    // Happy major chord notes (F major and A minor blend)
    const chord = [349.23, 440.00, 523.25, 659.25, 880.00];
    chord.forEach((freq, idx) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'triangle'; // warm cartoon-like triangle waves
      
      const noteDelay = idx * 0.07;
      const noteDur = 0.45 - noteDelay;
      
      osc.frequency.setValueAtTime(freq, now + noteDelay);
      
      gain.gain.setValueAtTime(0.0, now);
      gain.gain.setValueAtTime(0.0, now + noteDelay);
      gain.gain.linearRampToValueAtTime(0.08, now + noteDelay + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.001, now + noteDelay + noteDur);
      
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now + noteDelay);
      osc.stop(now + noteDelay + noteDur + 0.05);
    });
    
    setTimeout(() => ctx.close().catch(() => {}), 1000);
  } catch (err) {
    console.debug(err);
  }
};

/**
 * Memainkan efek suara memoles sikat gigi berkali-kali secara digital / sintetis
 * menggunakan audio white-noise dan penyaringan bandpass filter. 
 * Tidak bergantung pada file .mp3 eksternal sehingga dijamin lancar.
 */
export const playBrushSound = () => {
  if (isMutedGlobal) return;
  
  // Batasi panggilan musik berulang-ulang agar tidak macet / spam memori
  const now = Date.now();
  if (now - lastSoundTime < 100) return; 
  lastSoundTime = now;

  const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
  if (!AudioContextClass) return;

  try {
    const ctx = new AudioContextClass();
    
    // Durasi per srek (sekitar 0.08 detik)
    const duration = 0.08;
    const bufferSize = ctx.sampleRate * duration;
    const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
    const data = buffer.getChannelData(0);
    
    // Isi buffer dengan white noise acak
    for (let i = 0; i < bufferSize; i++) {
      data[i] = Math.random() * 2 - 1;
    }
    
    const noiseNode = ctx.createBufferSource();
    noiseNode.buffer = buffer;
    
    // Gunakan Bandpass Filter untuk menyaring frekuensi sehingga mirip gosokan bulu sikat
    const filter = ctx.createBiquadFilter();
    filter.type = 'bandpass';
    filter.frequency.setValueAtTime(1400, ctx.currentTime);
    filter.Q.setValueAtTime(3.0, ctx.currentTime);
    
    // Atur penguat suara (Gain Node) untuk membuat fade-out cepat agar bunyinya berderik ("srek")
    const gainNode = ctx.createGain();
    gainNode.gain.setValueAtTime(0.01, ctx.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.15, ctx.currentTime + 0.015);
    gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
    
    // Menghubungkan node audio
    noiseNode.connect(filter);
    filter.connect(gainNode);
    gainNode.connect(ctx.destination);
    
    noiseNode.start();
    
    // Matikan context setelah selesai play untuk dealloc memori
    setTimeout(() => {
      ctx.close().catch(() => {});
    }, 150);
  } catch (err) {
    // Abaikan jika browser menyuarakan error keamanan (dikarenakan interaksi awal user)
    console.debug("Web Audio API blocked by security context", err);
  }
};
