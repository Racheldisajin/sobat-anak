const audioPaths = {
  menuBg: import.meta.env.BASE_URL + "audio/bg-menu.mp3",
  gameBg: import.meta.env.BASE_URL + "audio/bg-game.mp3",
  click: import.meta.env.BASE_URL + "audio/click.mp3",
  flip: import.meta.env.BASE_URL + "audio/flip.mp3",
  match: import.meta.env.BASE_URL + "audio/match.mp3",
  wrong: import.meta.env.BASE_URL + "audio/wrong.mp3",
  win: import.meta.env.BASE_URL + "audio/win.mp3",
  lose: import.meta.env.BASE_URL + "audio/lose.mp3",
};

let isBgMusicMuted = false;
let currentBgMusic: HTMLAudioElement | null = null;
let currentBgType: "menu" | "game" | null = null;
const soundEffects: Map<string, HTMLAudioElement> = new Map();

// Initialize sound effect elements
Object.entries(audioPaths).forEach(([key, path]) => {
  if (key !== "menuBg" && key !== "gameBg") {
    const audio = new Audio(path);
    audio.preload = "auto";
    soundEffects.set(key, audio);
  }
});

export const audioManager = {
  setBgMusicMuted(muted: boolean) {
    isBgMusicMuted = muted;
    if (muted) {
      this.stopBackgroundMusic();
    } else if (currentBgType) {
      this.playBackgroundMusic(currentBgType);
    }
  },

  getBgMusicMuted() {
    return isBgMusicMuted;
  },

  playSound(type: keyof typeof audioPaths) {
    if (type === "menuBg" || type === "gameBg") return;

    const audio = soundEffects.get(type);
    if (audio) {
      audio.currentTime = 0;
      audio.play().catch(() => {});
    }
  },

  playBackgroundMusic(type: "menu" | "game") {
    if (isBgMusicMuted) return;

    const path = type === "menu" ? audioPaths.menuBg : audioPaths.gameBg;

    if (currentBgType === type && currentBgMusic) {
      // Already playing the right music
      return;
    }

    this.stopBackgroundMusic();

    currentBgMusic = new Audio(path);
    currentBgMusic.loop = true;
    currentBgMusic.volume = 0.3;
    currentBgMusic.play().catch(() => {});
    currentBgType = type;
  },

  stopBackgroundMusic() {
    if (currentBgMusic) {
      currentBgMusic.pause();
      currentBgMusic = null;
    }
    currentBgType = null;
  },

  switchBackgroundMusic(type: "menu" | "game") {
    this.playBackgroundMusic(type);
  },
};
