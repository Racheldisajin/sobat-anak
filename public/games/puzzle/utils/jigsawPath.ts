
/**
 * Generates a clean, classic jigsaw piece path with interlocking knobs.
 * The path is designed for a piece of width 'w' and height 'h'.
 */
export function generateJigsawPath(
  row: number,
  col: number,
  rows: number,
  cols: number,
  w: number,
  h: number
) {
  const knobSize = Math.min(w, h) * 0.2;
  
  // Tab directions: 1 = out (tab), -1 = in (hole), 0 = flat edge
  const getDir = (r: number, c: number, side: 'T'|'R'|'B'|'L') => {
    if (side === 'T') return r === 0 ? 0 : ((r + c) % 2 === 0 ? 1 : -1);
    if (side === 'B') return r === rows - 1 ? 0 : ((r + c + 1) % 2 === 0 ? 1 : -1);
    if (side === 'L') return c === 0 ? 0 : ((r + c) % 2 === 0 ? -1 : 1);
    if (side === 'R') return c === cols - 1 ? 0 : ((r + c + 1) % 2 === 0 ? -1 : 1);
    return 0;
  };

  const T = getDir(row, col, 'T');
  const R = getDir(row, col, 'R');
  const B = getDir(row, col, 'B');
  const L = getDir(row, col, 'L');

  // Helper to draw a side with a knob
  const drawSide = (dir: number, length: number, isVertical: boolean) => {
    if (dir === 0) return isVertical ? `v ${length}` : `h ${length}`;
    
    const s = dir * knobSize;
    if (!isVertical) {
      // Horizontal
      return `h ${length/2 - knobSize} 
              c 0 ${s*1.5}, ${knobSize*2} ${s*1.5}, ${knobSize*2} 0 
              h ${length/2 - knobSize}`;
    } else {
      // Vertical
      return `v ${length/2 - knobSize} 
              c ${s*1.5} 0, ${s*1.5} ${knobSize*2}, 0 ${knobSize*2} 
              v ${length/2 - knobSize}`;
    }
  };

  // Build path starting at 0,0
  let path = `M 0 0 `;
  path += drawSide(T, w, false); // Top side
  path += drawSide(R, h, true);  // Right side
  
  // Bottom side (backwards)
  if (B === 0) path += `h ${-w} `;
  else {
    const s = B * knobSize;
    path += `h ${-(w/2 - knobSize)} c 0 ${s*1.5}, ${-knobSize*2} ${s*1.5}, ${-knobSize*2} 0 h ${-(w/2 - knobSize)} `;
  }

  // Left side (upwards)
  if (L === 0) path += `v ${-h} `;
  else {
    const s = L * knobSize;
    path += `v ${-(h/2 - knobSize)} c ${s*1.5} 0, ${s*1.5} ${-knobSize*2}, 0 ${-knobSize*2} v ${-(h/2 - knobSize)} `;
  }

  path += `Z`;
  return path;
}
