import React, { useState, useEffect, useRef, useCallback, useMemo } from 'react';
import { PuzzleData, PieceState } from '../types';
import { generateJigsawPath } from '../utils/jigsawPath';
import { SNAP_THRESHOLD, GAME_TIME_SECONDS } from '../constants';
import SuccessModal from './SuccessModal';

interface GameBoardProps {
  puzzle: PuzzleData;
  onExit: () => void;
  onWin: () => void;
}

interface WrongFeedback {
  x: number;
  y: number;
  id: number;
}

const MAX_PIECE_SIZE = 110;
const MIN_PIECE_SIZE = 64;
const BOARD_BORDER = 14;
const PIECE_PADDING = 48;
const SHELF_HEIGHT = 128;
const SIDEBAR_WIDTH = 256;

// Ukuran visual minimal papan.
// Level mudah tetap jumlah piece sedikit, tetapi ukuran papan disamakan dengan level sedang.
const MIN_VISUAL_ROWS = 3;
const MIN_VISUAL_COLS = 4;

const GameBoard: React.FC<GameBoardProps> = ({ puzzle, onExit, onWin }) => {
  const [pieces, setPieces] = useState<PieceState[]>([]);
  const [isFinished, setIsFinished] = useState(false);
  const [isTimeUp, setIsTimeUp] = useState(false);
  const [activePieceId, setActivePieceId] = useState<string | null>(null);
  const [wrongFeedback, setWrongFeedback] = useState<WrongFeedback | null>(null);

  const [isStarting, setIsStarting] = useState(true);
  const [countdown, setCountdown] = useState<number | 'GO' | null>(null);

  const [score, setScore] = useState(0);
  const [timeLeft, setTimeLeft] = useState(GAME_TIME_SECONDS);
  const [scoreAnim, setScoreAnim] = useState(false);

  const [baseUnitSize, setBaseUnitSize] = useState(MAX_PIECE_SIZE);
  const [boardOffset, setBoardOffset] = useState({ x: 0, y: 0 });

  const tableRef = useRef<HTMLDivElement>(null);
  const scoreAnimTimerRef = useRef<number | null>(null);
  const wrongFeedbackTimerRef = useRef<number | null>(null);

  const totalPieces = pieces.length;
  const lockedPieces = useMemo(() => pieces.filter((p) => p.isLocked).length, [pieces]);

  const visualRows = Math.max(puzzle.rows, MIN_VISUAL_ROWS);
  const visualCols = Math.max(puzzle.cols, MIN_VISUAL_COLS);

  // Ukuran papan visual final.
  const boardWidth = visualCols * baseUnitSize;
  const boardHeight = visualRows * baseUnitSize;

  // Ukuran actual tiap keping agar level mudah tetap mengisi board penuh.
  // Contoh easy 2x3 pada board 3x4: piece otomatis lebih tinggi/lebar sehingga tidak ada space kosong.
  const pieceWidth = boardWidth / puzzle.cols;
  const pieceHeight = boardHeight / puzzle.rows;
  const maxPieceDimension = Math.max(pieceWidth, pieceHeight);

  const isGameActive = !isFinished && !isTimeUp && countdown === null && !isStarting;

  const calculateBoardLayout = useCallback(() => {
    if (!tableRef.current) return;

    const rect = tableRef.current.getBoundingClientRect();

    const safePaddingX = 80;
    const safePaddingY = 80;

    const availableW = Math.max(200, rect.width - safePaddingX);
    const availableH = Math.max(200, rect.height - safePaddingY);

    const nextVisualCols = Math.max(puzzle.cols, MIN_VISUAL_COLS);
    const nextVisualRows = Math.max(puzzle.rows, MIN_VISUAL_ROWS);

    const nextBaseUnitSize = Math.floor(
      Math.min(
        MAX_PIECE_SIZE,
        Math.max(
          MIN_PIECE_SIZE,
          Math.min(availableW / nextVisualCols, availableH / nextVisualRows)
        )
      )
    );

    const nextBoardW = nextVisualCols * nextBaseUnitSize;
    const nextBoardH = nextVisualRows * nextBaseUnitSize;

    setBaseUnitSize(nextBaseUnitSize);
    setBoardOffset({
      x: (rect.width - nextBoardW) / 2,
      y: (rect.height - nextBoardH) / 2,
    });
  }, [puzzle.cols, puzzle.rows]);

  const createInitialPieces = useCallback(() => {
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    const usableWidth = Math.max(
      260,
      windowWidth - SIDEBAR_WIDTH - maxPieceDimension - 120
    );

    const trayTop = windowHeight - SHELF_HEIGHT + 12;
    const trayMaxY = Math.max(trayTop, windowHeight - pieceHeight - 18);

    const newPieces: PieceState[] = [];

    for (let r = 0; r < puzzle.rows; r++) {
      for (let c = 0; c < puzzle.cols; c++) {
        const id = `piece-${r}-${c}`;
        const index = r * puzzle.cols + c;

        const trayX = 60 + Math.random() * usableWidth;
        const trayY = trayTop + Math.random() * Math.max(8, trayMaxY - trayTop);

        newPieces.push({
          id,
          row: r,
          col: c,
          currentPos: {
            x: trayX,
            y: trayY,
          },
          targetPos: {
            x: c * pieceWidth,
            y: r * pieceHeight,
          },
          isLocked: false,
          zIndex: 10 + index,
        });
      }
    }

    setPieces(newPieces);
  }, [puzzle, pieceWidth, pieceHeight, maxPieceDimension]);

  // Reset game hanya saat puzzle/level berubah.
  // Resize layar tidak boleh reset score, time, progress, atau posisi locked.
  useEffect(() => {
    setIsFinished(false);
    setIsTimeUp(false);
    setActivePieceId(null);
    setWrongFeedback(null);
    setIsStarting(true);
    setCountdown(null);
    setScore(0);
    setTimeLeft(GAME_TIME_SECONDS);
    setScoreAnim(false);
    createInitialPieces();
  }, [puzzle]);

  // Hitung ulang ukuran board saat mount, saat puzzle berubah, dan saat browser resize.
  // Ini hanya mengubah ukuran/layout, bukan regenerate pieces.
  useEffect(() => {
    const timeout = window.setTimeout(() => {
      calculateBoardLayout();
    }, 50);

    window.addEventListener('resize', calculateBoardLayout);

    return () => {
      window.clearTimeout(timeout);
      window.removeEventListener('resize', calculateBoardLayout);
    };
  }, [calculateBoardLayout]);

  // Saat ukuran board berubah karena resize:
  // - targetPos setiap piece diupdate.
  // - piece yang sudah locked tetap locked dan ikut pindah ke slot baru.
  // - piece yang belum locked tidak dibuat ulang, jadi progress/score/timer aman.
  useEffect(() => {
    setPieces((prev) => {
      if (prev.length === 0) return prev;

      const tableRect = tableRef.current?.getBoundingClientRect();

      return prev.map((p) => {
        const nextTargetPos = {
          x: p.col * pieceWidth,
          y: p.row * pieceHeight,
        };

        if (p.isLocked && tableRect) {
          return {
            ...p,
            targetPos: nextTargetPos,
            currentPos: {
              x: tableRect.left + boardOffset.x + nextTargetPos.x,
              y: tableRect.top + boardOffset.y + nextTargetPos.y,
            },
          };
        }

        return {
          ...p,
          targetPos: nextTargetPos,
        };
      });
    });
  }, [pieceWidth, pieceHeight, boardOffset.x, boardOffset.y]);

  useEffect(() => {
    if (!isGameActive) return;

    const interval = window.setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          window.clearInterval(interval);
          setIsTimeUp(true);
          return 0;
        }

        return prev - 1;
      });
    }, 1000);

    return () => window.clearInterval(interval);
  }, [isGameActive]);

  useEffect(() => {
    return () => {
      if (scoreAnimTimerRef.current) window.clearTimeout(scoreAnimTimerRef.current);
      if (wrongFeedbackTimerRef.current) window.clearTimeout(wrongFeedbackTimerRef.current);
    };
  }, []);

  const startCountdown = () => {
    setIsStarting(false);

    let count = 3;
    setCountdown(count);

    const timer = window.setInterval(() => {
      count -= 1;

      if (count > 0) {
        setCountdown(count);
      } else if (count === 0) {
        setCountdown('GO');
      } else {
        window.clearInterval(timer);
        setCountdown(null);
      }
    }, 1000);
  };

  const resetGame = () => {
    setIsFinished(false);
    setIsTimeUp(false);
    setActivePieceId(null);
    setWrongFeedback(null);
    setIsStarting(true);
    setCountdown(null);
    setScore(0);
    setTimeLeft(GAME_TIME_SECONDS);
    setScoreAnim(false);
    createInitialPieces();
  };

  const triggerScoreAnimation = () => {
    setScoreAnim(true);

    if (scoreAnimTimerRef.current) {
      window.clearTimeout(scoreAnimTimerRef.current);
    }

    scoreAnimTimerRef.current = window.setTimeout(() => {
      setScoreAnim(false);
    }, 500);
  };

  const showWrongFeedback = (x: number, y: number) => {
    setWrongFeedback({
      x,
      y,
      id: Date.now(),
    });

    if (wrongFeedbackTimerRef.current) {
      window.clearTimeout(wrongFeedbackTimerRef.current);
    }

    wrongFeedbackTimerRef.current = window.setTimeout(() => {
      setWrongFeedback(null);
    }, 800);
  };

  const handleMouseDown = (
    id: string,
    e: React.MouseEvent<HTMLDivElement> | React.TouchEvent<HTMLDivElement>
  ) => {
    if (!isGameActive) return;

    const piece = pieces.find((p) => p.id === id);
    if (!piece || piece.isLocked) return;

    e.preventDefault();

    setActivePieceId(id);

    setPieces((prev) =>
      prev.map((p) =>
        p.id === id
          ? {
              ...p,
              zIndex: 3000,
            }
          : p
      )
    );
  };

  const handleMouseMove = useCallback(
    (e: MouseEvent | TouchEvent) => {
      if (!activePieceId || !isGameActive) return;

      if ('touches' in e && e.cancelable) {
        e.preventDefault();
      }

      const clientX = 'touches' in e ? e.touches[0]?.clientX : e.clientX;
      const clientY = 'touches' in e ? e.touches[0]?.clientY : e.clientY;

      if (clientX === undefined || clientY === undefined) return;

      const x = clientX - pieceWidth / 2;
      const y = clientY - pieceHeight / 2;

      setPieces((prev) =>
        prev.map((p) =>
          p.id === activePieceId
            ? {
                ...p,
                currentPos: { x, y },
              }
            : p
        )
      );
    },
    [activePieceId, isGameActive, pieceWidth, pieceHeight]
  );

  const handleMouseUp = useCallback(
    (e: MouseEvent | TouchEvent) => {
      if (!activePieceId || !isGameActive) return;

      setPieces((prev) => {
        const active = prev.find((p) => p.id === activePieceId);

        if (!active || !tableRef.current) return prev;

        const tableRect = tableRef.current.getBoundingClientRect();

        const slotScreenX = tableRect.left + boardOffset.x + active.targetPos.x;
        const slotScreenY = tableRect.top + boardOffset.y + active.targetPos.y;

        const dist = Math.sqrt(
          Math.pow(active.currentPos.x - slotScreenX, 2) +
            Math.pow(active.currentPos.y - slotScreenY, 2)
        );

        if (dist < SNAP_THRESHOLD) {
          triggerScoreAnimation();

          const updated = prev.map((p) =>
            p.id === activePieceId
              ? {
                  ...p,
                  currentPos: {
                    x: slotScreenX,
                    y: slotScreenY,
                  },
                  isLocked: true,
                  zIndex: 1,
                }
              : p
          );

          const isAllLocked = updated.every((p) => p.isLocked);
          const timeBonus = isAllLocked ? timeLeft * 50 : 0;

          setScore((s) => s + 100 + timeBonus);

          if (isAllLocked) {
            window.setTimeout(() => {
              setIsFinished(true);
            }, 800);
          }

          return updated;
        }

        const clientX = 'changedTouches' in e ? e.changedTouches[0]?.clientX : e.clientX;
        const clientY = 'changedTouches' in e ? e.changedTouches[0]?.clientY : e.clientY;

        if (clientX !== undefined && clientY !== undefined) {
          const boardScreenX = tableRect.left + boardOffset.x;
          const boardScreenY = tableRect.top + boardOffset.y;

          const isNearBoard =
            clientX > boardScreenX - 40 &&
            clientX < boardScreenX + boardWidth + 40 &&
            clientY > boardScreenY - 40 &&
            clientY < boardScreenY + boardHeight + 40;

          if (isNearBoard) {
            showWrongFeedback(clientX, clientY);
          }
        }

        return prev;
      });

      setActivePieceId(null);
    },
    [
      activePieceId,
      isGameActive,
      boardOffset.x,
      boardOffset.y,
      boardWidth,
      boardHeight,
      timeLeft,
    ]
  );

  useEffect(() => {
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('mouseup', handleMouseUp);
    window.addEventListener('touchmove', handleMouseMove, { passive: false });
    window.addEventListener('touchend', handleMouseUp);

    return () => {
      window.removeEventListener('mousemove', handleMouseMove);
      window.removeEventListener('mouseup', handleMouseUp);
      window.removeEventListener('touchmove', handleMouseMove);
      window.removeEventListener('touchend', handleMouseUp);
    };
  }, [handleMouseMove, handleMouseUp]);

  const renderBoardSlots = () => {
    const slots = [];

    for (let r = 0; r < puzzle.rows; r++) {
      for (let c = 0; c < puzzle.cols; c++) {
        const path = generateJigsawPath(
          r,
          c,
          puzzle.rows,
          puzzle.cols,
          pieceWidth,
          pieceHeight
        );

        slots.push(
          <path
            key={`slot-${r}-${c}`}
            d={path}
            transform={`translate(${c * pieceWidth}, ${r * pieceHeight})`}
            fill="#065f46"
            fillOpacity="0.06"
            stroke="#065f46"
            strokeWidth="1.6"
            strokeOpacity="0.28"
            strokeDasharray="5 5"
          />
        );
      }
    }

    return slots;
  };

  const formatTime = (seconds: number) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;

    return `${m}:${s.toString().padStart(2, '0')}`;
  };

  return (
    <div
      className="relative w-full h-screen flex flex-col bg-[#FFF7ED] overflow-hidden fixed inset-0 touch-none"
      style={{ touchAction: 'none' }}
    >
      <div className="h-24 shrink-0 px-5 md:px-10 flex justify-between items-center bg-white border-b-4 border-[#ECFDF5] z-[100] shadow-md">
        <button
          onClick={onExit}
          className="bg-white border-b-4 border-[#FEF3C7] p-3 rounded-2xl text-[#DC2626] hover:bg-[#FEF3C7] active:translate-y-1 active:border-b-0 transition-all shadow-sm"
        >
          <svg className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={3}
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
        </button>

        <div className="text-center px-3">
          <h2 className="text-2xl md:text-4xl font-fredoka text-[#1E2939] tracking-tighter uppercase line-clamp-1">
            {puzzle.title}
          </h2>
          <p className="text-[9px] md:text-[10px] font-bold text-[#81C784]/60 uppercase tracking-widest mt-0.5">
            {puzzle.rows} x {puzzle.cols} Puzzle • SobatAnak
          </p>
        </div>

        <div className="bg-[#ECFDF5] border-4 border-white rounded-2xl px-4 py-2 shadow-sm text-center">
          <p className="text-[9px] font-black text-[#81C784]/60 uppercase tracking-[0.25em]">TIME</p>
          <p className="text-2xl md:text-3xl font-fredoka text-[#81C784]">{formatTime(timeLeft)}</p>
        </div>
      </div>

      <div className="flex-1 relative flex overflow-hidden touch-none">
        <div
          ref={tableRef}
          className="flex-1 relative bg-[#FFF7ED] shadow-inner p-8 flex items-center justify-center overflow-hidden touch-none"
        >
          <div
            className="absolute inset-0 opacity-[0.03] pointer-events-none"
            style={{
              backgroundImage:
                'url("https://www.transparenttextures.com/patterns/pinstriped-suit.png")',
            }}
          />

          <div
            className="absolute bg-[#ECFDF5] border-[14px] border-[#5d4037] shadow-[0_35px_80px_-20px_rgba(0,0,0,0.35)] rounded-[3rem] overflow-hidden"
            style={{
              width: boardWidth + BOARD_BORDER * 2,
              height: boardHeight + BOARD_BORDER * 2,
              left: boardOffset.x - BOARD_BORDER,
              top: boardOffset.y - BOARD_BORDER,
            }}
          >
            <div className="absolute inset-0 opacity-[0.025] grayscale pointer-events-none select-none">
              <img src={puzzle.image} className="w-full h-full object-cover" alt="" draggable={false} />
            </div>

            <svg width={boardWidth} height={boardHeight} className="absolute top-0 left-0 pointer-events-none">
              {renderBoardSlots()}
            </svg>
          </div>
        </div>

        <div className="w-56 md:w-64 bg-white/40 backdrop-blur-xl border-l-4 border-[#ECFDF5] flex flex-col items-center py-10 px-4 z-[90]">
          <div className="w-full bg-[#81C784] p-1 rounded-3xl mb-8 shadow-lg rotate-1">
            <div className="bg-white rounded-[1.4rem] p-6 text-center border-2 border-[#ECFDF5]">
              <p className="text-[10px] font-black text-[#81C784]/60 uppercase tracking-[0.3em] mb-1">SCORE</p>
              <p
                className={`text-5xl font-fredoka text-[#81C784] transition-all duration-300 ${
                  scoreAnim ? 'scale-125 text-[#FFD54F]' : 'scale-100'
                }`}
              >
                {score}
              </p>
            </div>
          </div>

          <div className="flex-1 space-y-6 w-full">
            <div className="bg-white/80 p-5 rounded-[2rem] border-2 border-[#ECFDF5] shadow-sm">
              <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Progress</p>

              <div className="h-4 w-full bg-[#ECFDF5] rounded-full overflow-hidden">
                <div
                  className="h-full bg-[#81C784] transition-all duration-500"
                  style={{
                    width: totalPieces > 0 ? `${(lockedPieces / totalPieces) * 100}%` : '0%',
                  }}
                />
              </div>

              <p className="text-right text-xs font-bold text-[#81C784] mt-2">
                {lockedPieces}/{totalPieces} Pieces
              </p>
            </div>

            <div className="bg-[#FEF3C7] p-5 rounded-[2rem] border-2 border-[#FEF3C7] shadow-sm animate-pulse-slow">
              <p className="text-[9px] font-bold text-[#FFA500] uppercase tracking-widest mb-1">Time Bonus</p>
              <p className="text-2xl font-fredoka text-[#FFA500]">+{timeLeft * 50}</p>
            </div>

            <div className="pt-10 flex flex-col items-center opacity-20 pointer-events-none">
              <div className="text-6xl mb-2">🌿</div>
              <p className="font-fredoka text-[#1E2939] text-sm">SobatAnak</p>
            </div>
          </div>
        </div>
      </div>

      <div className="h-32 shrink-0 bg-[#4e342e] border-t-8 border-[#3e2723] relative z-[50] shadow-[0_-15px_45px_rgba(0,0,0,0.5)] touch-none">
        <div className="absolute inset-4 bg-black/20 rounded-[2rem] shadow-inner flex flex-col items-center justify-center border border-white/5">
          <span className="text-white/10 font-fredoka text-xs uppercase tracking-[1.2em] mb-1 select-none">
            RAK KEPINGAN PUZZLE
          </span>
        </div>
      </div>

      <div className="fixed inset-0 pointer-events-none z-[150] touch-none">
        {pieces.map((p) => {
          const path = generateJigsawPath(
            p.row,
            p.col,
            puzzle.rows,
            puzzle.cols,
            pieceWidth,
            pieceHeight
          );

          const isActive = activePieceId === p.id;
          const canDrag = isGameActive && !p.isLocked;

          return (
            <div
              key={p.id}
              onMouseDown={(e) => handleMouseDown(p.id, e)}
              onTouchStart={(e) => handleMouseDown(p.id, e)}
              className={`absolute pointer-events-auto transition-shadow ${
                canDrag ? 'cursor-grab active:cursor-grabbing' : ''
              }`}
              style={{
                left: p.currentPos.x,
                top: p.currentPos.y,
                width: pieceWidth + PIECE_PADDING,
                height: pieceHeight + PIECE_PADDING,
                zIndex: isActive ? 3000 : p.isLocked ? 1 : p.zIndex,
                transform: `translate(${-PIECE_PADDING / 2}px, ${-PIECE_PADDING / 2}px) ${
                  isActive ? 'scale(1.12)' : 'scale(1)'
                }`,
                transition: isActive
                  ? 'none'
                  : 'transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s',
                opacity: !isGameActive && !p.isLocked ? 0.3 : 1,
                touchAction: 'none',
              }}
            >
              <svg
                width={pieceWidth + PIECE_PADDING}
                height={pieceHeight + PIECE_PADDING}
                viewBox={`${-PIECE_PADDING / 2} ${-PIECE_PADDING / 2} ${pieceWidth + PIECE_PADDING} ${pieceHeight + PIECE_PADDING}`}
                className={
                  isActive
                    ? 'drop-shadow-[0_25px_30px_rgba(0,0,0,0.6)]'
                    : p.isLocked
                    ? 'drop-shadow-sm'
                    : 'drop-shadow-[0_6px_10px_rgba(0,0,0,0.35)]'
                }
              >
                <defs>
                  <clipPath id={`clip-${p.id}`}>
                    <path d={path} />
                  </clipPath>
                </defs>

                <path
                  d={path}
                  fill="white"
                  stroke={p.isLocked ? 'white' : '#1E2939'}
                  strokeWidth={p.isLocked ? 1.3 : 2.4}
                />

                <g clipPath={`url(#clip-${p.id})`}>
                  <image
                    href={puzzle.image}
                    width={boardWidth}
                    height={boardHeight}
                    x={-p.targetPos.x}
                    y={-p.targetPos.y}
                    preserveAspectRatio="none"
                  />
                </g>
              </svg>
            </div>
          );
        })}
      </div>

      {isStarting && (
        <div className="fixed inset-0 z-[1000] bg-[#1E2939]/60 backdrop-blur-md flex items-center justify-center p-6 animate-fade-in">
          <div className="bg-white p-12 rounded-[4rem] border-[12px] border-[#81C784] shadow-2xl text-center transform animate-pop max-w-lg w-full">
            <div className="text-8xl mb-6 animate-bounce">🌱</div>
            <h2 className="text-5xl font-fredoka text-[#1E2939] mb-4 uppercase">Siap Main?</h2>
            <p className="text-xl text-[#81C784]/80 mb-10 font-bold">Susun cepat untuk skor maksimal!</p>

            <button
              onClick={startCountdown}
              className="group w-full bg-[#81C784] hover:bg-[#639C62] text-white font-fredoka text-4xl py-8 rounded-[2.5rem] shadow-[0_12px_0_0_#4A7B52] hover:shadow-[0_6px_0_0_#4A7B52] hover:translate-y-[6px] active:translate-y-[12px] active:shadow-none transition-all flex items-center justify-center gap-4"
            >
              MULAI!
              <span className="text-5xl group-hover:rotate-12 transition-transform">🚀</span>
            </button>
          </div>
        </div>
      )}

      {countdown !== null && (
        <div className="fixed inset-0 z-[1100] flex items-center justify-center pointer-events-none bg-black/10">
          <div
            key={countdown}
            className={`text-[15rem] md:text-[25rem] font-fredoka animate-countdown-pop drop-shadow-[0_20px_50px_rgba(0,0,0,0.3)]
              ${countdown === 3 ? 'text-[#FFF54F]' : ''}
              ${countdown === 2 ? 'text-[#FF7316]' : ''}
              ${countdown === 1 ? 'text-[#DC2626]' : ''}
              ${countdown === 'GO' ? 'text-[#81C784]' : ''}
            `}
          >
            {countdown}
          </div>
        </div>
      )}

      {wrongFeedback && (
        <div
          className="fixed z-[5000] pointer-events-none animate-wrong-pop"
          style={{
            left: wrongFeedback.x,
            top: wrongFeedback.y,
          }}
        >
          <div className="relative -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
            <div className="bg-[#DC2626] text-white font-fredoka text-7xl px-7 py-3 rounded-3xl border-[6px] border-white shadow-2xl rotate-12">
              X
            </div>
          </div>
        </div>
      )}

      {isTimeUp && !isFinished && (
        <div className="fixed inset-0 z-[2000] flex items-center justify-center bg-[#1E2939]/80 backdrop-blur-xl animate-fade-in">
          <div className="bg-white p-12 rounded-[4rem] text-center shadow-2xl border-[10px] border-[#DC2626] transform animate-pop max-w-sm mx-auto">
            <div className="text-8xl mb-6">⏳</div>
            <h2 className="text-5xl font-fredoka text-[#DC2626] mb-2 uppercase">Yah Habis!</h2>
            <p className="text-xl text-slate-500 mb-10 font-bold">Waktunya habis, coba lagi yuk!</p>
            <div className="flex flex-col gap-4">
              <button
                onClick={resetGame}
                className="bg-[#DC2626] hover:bg-[#B71C1E] text-white font-fredoka text-3xl py-6 w-full rounded-full shadow-[0_10px_0_0_#7A0F0F] active:translate-y-2 active:shadow-none transition-all"
              >
                ULANGI ♻️
              </button>
              <button
                onClick={onExit}
                className="bg-slate-200 hover:bg-slate-300 text-slate-700 font-fredoka text-2xl py-4 w-full rounded-full shadow-[0_6px_0_0_#94a3b8] active:translate-y-1 active:shadow-none transition-all"
              >
                KEMBALI ←
              </button>
            </div>
          </div>
        </div>
      )}

      {isFinished && <SuccessModal score={score} onNext={onWin} />}

      <style>{`
        @keyframes pulse-slow {
          0%, 100% {
            transform: scale(1);
            opacity: 1;
          }
          50% {
            transform: scale(1.02);
            opacity: 0.9;
          }
        }

        .animate-pulse-slow {
          animation: pulse-slow 3s ease-in-out infinite;
        }

        @keyframes countdown-pop {
          0% {
            transform: scale(0) rotate(-20deg);
            opacity: 0;
          }
          40% {
            transform: scale(1.2) rotate(10deg);
            opacity: 1;
          }
          100% {
            transform: scale(1) rotate(0deg);
            opacity: 0;
          }
        }

        .animate-countdown-pop {
          animation: countdown-pop 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .animate-fade-in {
          animation: fade-in 0.5s ease-out forwards;
        }

        .animate-pop {
          animation: pop 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes fade-in {
          from {
            opacity: 0;
          }
          to {
            opacity: 1;
          }
        }

        @keyframes pop {
          from {
            transform: scale(0.5);
            opacity: 0;
          }
          to {
            transform: scale(1);
            opacity: 1;
          }
        }

        @keyframes wrong-pop {
          0% {
            transform: scale(0);
            opacity: 0;
          }
          50% {
            transform: scale(1.2);
            opacity: 1;
          }
          100% {
            transform: scale(1);
            opacity: 0;
          }
        }

        .animate-wrong-pop {
          animation: wrong-pop 0.8s ease-out forwards;
        }
      `}</style>
    </div>
  );
};

export default GameBoard;
