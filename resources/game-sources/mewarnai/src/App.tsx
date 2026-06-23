import { useState, useRef, useEffect, useCallback } from "react";

// Types
type Screen = "home" | "select" | "coloring" | "result";

type ColoringImage = {
  id: string;
  name: string;
  category: string;
  description: string;
  src: string;
};

// Constants
const GAME_BASE = import.meta.env.BASE_URL;
const MAX_HISTORY = 20;

const coloringImages: ColoringImage[] = [
  {
    id: "panda",
    name: "Panda Lucu",
    category: "Hewan",
    description: "Panda lucu yang siap diwarnai.",
    src: `${GAME_BASE}coloring/panda.png`,
  },
  {
    id: "mobil",
    name: "Mobil Ceria",
    category: "Kendaraan",
    description: "Mobil kecil yang siap berpetualang.",
    src: `${GAME_BASE}coloring/mobil.png`,
  },
  {
    id: "burung",
    name: "Burung Kakaktua",
    category: "Hewan",
    description: "Burung ceria yang bisa kamu warnai.",
    src: `${GAME_BASE}coloring/burung.png`,
  },
  {
    id: "pemandangan",
    name: "Pemandangan Indah",
    category: "Alam",
    description: "Gunung, sungai, pohon, dan matahari ceria.",
    src: `${GAME_BASE}coloring/pemandangan.png`,
  },
  {
    id: "bunga",
    name: "Bunga Ceria",
    category: "Alam",
    description: "Bunga lucu yang siap diberi warna.",
    src: `${GAME_BASE}coloring/bunga.png`,
  },
  {
    id: "ikan",
    name: "Ikan Lucu",
    category: "Hewan",
    description: "Ikan yang berenang di laut.",
    src: `${GAME_BASE}coloring/ikan.png`,
  },
  {
    id: "kupu-kupu",
    name: "Kupu-Kupu Cantik",
    category: "Hewan",
    description: "Kupu-kupu dengan sayap indah.",
    src: `${GAME_BASE}coloring/kupu-kupu.png`,
  },
  {
    id: "rumah",
    name: "Rumah Nyaman",
    category: "Bangunan",
    description: "Rumah yang nyaman untuk diwarnai.",
    src: `${GAME_BASE}coloring/rumah.png`,
  },
    {
    id: "kucing",
    name: "Kucing Lucu",
    category: "Hewan",
    description: "Kucing yang manis dan lucu.",
    src: `${GAME_BASE}coloring/kucing.png`,
  },
];

// All colors in one palette
const allColors: string[] = [
  "#FF1744", "#FF4444", "#FF8A65", "#F97316", "#FB923C",
  "#FFD54F", "#FFC107", "#FF8F00", "#A1887F", "#795548",
  "#CCFF90", "#69F0AE", "#66BB6A", "#81C784", "#2E7D32",
  "#80D8FF", "#4FC3F7", "#29B6F6", "#2196F3", "#1565C0",
  "#F48FB1", "#F472B6", "#CE93D8", "#A78BFA", "#9C27B0",
  "#FFCCBC", "#FFAB91", "#D4A574", "#BCAAA4", "#94A3B8",
  "#FFFFFF", "#E0E0E0", "#64748B", "#1E293B",
];

// Helper Functions
function hexToRgba(hex: string): { r: number; g: number; b: number; a: number } {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  if (!result) return { r: 0, g: 0, b: 0, a: 255 };
  return {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16),
    a: 255,
  };
}

function isDarkPixel(r: number, g: number, b: number): boolean {
  const brightness = (r * 299 + g * 587 + b * 114) / 1000;
  return brightness < 140;
}

function colorDistance(
  r1: number,
  g1: number,
  b1: number,
  r2: number,
  g2: number,
  b2: number
): number {
  return Math.sqrt((r1 - r2) ** 2 + (g1 - g2) ** 2 + (b1 - b2) ** 2);
}

function floodFill(
  imageData: ImageData,
  originalImageData: ImageData,
  startX: number,
  startY: number,
  fillColor: { r: number; g: number; b: number; a: number },
  tolerance: number = 50
): ImageData {
  const data = imageData.data;
  const originalData = originalImageData.data;
  const width = imageData.width;
  const height = imageData.height;

  const startIdx = (startY * width + startX) * 4;
  const startR = data[startIdx];
  const startG = data[startIdx + 1];
  const startB = data[startIdx + 2];

  // Check if starting pixel is an original dark (outline) pixel
  const origStartR = originalData[startIdx];
  const origStartG = originalData[startIdx + 1];
  const origStartB = originalData[startIdx + 2];
  if (isDarkPixel(origStartR, origStartG, origStartB)) {
    return imageData;
  }

  // Check if starting pixel is already black
  if (startR === 0 && startG === 0 && startB === 0) {
    return imageData;
  }

  const dist = colorDistance(startR, startG, startB, fillColor.r, fillColor.g, fillColor.b);
  if (dist < 5) {
    return imageData;
  }

  const stack: [number, number][] = [[startX, startY]];
  const visited = new Set<string>();

  while (stack.length > 0) {
    const [x, y] = stack.pop()!;
    const key = `${x},${y}`;

    if (visited.has(key)) continue;
    if (x < 0 || x >= width || y < 0 || y >= height) continue;

    const idx = (y * width + x) * 4;
    const r = data[idx];
    const g = data[idx + 1];
    const b = data[idx + 2];

    // Check if current pixel is an original dark (outline) pixel
    const origR = originalData[idx];
    const origG = originalData[idx + 1];
    const origB = originalData[idx + 2];
    if (isDarkPixel(origR, origG, origB)) continue;

    // Check if current pixel is already black
    if (r === 0 && g === 0 && b === 0) continue;

    const distance = colorDistance(r, g, b, startR, startG, startB);
    if (distance > tolerance) continue;

    visited.add(key);

    data[idx] = fillColor.r;
    data[idx + 1] = fillColor.g;
    data[idx + 2] = fillColor.b;
    data[idx + 3] = fillColor.a;

    stack.push([x + 1, y], [x - 1, y], [x, y + 1], [x, y - 1]);
  }

  return imageData;
}

function cloneImageData(imageData: ImageData): ImageData {
  const clone = new ImageData(
    new Uint8ClampedArray(imageData.data),
    imageData.width,
    imageData.height
  );
  return clone;
}

function createAudioContext(): AudioContext | null {
  try {
    return new (window.AudioContext || (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext)();
  } catch {
    return null;
  }
}

function playSound(type: "click" | "success" | "error" | "undo" = "click"): void {
  const audioContext = createAudioContext();
  if (!audioContext) return;

  const oscillator = audioContext.createOscillator();
  const gainNode = audioContext.createGain();
  oscillator.connect(gainNode);
  gainNode.connect(audioContext.destination);

  if (type === "click") {
    oscillator.frequency.value = 600;
    oscillator.type = "sine";
    gainNode.gain.setValueAtTime(0.08, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.1);
    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.1);
  } else if (type === "success") {
    oscillator.frequency.value = 800;
    oscillator.type = "sine";
    gainNode.gain.setValueAtTime(0.15, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.25);
    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.25);
    setTimeout(() => {
      const osc2 = audioContext.createOscillator();
      const gain2 = audioContext.createGain();
      osc2.connect(gain2);
      gain2.connect(audioContext.destination);
      osc2.frequency.value = 1050;
      osc2.type = "sine";
      gain2.gain.setValueAtTime(0.15, audioContext.currentTime);
      gain2.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.3);
      osc2.start();
      osc2.stop(audioContext.currentTime + 0.3);
    }, 120);
  } else if (type === "error") {
    oscillator.frequency.value = 220;
    oscillator.type = "square";
    gainNode.gain.setValueAtTime(0.08, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.2);
    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.2);
  } else if (type === "undo") {
    oscillator.frequency.value = 400;
    oscillator.type = "sine";
    gainNode.gain.setValueAtTime(0.08, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.12);
    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.12);
  }
}



export default function App() {
  const [screen, setScreen] = useState<Screen>("home");
  const [selectedImage, setSelectedImage] = useState<ColoringImage | null>(null);
  const [selectedColor, setSelectedColor] = useState<string>(allColors[0]);
  const [soundEnabled, setSoundEnabled] = useState<boolean>(true);
  const [musicEnabled, setMusicEnabled] = useState<boolean>(true);
  const [resultImageDataUrl, setResultImageDataUrl] = useState<string>("");
  const [pointMessage, setPointMessage] = useState<string>("");
  const [earnedPoint, setEarnedPoint] = useState<number | null>(null);
  const [imageLoaded, setImageLoaded] = useState<boolean>(false);
  const [isCanvasReady, setIsCanvasReady] = useState<boolean>(false);
  const [canUndo, setCanUndo] = useState<boolean>(false);
  const [canRedo, setCanRedo] = useState<boolean>(false);
  const audioRef = useRef<HTMLAudioElement | null>(null);
  const logoRef = useRef<HTMLImageElement | null>(null);
  const savingPointRef = useRef<boolean>(false);

  useEffect(() => {
    audioRef.current = new Audio(`${GAME_BASE}sound/backsound.mp3`);
    audioRef.current.loop = true;
    audioRef.current.volume = 0.3;
    
    return () => {
      if (audioRef.current) {
        audioRef.current.pause();
        audioRef.current = null;
      }
    };
  }, []);

  useEffect(() => {
    if (audioRef.current) {
      if (musicEnabled) {
        audioRef.current.play().catch(() => {});
      } else {
        audioRef.current.pause();
      }
    }
  }, [musicEnabled]);

  // Preload logo for watermark
  useEffect(() => {
    const img = new Image();
    img.crossOrigin = "anonymous";
    img.src = `${GAME_BASE}logo-sobat-anak.png`;
    img.onload = () => {
      logoRef.current = img;
    };
  }, []);

  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const originalImageRef = useRef<ImageData | null>(null);
  const undoStackRef = useRef<ImageData[]>([]);
  const redoStackRef = useRef<ImageData[]>([]);

  const syncUndoRedoState = useCallback(() => {
    setCanUndo(undoStackRef.current.length > 0);
    setCanRedo(redoStackRef.current.length > 0);
  }, []);

  const playClickSound = useCallback(
    (type: "click" | "success" | "error" | "undo" = "click") => {
      if (soundEnabled) playSound(type);
    },
    [soundEnabled]
  );

  const navigateTo = useCallback(
    (nextScreen: Screen) => {
      playClickSound("click");
      setScreen(nextScreen);
    },
    [playClickSound]
  );

  const clearHistory = useCallback(() => {
    undoStackRef.current = [];
    redoStackRef.current = [];
    setCanUndo(false);
    setCanRedo(false);
  }, []);

  const handleSelectImage = useCallback(
    (image: ColoringImage) => {
      playClickSound("click");
      setSelectedImage(image);
      setResultImageDataUrl("");
      setImageLoaded(false);
      setIsCanvasReady(false);
      clearHistory();
      setScreen("coloring");
    },
    [playClickSound, clearHistory]
  );

  // Load image into canvas
  useEffect(() => {
    if (screen !== "coloring" || !selectedImage || !canvasRef.current) return;

    const canvas = canvasRef.current;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const img = new Image();
    img.crossOrigin = "anonymous";

    img.onload = () => {
      const maxWidth = window.innerWidth < 768 ? window.innerWidth - 32 : 600;
      const maxHeight = window.innerWidth < 768 ? 400 : 500;

      let width = img.width;
      let height = img.height;

      if (width > maxWidth) {
        const ratio = maxWidth / width;
        width = maxWidth;
        height = height * ratio;
      }
      if (height > maxHeight) {
        const ratio = maxHeight / height;
        height = maxHeight;
        width = width * ratio;
      }

      canvas.width = Math.floor(width);
      canvas.height = Math.floor(height);

      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      originalImageRef.current = cloneImageData(ctx.getImageData(0, 0, canvas.width, canvas.height));

      setImageLoaded(true);
      setIsCanvasReady(true);
    };

    img.onerror = () => {
      canvas.width = 400;
      canvas.height = 400;
      ctx.fillStyle = "#ffffff";
      ctx.fillRect(0, 0, 400, 400);
      ctx.strokeStyle = "#1e293b";
      ctx.lineWidth = 4;
      ctx.beginPath();
      ctx.arc(200, 200, 120, 0, Math.PI * 2);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(100, 100, 40, 0, Math.PI * 2);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(300, 100, 40, 0, Math.PI * 2);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(150, 180, 20, 0, Math.PI * 2);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(250, 180, 20, 0, Math.PI * 2);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(200, 230, 25, 0, Math.PI * 2);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(200, 280, 30, 0, Math.PI);
      ctx.stroke();
      originalImageRef.current = cloneImageData(ctx.getImageData(0, 0, 400, 400));
      setImageLoaded(true);
      setIsCanvasReady(true);
    };

    img.src = selectedImage.src;
  }, [screen, selectedImage]);

  // Core fill logic reused by click and touch
  const applyFill = useCallback(
    (x: number, y: number) => {
      if (!canvasRef.current || !isCanvasReady || !originalImageRef.current) return;

      const canvas = canvasRef.current;
      const ctx = canvas.getContext("2d");
      if (!ctx) return;

      const before = cloneImageData(ctx.getImageData(0, 0, canvas.width, canvas.height));
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const fillColor = hexToRgba(selectedColor);
      const filled = floodFill(imageData, originalImageRef.current, x, y, fillColor, 50);

      // Only push history if something actually changed
      if (filled !== imageData || true) {
        undoStackRef.current.push(before);
        if (undoStackRef.current.length > MAX_HISTORY) {
          undoStackRef.current.shift();
        }
        redoStackRef.current = [];
        syncUndoRedoState();
      }

      ctx.putImageData(filled, 0, 0);
      playClickSound("click");
    },
    [isCanvasReady, selectedColor, playClickSound, syncUndoRedoState]
  );

  const handleCanvasClick = useCallback(
    (e: React.MouseEvent<HTMLCanvasElement>) => {
      if (!canvasRef.current) return;
      const canvas = canvasRef.current;
      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;
      const x = Math.floor((e.clientX - rect.left) * scaleX);
      const y = Math.floor((e.clientY - rect.top) * scaleY);
      applyFill(x, y);
    },
    [applyFill]
  );

  const handleCanvasTouch = useCallback(
    (e: React.TouchEvent<HTMLCanvasElement>) => {
      if (!canvasRef.current) return;
      e.preventDefault();
      const canvas = canvasRef.current;
      const touch = e.touches[0];
      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;
      const x = Math.floor((touch.clientX - rect.left) * scaleX);
      const y = Math.floor((touch.clientY - rect.top) * scaleY);
      applyFill(x, y);
    },
    [applyFill]
  );

  const handleUndo = useCallback(() => {
    if (!canvasRef.current || undoStackRef.current.length === 0) return;

    const canvas = canvasRef.current;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const current = cloneImageData(ctx.getImageData(0, 0, canvas.width, canvas.height));
    redoStackRef.current.push(current);

    const prev = undoStackRef.current.pop()!;
    ctx.putImageData(prev, 0, 0);
    syncUndoRedoState();
    playClickSound("undo");
  }, [playClickSound, syncUndoRedoState]);

  const handleRedo = useCallback(() => {
    if (!canvasRef.current || redoStackRef.current.length === 0) return;

    const canvas = canvasRef.current;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const current = cloneImageData(ctx.getImageData(0, 0, canvas.width, canvas.height));
    undoStackRef.current.push(current);

    const next = redoStackRef.current.pop()!;
    ctx.putImageData(next, 0, 0);
    syncUndoRedoState();
    playClickSound("click");
  }, [playClickSound, syncUndoRedoState]);

  const handleReset = useCallback(() => {
    if (!canvasRef.current || !originalImageRef.current) return;

    const canvas = canvasRef.current;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    ctx.putImageData(originalImageRef.current, 0, 0);
    clearHistory();
    playClickSound("click");
  }, [playClickSound, clearHistory]);

  const saveColoringPoint = useCallback(async () => {
    if (savingPointRef.current) return;
    savingPointRef.current = true;
    setPointMessage("Menyimpan poin karya mewarnai...");
    setEarnedPoint(null);

    const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || "";

    try {
      const response = await fetch("/game/play", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrf,
        },
        body: JSON.stringify({ game: "mewarnai", score: 1 }),
      });

      const payload = await response.json().catch(() => ({}));

      if (!response.ok || !payload.ok) {
        if (payload.redirect) {
          setPointMessage("Login dulu agar poin mewarnai tersimpan ke akun kamu.");
          return;
        }
        throw new Error(payload.message || "Poin belum berhasil tersimpan.");
      }

      const earned = Number(payload.earned || 0);
      setEarnedPoint(earned);
      setPointMessage(payload.message || `Yeay! Kamu dapat ${earned} poin.`);

      document.querySelectorAll("[data-points], [data-points-bottom]").forEach((node) => {
        node.textContent = Number(payload.points || 0).toLocaleString("id-ID");
      });
    } catch (error) {
      console.warn(error);
      setPointMessage("Poin belum tersimpan. Coba login lalu selesaikan gambar lagi ya.");
    }
  }, []);

  const handleComplete = useCallback(() => {
    if (!canvasRef.current) return;
    const dataUrl = canvasRef.current.toDataURL("image/png");
    setResultImageDataUrl(dataUrl);
    setPointMessage("");
    setEarnedPoint(null);
    savingPointRef.current = false;
    playClickSound("success");
    setScreen("result");
    saveColoringPoint();
  }, [playClickSound, saveColoringPoint]);

  const handleDownloadImage = useCallback(() => {
    if (!resultImageDataUrl || !logoRef.current) return;

    // Create a temporary canvas to add watermark
    const tempCanvas = document.createElement('canvas');
    const tempCtx = tempCanvas.getContext('2d');
    if (!tempCtx) return;

    // Load the result image
    const img = new Image();
    img.crossOrigin = "anonymous";
    img.onload = () => {
      // Set temp canvas size to match the image
      tempCanvas.width = img.width;
      tempCanvas.height = img.height;

      // Draw the original image
      tempCtx.drawImage(img, 0, 0);

      // Draw watermark logo at bottom right
      const logoWidth = Math.min(100, tempCanvas.width * 0.2);
      const logoHeight = (logoRef.current.height / logoRef.current.width) * logoWidth;
      const logoX = tempCanvas.width - logoWidth - 20;
      const logoY = tempCanvas.height - logoHeight - 20;

      tempCtx.globalAlpha = 0.7; // Make watermark semi-transparent
      tempCtx.drawImage(logoRef.current, logoX, logoY, logoWidth, logoHeight);
      tempCtx.globalAlpha = 1.0;

      // Create download link
      const link = document.createElement('a');
      link.download = `warna-${selectedImage?.id || 'gambar'}-${Date.now()}.png`;
      link.href = tempCanvas.toDataURL('image/png');
      link.click();

      playClickSound("success");
    };
    img.src = resultImageDataUrl;
  }, [resultImageDataUrl, selectedImage, playClickSound]);

  // Screens
  const renderHome = () => (
    <div className="screen home-screen">
      <div className="home-content">
        <div className="logo-section">
          <div className="brand-logo">
            <img src={`${GAME_BASE}logo-sobat-anak.png`} alt="Logo Sobat Anak" className="brand-icon" />
            <span className="brand-name"></span>
          </div>
          <h1 className="game-title">Coloring Fun Kids</h1>
          <p className="game-subtitle">Warnai gambar favoritmu dengan warna ceria!</p>
        </div>
        <div className="home-illustration">
          <div className="floating-shape shape-1"></div>
          <div className="floating-shape shape-2"></div>
          <div className="floating-shape shape-3"></div>
          <div className="floating-shape shape-4"></div>
          <div className="main-illustration">
            <img src={`${GAME_BASE}logo-game.png`} alt="Game Icon" className="illustration-icon" />
          </div>
        </div>
        <div className="home-actions">
          <button className="btn btn-primary btn-large" onClick={() => navigateTo("select")}>
            <span className="btn-icon">✏️</span>
            Mulai Mewarnai
          </button>
        </div>
      </div>
    </div>
  );

  const renderSelect = () => (
    <div className="screen select-screen">
      <div className="screen-header">
        <button className="btn btn-back" onClick={() => navigateTo("home")}>
          ← Kembali
        </button>
        <div className="screen-titles">
          <h1 className="screen-title">Pilih Gambar</h1>
          <p className="screen-subtitle">Mau mewarnai apa hari ini?</p>
        </div>
      </div>
      <div className="image-grid">
        {coloringImages.map((image) => (
          <div key={image.id} className="image-card">
            <div className="image-preview">
              <img
                src={image.src}
                alt={image.name}
              />
            </div>
            <div className="image-info">
              <span className="image-category">{image.category}</span>
              <h3 className="image-name">{image.name}</h3>
              <p className="image-desc">{image.description}</p>
            </div>
            <button className="btn btn-primary btn-full" onClick={() => handleSelectImage(image)}>
              <span className="btn-icon">✏️</span>
              Warnai
            </button>
          </div>
        ))}
      </div>
    </div>
  );

  const renderColoring = () => (
    <div className="screen coloring-screen">
      <div className="coloring-header">
        <button className="btn btn-back" onClick={() => navigateTo("select")}>
          ← Kembali
        </button>
        <h1 className="coloring-title">{selectedImage?.name || "Mewarnai"}</h1>
        {/* Undo / Redo */}
        <div className="undo-redo-group">
          <button
            className="btn btn-tool"
            onClick={handleUndo}
            disabled={!canUndo}
            title="Undo (batalkan)"
          >
            ↩ Undo
          </button>
          <button
            className="btn btn-tool"
            onClick={handleRedo}
            disabled={!canRedo}
            title="Redo (ulangi)"
          >
            Redo ↪
          </button>
        </div>
      </div>

      <div className="canvas-container">
        {!imageLoaded && <div className="loading-indicator">Memuat gambar...</div>}
        <canvas
          ref={canvasRef}
          className="coloring-canvas"
          onClick={handleCanvasClick}
          onTouchStart={handleCanvasTouch}
        />
      </div>

      {/* Palette — simple */}
      <div className="palette-section">
        <h3 className="palette-title">Pilih Warna</h3>
        <div className="palette-row">
          {allColors.map((color) => (
            <button
              key={color}
              className={`color-btn ${selectedColor === color ? "active" : ""}`}
              style={{
                backgroundColor: color,
                outline: color === "#FFFFFF" ? "1px solid #e2e8f0" : "none",
              }}
              onClick={() => {
                setSelectedColor(color);
                playClickSound("click");
              }}
              title={color}
            />
          ))}
        </div>
        {/* Selected color preview */}
        <div className="selected-color-bar">
          <div
            className="selected-color-swatch"
            style={{
              backgroundColor: selectedColor,
              outline: selectedColor === "#FFFFFF" ? "1px solid #e2e8f0" : "none",
            }}
          />
          <span className="selected-color-label">Warna dipilih</span>
          <span className="selected-color-hex">{selectedColor}</span>
        </div>
      </div>

      <div className="coloring-actions">
        <button className="btn btn-secondary" onClick={handleReset} title="Hapus semua warna">
          <span className="btn-icon">🔄</span>
          Reset
        </button>
        <button className="btn btn-primary" onClick={handleComplete}>
          <span className="btn-icon">✅</span>
          Selesai
        </button>
      </div>
    </div>
  );

  const renderResult = () => (
    <div className="screen result-screen">
      <div className="screen-header">
        <h1 className="screen-title">Karya Hebatmu!</h1>
        <p className="screen-subtitle">Wah, warnanya cantik sekali!</p>
        <div className="result-point-card">
          <strong>{earnedPoint ? `+${earnedPoint} poin berhasil ditambahkan` : "Bonus Poin Mewarnai"}</strong>
          <span>{pointMessage || "Selesaikan gambar untuk menyimpan poin kecil ke akunmu."}</span>
        </div>
      </div>
      <div className="result-preview">
        {resultImageDataUrl ? (
          <img src={resultImageDataUrl} alt="Hasil mewarnai" className="result-image" />
        ) : (
          <div className="no-result">Tidak ada hasil</div>
        )}
      </div>
      <div className="result-actions">
        <button className="btn btn-primary" onClick={handleDownloadImage}>
          <span className="btn-icon">💾</span>
          Download Gambar
        </button>
        <button
          className="btn btn-secondary"
          onClick={() => selectedImage && handleSelectImage(selectedImage)}
        >
          <span className="btn-icon">✏️</span>
          Warnai Lagi
        </button>
        <button className="btn btn-secondary" onClick={() => navigateTo("select")}>
          <span className="btn-icon">🖼️</span>
          Pilih Gambar Lain
        </button>
      </div>
    </div>
  );

  return (
    <div className="app">
      {screen === "home" && renderHome()}
      {screen === "select" && renderSelect()}
      {screen === "coloring" && renderColoring()}
      {screen === "result" && renderResult()}



      {/* Global music toggle — fixed bottom-left */}
      <button
        className="sound-fab"
        onClick={() => setMusicEnabled(!musicEnabled)}
        title={musicEnabled ? "Matikan Musik" : "Nyalakan Musik"}
        aria-label={musicEnabled ? "Matikan Musik" : "Nyalakan Musik"}
      >
        {musicEnabled ? "🎵" : "🔇"}
      </button>
    </div>
  );
}

