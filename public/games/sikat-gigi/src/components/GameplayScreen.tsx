import React, { useState, useEffect, useRef } from 'react';
import { Tooth, Stain } from '../types';
import { TEETH_DATA, generateStains, playBrushSound, playSparkleSound } from '../data';

interface GameplayScreenProps {
  onGameEnd: (score: number) => void;
  setTimeLeftParent: (time: number) => void;
  setCleanPercentageParent: (pct: number) => void;
}

export const GameplayScreen: React.FC<GameplayScreenProps> = ({
  onGameEnd,
  setTimeLeftParent,
  setCleanPercentageParent,
}) => {
  // Gameplay States
  const [stains, setStains] = useState<Stain[]>([]);
  const [timeLeft, setTimeLeft] = useState<number>(30);
  const [cleanPercentage, setCleanPercentage] = useState<number>(0);
  const [activeBrushPos, setActiveBrushPos] = useState<{ x: number; y: number } | null>(null);
  const [isBrushing, setIsBrushing] = useState<boolean>(false);
  const [isVictoryCelebration, setIsVictoryCelebration] = useState<boolean>(false);
  const [celebrationSparkles, setCelebrationSparkles] = useState<any[]>([]);
  
  const gameplayContainerRef = useRef<HTMLDivElement>(null);

  // Inisialisasi noda gigi (Stains)
  useEffect(() => {
    setStains(generateStains());
    setTimeLeft(30);
    setCleanPercentage(0);
    setTimeLeftParent(30);
    setCleanPercentageParent(0);
    setIsVictoryCelebration(false);
    setCelebrationSparkles([]);
  }, []);

  // Hitung Mundur Otomatis (Countdown timer 30s)
  useEffect(() => {
    if (isVictoryCelebration) {
      return;
    }
    if (timeLeft <= 0) {
      // Selesaikan game jika waktu habis
      onGameEnd(cleanPercentage);
      return;
    }

    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        const nextTime = Math.max(0, prev - 1);
        setTimeLeftParent(nextTime);
        return nextTime;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [timeLeft, cleanPercentage, onGameEnd, setTimeLeftParent, isVictoryCelebration]);

  /**
   * LOGIKA PERHITUNGAN PERSENTASE KEBERSIHAN (OPACITY CALCULATION)
   * 
   * Opasitas awal setiap noda/kuman dimulai dari nilai 1.0 (sangat kotor).
   * Semakin banyak anak menyikat gigi tersebut, nilai opasitas noda berkurang secara bertahap menuju 0.0 (bersih sempurna).
   * Formula Kebersihan:
   * Total Noda Maksimal = Jumlah Noda * 1.0 (Skala 100% kotor)
   * Kotoran Tersisa      = Penjumlahan (Opacity saat ini untuk semua noda)
   * Kebersihan Gigi (%)  = ((Total Noda Maksimal - Kotoran Tersisa) / Total Noda Maksimal) * 100
   */
  const updateCleanPercentage = (currentStains: Stain[]) => {
    if (isVictoryCelebration) return;
    const totalMaxDirt = currentStains.length; // Tiap noda bernilai maksimal 1.0
    const remainingDirt = currentStains.reduce((sum, stain) => sum + stain.opacity, 0);
    
    let percentage = 0;
    if (totalMaxDirt > 0) {
      percentage = Math.round(((totalMaxDirt - remainingDirt) / totalMaxDirt) * 100);
    }
    
    // Batasi dalam rentang 0% hingga 100%
    const finalPct = Math.min(100, Math.max(0, percentage));
    setCleanPercentage(finalPct);
    setCleanPercentageParent(finalPct);

    // Jika mencapai tingkat kebersihan 100%, tampilkan perayaan berkilau dulu!
    if (finalPct === 100) {
      setIsVictoryCelebration(true);
      
      // Buat data kilauan bintang-bintang yang elegan langsung di area gigi (atas & bawah)
      const targetPositions = [
        { left: 16, top: 12 },
        { left: 32, top: 10 },
        { left: 50, top: 8 },
        { left: 68, top: 10 },
        { left: 84, top: 12 },
        { left: 20, top: 82 },
        { left: 38, top: 84 },
        { left: 50, top: 85 },
        { left: 62, top: 84 },
        { left: 80, top: 82 },
      ];

      const sparkles = targetPositions.map((pos, idx) => ({
        id: idx,
        emoji: '✨', // Hanya bintang berkilau kuning emas yang estetik & bersih
        left: pos.left,
        top: pos.top,
        delay: idx * 0.15, // Delay bertahap agar kerlap-kerlip terkesan dinamis dan anggun
        duration: 1.2,
        size: 32, // Ukuran pas untuk kilauan
      }));

      setCelebrationSparkles(sparkles);

      // Mainkan suara twinkle berkilau manis berganti-gantian
      let playedTimes = 0;
      const twinkleChimes = setInterval(() => {
        if (playedTimes < 6) {
          playSparkleSound();
          playedTimes++;
        } else {
          clearInterval(twinkleChimes);
        }
      }, 300);

      // Tahan di layar selama 2.8 detik agar anak puas melihat giginya yang berkilau bersih
      setTimeout(() => {
        clearInterval(twinkleChimes);
        onGameEnd(100);
      }, 2800);
    }
  };

  /**
   * Logika Utama Pengurangan Opasitas Noda saat Tergosok (Brushing Tick Action)
   */
  const brushStain = (stainId: string) => {
    let playedSound = false;

    setStains((prevStains) => {
      const updated = prevStains.map((stain) => {
        if (stain.id === stainId && stain.opacity > 0) {
          // Mainkan efek suara srek srek srek yang disintesis digital
          if (!playedSound) {
            playBrushSound();
            playedSound = true;
          }
          // Kurangi opasitas noda sebanyak 12% per gosokan agar terasa bernilai rub/sikat
          const newOpacity = Math.max(0, stain.opacity - 0.12);
          
          // Mainkan efek gemerlap/sparkle bila kuman berhasil dibasmi total
          if (newOpacity === 0) {
            setTimeout(() => {
              playSparkleSound();
            }, 0);
          }
          
          return { ...stain, opacity: newOpacity };
        }
        return stain;
      });

      // Jadwalkan untuk kalkulasi ulang persentase kebersihan gigi
      setTimeout(() => updateCleanPercentage(updated), 0);
      return updated;
    });
  };

  // Tracking Mouse Koordinat untuk visual sikat gigi kustom di desktop
  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!gameplayContainerRef.current) return;
    const rect = gameplayContainerRef.current.getBoundingClientRect();
    setActiveBrushPos({
      x: e.clientX - rect.left,
      y: e.clientY - rect.top,
    });
    setIsBrushing(true);
  };

  const handleMouseLeave = () => {
    setActiveBrushPos(null);
    setIsBrushing(false);
  };

  const handleMouseEnter = () => {
    setIsBrushing(true);
  };

  /**
   * JEMBATAN INTERAKSI TOUCH-TO-DESKTOP (TOUCHSCREEN BRIDGING LOGIC)
   * 
   * Pada ponsel pintar / tablet (touch), event "onMouseEnter" atau "onMouseMove"
   * tidak akan dipicu di atas elemen-elemen individual saat jari digeser/drag, 
   * karena target event dikunci ke elemen pertama tempat sentuhan dimulai.
   * 
   * Solusinya:
   * 1. Kita pasang event listner 'onTouchMove' pada kotak container game utama.
   * 2. Ambil titik kooordinat jari saat ini: (touch.clientX, touch.clientY).
   * 3. Gunakan 'document.elementFromPoint(x, y)' untuk mencuri elemen DOM di bawah jari anak tersebut secara real-time.
   * 4. Jika terdeteksi atribut 'data-stain-id', pemicu sikat noda (brushStain) langsung dieksekusi.
   */
  const handleTouchMove = (e: React.TouchEvent<HTMLDivElement>) => {
    if (!gameplayContainerRef.current) return;
    
    // Cari element sentuhan pertama
    const touch = e.touches[0];
    if (!touch) return;

    // Hitung posisi relatif kuil sikat untuk menampilkan animasi sikat gigi mengikuti jari anak
    const rect = gameplayContainerRef.current.getBoundingClientRect();
    setActiveBrushPos({
      x: touch.clientX - rect.left,
      y: touch.clientY - rect.top,
    });
    setIsBrushing(true);

    // Deteksi target elemen di bawah koordinat layar sentuh saat ini
    const element = document.elementFromPoint(touch.clientX, touch.clientY);
    if (element) {
      const stainId = element.getAttribute('data-stain-id');
      if (stainId) {
        brushStain(stainId);
      }
    }
  };

  const handleTouchEnd = () => {
    setActiveBrushPos(null);
    setIsBrushing(false);
  };

  // Dynamic offset calculation for pupils following the brush/cursor position
  const getPupilTransform = (eyeSide: 'left' | 'right') => {
    if (!activeBrushPos || !gameplayContainerRef.current) {
      return { transform: 'translate(0px, 0px)' };
    }
    const rect = gameplayContainerRef.current.getBoundingClientRect();
    const centerX = rect.width / 2;
    // Eye points are approximately offset from center
    const eyeX = eyeSide === 'left' ? centerX - 24 : centerX + 24;
    const eyeY = 40; // Approximate offset from top of game container for eyes

    const dx = activeBrushPos.x - eyeX;
    const dy = activeBrushPos.y - eyeY;
    const distance = Math.sqrt(dx * dx + dy * dy) || 1;
    
    // We want the pupil to move inside the white eye container.
    // w-10 (40px) white eye, w-4 (16px) pupil. Remaining radius is ~12px.
    // Let's constrain the max shift to 8px so it stays inside beautifully.
    const maxShift = 8;
    const shiftX = (dx / distance) * Math.min(maxShift, distance * 0.12);
    const shiftY = (dy / distance) * Math.min(maxShift, distance * 0.12);

    return {
      transform: `translate(${shiftX}px, ${shiftY}px)`,
    };
  };

  // Kelompokkan gigi per baris agar mudah digambar
  const topTeeth = TEETH_DATA.filter((t) => t.position === 'top');
  const bottomTeeth = TEETH_DATA.filter((t) => t.position === 'bottom');

  return (
    <div className="relative w-full h-full flex flex-col items-center justify-center select-none">
      
      {/* Container Game UTAMA (Mulut Monster Mumu) */}
      <div
        id="viewport-monster-mouth"
        ref={gameplayContainerRef}
        onMouseMove={handleMouseMove}
        onMouseLeave={handleMouseLeave}
        onMouseEnter={handleMouseEnter}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
        onTouchStart={handleTouchMove} // mulakan langsung saat sentuh pertama
        className="cursor-toothbrush-grip relative w-full h-[410px] overflow-hidden p-4 flex flex-col items-center justify-between rounded-2xl bg-[#E0F7FA]"
      >

        {/* Mata Monster Diam di Atas Mulut - Pupil Mengikuti Kursor */}
        <div className="absolute top-2 flex space-x-12 z-10">
          {/* Mata Kiri */}
          <div className="w-16 h-16 bg-[#3B82F6] border-comic rounded-full flex items-center justify-center relative shadow-sm">
            <div className="w-10 h-10 bg-white rounded-full flex items-center justify-center border-comic-thin overflow-hidden">
              <div 
                className="w-5 h-5 bg-slate-800 rounded-full transition-transform duration-75 ease-out"
                style={getPupilTransform('left')}
              ></div>
            </div>
          </div>
          {/* Mata Kanan */}
          <div className="w-16 h-16 bg-[#3B82F6] border-comic rounded-full flex items-center justify-center relative shadow-sm">
            <div className="w-10 h-10 bg-white rounded-full flex items-center justify-center border-comic-thin overflow-hidden">
              <div 
                className="w-5 h-5 bg-slate-800 rounded-full transition-transform duration-75 ease-out"
                style={getPupilTransform('right')}
              ></div>
            </div>
          </div>
        </div>

        {/* Background Mulut Terbuka Lebar (Deep Crimson Bowl) */}
        <div className="relative w-full max-w-xl h-full mt-10 mb-2 bg-[#4E0C1B] rounded-t-[100px] rounded-b-[100px] border-comic flex flex-col justify-between items-center pt-2 pb-2 px-4 z-0 overflow-hidden">
          
          {/* Lidah Merah Muda Monster */}
          <div className="absolute bottom-4 w-44 h-16 bg-[#FF8A65] rounded-t-full opacity-70 border-comic-thin border-b-0"></div>

          {/* ================= BARIS GIGI ATAS ================= */}
          <div className="flex justify-around w-full max-w-[560px] z-20">
            {topTeeth.map((tooth) => {
              // Dapatkan semua noda yang tertempel di Gigi ini
              const toothStains = stains.filter((s) => s.toothId === tooth.id);
              
              return (
                <div
                  key={tooth.id}
                  className="relative w-[14%] h-14 bg-white border-2 border-[#1E2939] rounded-b-2xl shadow-sm flex flex-col justify-end items-center pb-2 select-none"
                  style={{
                    backgroundColor: '#FFFFFF',
                    borderTop: '0px',
                  }}
                >

                  {/* Render Noda Kuman di permukaan Gigi ini */}
                  {toothStains.map((stain) => {
                    const isFullyCleaned = stain.opacity <= 0;
                    
                    return (
                      <div
                        key={stain.id}
                        data-stain-id={stain.id}
                        onMouseEnter={() => brushStain(stain.id)}
                        onMouseMove={() => brushStain(stain.id)}
                        className={`absolute select-none cursor-pointer flex items-center justify-center transition-all ${
                          isFullyCleaned ? 'pointer-events-none scale-0 opacity-0' : 'pointer-events-auto'
                        }`}
                        style={{
                          left: `${stain.x - 20}%`,
                          top: `${stain.y - 10}%`,
                          width: `${stain.size}px`,
                          height: `${stain.size}px`,
                          opacity: stain.opacity,
                          transform: `rotate(${stain.angle}deg)`,
                        }}
                      >
                        {/* Area Target sentuhan transparan diperluas (untuk jari anak yang gemuk) */}
                        <div 
                          data-stain-id={stain.id}
                          className="absolute w-12 h-12 bg-transparent -m-4 rounded-full z-10"
                        ></div>

                        {/* Rendering Visual Kuman Sesuai Tipenya */}
                        {stain.type === 'bacteria' && (
                          <div 
                            data-stain-id={stain.id}
                            className="w-full h-full rounded-full border border-slate-800 flex items-center justify-center animate-pulse"
                            style={{ backgroundColor: stain.color }}
                          >
                            {/* Comical Germ Face */}
                            <div className="flex flex-col items-center pointer-events-none">
                              <div className="flex space-x-0.5">
                                <div className="w-1.5 h-1.5 bg-white rounded-full flex items-center justify-center">
                                  <div className="w-[1px] h-[1px] bg-black rounded-full"></div>
                                </div>
                                <div className="w-1.5 h-1.5 bg-white rounded-full flex items-center justify-center">
                                  <div className="w-[1px] h-[1px] bg-black rounded-full"></div>
                                </div>
                              </div>
                              <div className="w-2 h-[2px] bg-slate-800 rounded-full mt-0.5"></div>
                            </div>
                          </div>
                        )}

                        {stain.type === 'yellow' && (
                          <div
                            data-stain-id={stain.id}
                            className="w-full h-full rounded-xl border border-amber-900 opacity-95 flex items-center justify-center"
                            style={{ backgroundColor: stain.color, borderRadius: '40% 50% 30% 60%' }}
                          >
                            <span data-stain-id={stain.id} className="text-[10px] pointer-events-none">👾</span>
                          </div>
                        )}

                        {stain.type === 'cookie' && (
                          <div
                            data-stain-id={stain.id}
                            className="w-full h-full rounded-full border border-amber-950 flex items-center justify-center"
                            style={{ backgroundColor: stain.color, transform: 'scale(0.85)' }}
                          >
                            <span data-stain-id={stain.id} className="text-[8px] font-black text-amber-200 pointer-events-none">🍪</span>
                          </div>
                        )}
                      </div>
                    );
                  })}
                </div>
              );
            })}
          </div>

          {/* Sparkles / Bintang Efek Berkilau Gantung dihilangkan sesuai permintaan user */}
          <div className="flex space-x-12 opacity-0 z-10 select-none pointer-events-none h-6">
          </div>

          {/* ================= BARIS GIGI BAWAH ================= */}
          <div className="flex justify-around w-full max-w-[560px] z-20">
            {bottomTeeth.map((tooth) => {
              const toothStains = stains.filter((s) => s.toothId === tooth.id);
              
              return (
                <div
                  key={tooth.id}
                  className="relative w-[14%] h-14 bg-white border-2 border-[#1E2939] rounded-t-2xl shadow-sm flex flex-col justify-start items-center pt-2 select-none"
                  style={{
                    backgroundColor: '#FFFFFF',
                    borderBottom: '0px',
                  }}
                >
                  {/* Render Noda Kuman */}
                  {toothStains.map((stain) => {
                    const isFullyCleaned = stain.opacity <= 0;
                    
                    return (
                      <div
                        key={stain.id}
                        data-stain-id={stain.id}
                        onMouseEnter={() => brushStain(stain.id)}
                        onMouseMove={() => brushStain(stain.id)}
                        className={`absolute select-none cursor-pointer flex items-center justify-center transition-all ${
                          isFullyCleaned ? 'pointer-events-none scale-0 opacity-0' : 'pointer-events-auto'
                        }`}
                        style={{
                          left: `${stain.x - 20}%`,
                          top: `${stain.y - 10}%`,
                          width: `${stain.size}px`,
                          height: `${stain.size}px`,
                          opacity: stain.opacity,
                          transform: `rotate(${stain.angle}deg)`,
                        }}
                      >
                        {/* Area sentuhan transparan diperbesar */}
                        <div 
                          data-stain-id={stain.id}
                          className="absolute w-12 h-12 bg-transparent -m-4 rounded-full z-10"
                        ></div>

                        {stain.type === 'bacteria' && (
                          <div 
                            data-stain-id={stain.id}
                            className="w-full h-full rounded-full border border-slate-800 flex items-center justify-center animate-pulse"
                            style={{ backgroundColor: stain.color }}
                          >
                            <div className="flex flex-col items-center pointer-events-none">
                              <div className="flex space-x-0.5">
                                <div className="w-1.5 h-1.5 bg-white rounded-full flex items-center justify-center">
                                  <div className="w-[1px] h-[1px] bg-black rounded-full"></div>
                                </div>
                                <div className="w-1.5 h-1.5 bg-white rounded-full flex items-center justify-center">
                                  <div className="w-[1px] h-[1px] bg-black rounded-full"></div>
                                </div>
                              </div>
                              <div className="w-2.5 h-[2px] bg-slate-800 rounded-full mt-0.5"></div>
                            </div>
                          </div>
                        )}

                        {stain.type === 'yellow' && (
                          <div
                            data-stain-id={stain.id}
                            className="w-full h-full rounded-xl border border-amber-900 opacity-95 flex items-center justify-center"
                            style={{ backgroundColor: stain.color, borderRadius: '50% 30% 60% 40%' }}
                          >
                            <span data-stain-id={stain.id} className="text-[10px] pointer-events-none">👾</span>
                          </div>
                        )}

                        {stain.type === 'cookie' && (
                          <div
                            data-stain-id={stain.id}
                            className="w-full h-full rounded-full border border-amber-950 flex items-center justify-center"
                            style={{ backgroundColor: stain.color, transform: 'scale(0.85)' }}
                          >
                            <span data-stain-id={stain.id} className="text-[8px] font-black text-amber-200 pointer-events-none">🍪</span>
                          </div>
                        )}
                      </div>
                    );
                  })}

                </div>
              );
            })}
          </div>

          {/* Victory Celebration Overlay inside the Deep Crimson Bowl */}
          {isVictoryCelebration && (
            <div className="absolute inset-0 bg-black/10 flex flex-col items-center justify-center z-30 pointer-events-none select-none rounded-[100px]">
              {/* Twinkling star structures */}
              {celebrationSparkles.map((sparkle) => (
                <div
                  key={sparkle.id}
                  className="absolute animate-twinkle text-yellow-300 select-none z-30"
                  style={{
                    left: `${sparkle.left}%`,
                    top: `${sparkle.top}%`,
                    fontSize: `${sparkle.size}px`,
                    animationDelay: `${sparkle.delay}s`,
                    animationDuration: `${sparkle.duration}s`,
                    textShadow: '0 0 16px rgba(255, 235, 59, 1), 0 0 6px #ffffff',
                  }}
                >
                  {sparkle.emoji}
                </div>
              ))}
            </div>
          )}

        </div>

        {/* ================= SIKAT GIGI INTERAKTIF (Visual Brush Tracking) ================= */}
        {activeBrushPos && (
          <div
            className="absolute pointer-events-none z-30 transition-transform duration-75 select-none"
            style={{
              left: `${activeBrushPos.x}px`,
              top: `${activeBrushPos.y}px`,
              transform: `translate(-20px, -40px) ${isBrushing ? 'rotate(-15deg) scale(1.1)' : 'rotate(0deg)'}`,
            }}
          >
            {/* Gagang Sikat Gigi Lucu dan Busa Sabun Sikat Gigi */}
            <div className="relative">
              {/* Gelembung Busa Sabun (Muncul saat digosokkan) */}
              {isBrushing && (
                <div className="absolute -top-4 -left-4 flex space-x-1">
                  <span className="text-sm animate-ping">🫧</span>
                  <span className="text-xs animate-bounce">🫧</span>
                  <span className="text-sm animate-pulse">🫧</span>
                </div>
              )}
              
              {/* Ikon Sikat Gigi Besar */}
              <div className="text-4xl filter drop-shadow-[2px_3px_0px_rgba(30,41,57,1)] select-none">
                🪥
              </div>
            </div>
          </div>
        )}

        {/* Koin Panduan Sentuh/Gerak untuk Gadget Selular */}
        <div className="absolute bottom-1 right-2 bg-slate-800 text-white rounded-lg px-2 py-0.5 text-[9px] font-bold opacity-30">
          Geser jari atau gerakkan mouse untuk menyikat!
        </div>
      </div>

    </div>
  );
};
