import { Chess } from './vendor/chess.js';

const cfg = window.CHESS;
const GLYPH = { wk: '♔', wq: '♕', wr: '♖', wb: '♗', wn: '♘', wp: '♙', bk: '♚', bq: '♛', br: '♜', bb: '♝', bn: '♞', bp: '♟' };
const VALUE = { p: 100, n: 320, b: 330, r: 500, q: 900, k: 0 };
// Simple centre-control bonus for minor pieces & pawns (index 0..63 from a8)
const CENTER = [
    0, 0, 0, 0, 0, 0, 0, 0,
    0, 5, 5, 5, 5, 5, 5, 0,
    0, 5, 10, 15, 15, 10, 5, 0,
    0, 5, 15, 25, 25, 15, 5, 0,
    0, 5, 15, 25, 25, 15, 5, 0,
    0, 5, 10, 15, 15, 10, 5, 0,
    0, 5, 5, 5, 5, 5, 5, 0,
    0, 0, 0, 0, 0, 0, 0, 0,
];
const DEPTH = { 1: 1, 2: 2, 3: 3 };

const $ = (id) => document.getElementById(id);
const boardEl = $('board'), statusEl = $('status'), movesEl = $('moves'), resultEl = $('result');

let game = new Chess();
let playerColor = 'w';
let difficulty = 2;
let selected = null;
let lastMove = null;
let over = false;
let thinking = false;

function render() {
    boardEl.innerHTML = '';
    const board = game.board(); // [rank8..rank1][a..h]
    const legalTargets = selected ? game.moves({ square: selected, verbose: true }).map((m) => m.to) : [];
    const inCheckKing = game.inCheck() ? findKing(game.turn()) : null;

    for (let vr = 0; vr < 8; vr++) {
        for (let vf = 0; vf < 8; vf++) {
            const r = playerColor === 'w' ? vr : 7 - vr;
            const f = playerColor === 'w' ? vf : 7 - vf;
            const sq = 'abcdefgh'[f] + (8 - r);
            const piece = board[r][f];
            const cell = document.createElement('button');
            cell.className = 'sq ' + ((r + f) % 2 ? 'dark' : 'light');
            cell.dataset.sq = sq;
            cell.setAttribute('aria-label', sq + (piece ? ' ' + piece.color + piece.type : ''));
            if (piece) {
                cell.textContent = GLYPH[piece.color + piece.type];
                cell.classList.add(piece.color === 'w' ? 'pw' : 'pb');
            }
            if (sq === selected) cell.classList.add('sel');
            if (legalTargets.includes(sq)) cell.classList.add(piece ? 'capture' : 'target');
            if (lastMove && (sq === lastMove.from || sq === lastMove.to)) cell.classList.add('last');
            if (sq === inCheckKing) cell.classList.add('check');
            if (vf === 0) cell.dataset.rank = 8 - r;
            if (vr === 7) cell.dataset.file = 'abcdefgh'[f];
            boardEl.appendChild(cell);
        }
    }
    renderMoves();
    renderStatus();
}

function findKing(color) {
    for (const row of game.board()) for (const p of row) if (p && p.type === 'k' && p.color === color) return p.square;
    return null;
}

function renderMoves() {
    const hist = game.history();
    movesEl.innerHTML = '';
    for (let i = 0; i < hist.length; i += 2) {
        const li = document.createElement('li');
        li.textContent = hist[i] + (hist[i + 1] ? '  ' + hist[i + 1] : '');
        movesEl.appendChild(li);
    }
    movesEl.scrollTop = movesEl.scrollHeight;
}

function renderStatus() {
    const side = game.turn() === 'w' ? 'White' : 'Black';
    if (game.isCheckmate()) statusEl.textContent = `Checkmate — ${side === 'White' ? 'Black' : 'White'} wins`;
    else if (game.isDraw()) statusEl.textContent = 'Draw';
    else if (over) statusEl.textContent = 'Game over';
    else if (thinking) statusEl.textContent = 'Computer is thinking…';
    else statusEl.textContent = `${side} to move${game.inCheck() ? ' — check!' : ''}`;
}

boardEl.addEventListener('click', (e) => {
    const cell = e.target.closest('.sq');
    if (!cell || over || thinking || game.turn() !== playerColor) return;
    const sq = cell.dataset.sq;
    const piece = game.get(sq);

    if (selected) {
        const move = game.moves({ square: selected, verbose: true }).find((m) => m.to === sq);
        if (move) {
            game.move({ from: selected, to: sq, promotion: 'q' });
            lastMove = { from: selected, to: sq };
            selected = null;
            render();
            if (!checkEnd()) setTimeout(computerMove, 250);
            return;
        }
    }
    selected = piece && piece.color === playerColor ? sq : null;
    render();
});

// ---------- AI: minimax with alpha-beta ----------
function evaluate(g) {
    // score from white's perspective
    let score = 0;
    const b = g.board();
    for (let r = 0; r < 8; r++) for (let f = 0; f < 8; f++) {
        const p = b[r][f];
        if (!p) continue;
        let v = VALUE[p.type];
        if (p.type !== 'k' && p.type !== 'q') v += CENTER[r * 8 + f];
        if (p.type === 'p') v += (p.color === 'w' ? 6 - r : r - 1) * 5; // advance pawns
        score += p.color === 'w' ? v : -v;
    }
    return score;
}

function orderMoves(moves) {
    return moves.sort((a, b) => (b.captured ? VALUE[b.captured] : 0) - (a.captured ? VALUE[a.captured] : 0));
}

function minimax(g, depth, alpha, beta, maximizing) {
    if (g.isCheckmate()) return maximizing ? -100000 - depth : 100000 + depth;
    if (g.isDraw()) return 0;
    if (depth === 0) return evaluate(g);
    const moves = orderMoves(g.moves({ verbose: true }));
    if (maximizing) {
        let best = -Infinity;
        for (const m of moves) {
            g.move(m.san);
            best = Math.max(best, minimax(g, depth - 1, alpha, beta, false));
            g.undo();
            alpha = Math.max(alpha, best);
            if (beta <= alpha) break;
        }
        return best;
    }
    let best = Infinity;
    for (const m of moves) {
        g.move(m.san);
        best = Math.min(best, minimax(g, depth - 1, alpha, beta, true));
        g.undo();
        beta = Math.min(beta, best);
        if (beta <= alpha) break;
    }
    return best;
}

function bestMove() {
    const g = new Chess(game.fen());
    const maximizing = g.turn() === 'w';
    const moves = orderMoves(g.moves({ verbose: true }));
    // Easy: occasionally play a random move
    if (difficulty === 1 && Math.random() < 0.3) return moves[Math.floor(Math.random() * moves.length)];
    let best = [], bestScore = maximizing ? -Infinity : Infinity;
    for (const m of moves) {
        g.move(m.san);
        const s = minimax(g, DEPTH[difficulty] - 1, -Infinity, Infinity, !maximizing);
        g.undo();
        if (s === bestScore) best.push(m);
        else if (maximizing ? s > bestScore : s < bestScore) { bestScore = s; best = [m]; }
    }
    return best[Math.floor(Math.random() * best.length)];
}

function computerMove() {
    if (over) return;
    thinking = true;
    renderStatus();
    setTimeout(() => {
        const m = bestMove();
        game.move(m.san);
        lastMove = { from: m.from, to: m.to };
        thinking = false;
        render();
        checkEnd();
    }, 20);
}

// ---------- game end & scoring ----------
function checkEnd() {
    if (!game.isGameOver()) return false;
    let result = 'draw';
    if (game.isCheckmate()) result = game.turn() === playerColor ? 'loss' : 'win';
    finish(result);
    return true;
}

async function finish(result) {
    over = true;
    render();
    const label = { win: 'You win! 🎉', loss: 'You lost.', draw: 'Draw.' }[result];
    resultEl.hidden = false;
    resultEl.className = 'result res-' + result;
    if (!cfg.loggedIn) {
        resultEl.innerHTML = `${label}<br><small>Your score was not saved — <a href="${cfg.loginUrl}">log in</a> to keep track.</small>`;
        return;
    }
    resultEl.textContent = label + ' Saving…';
    try {
        const res = await fetch(cfg.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({
                result,
                player_color: playerColor,
                difficulty,
                moves: Math.ceil(game.history().length / 2),
                pgn: game.pgn(),
            }),
        });
        if (!res.ok) throw new Error(res.status);
        const data = await res.json();
        resultEl.innerHTML = `${label}<br><small>+${data.points} points · total ${data.total}</small>`;
    } catch (e) {
        resultEl.innerHTML = `${label}<br><small>Could not save score (${e.message}).</small>`;
    }
}

function newGame() {
    game = new Chess();
    playerColor = $('color').value;
    difficulty = parseInt($('difficulty').value, 10);
    selected = null; lastMove = null; over = false; thinking = false;
    resultEl.hidden = true;
    render();
    if (playerColor === 'b') computerMove();
}

$('newGame').addEventListener('click', newGame);
$('resign').addEventListener('click', () => {
    if (over || game.history().length === 0) return;
    if (confirm('Resign this game?')) finish('loss');
});
$('undo').addEventListener('click', () => {
    if (over || thinking) return;
    // undo the computer's reply and the player's move
    game.undo();
    if (game.turn() !== playerColor) game.undo();
    selected = null; lastMove = null;
    render();
});

newGame();
