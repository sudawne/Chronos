<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>AI Game Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
            background: #0f172a;
            margin: 0;
            color: #f8fafc;
            touch-action: none;
        }
        canvas {
            display: block;
            position: absolute;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            z-index: 10;
            cursor: none;
            /* GPU-accelerated canvas */
            will-change: contents;
        }
        #ai-cursor {
            position: fixed;
            top: 0; left: 0;
            width: 32px; height: 32px;
            margin-left: -16px; margin-top: -16px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            background: rgba(79, 70, 229, 0.2);
            border: 2px solid rgba(79, 70, 229, 0.8);
            box-shadow: 0 0 10px rgba(79, 70, 229, 0.3);
            will-change: transform;
            transform-origin: center;
            /* Remove CSS transition – we handle smoothing in JS */
            opacity: 0;
        }
        #ai-cursor.grabbing {
            background: rgba(225, 29, 72, 0.3);
            border-color: rgba(225, 29, 72, 0.9);
            box-shadow: 0 0 15px rgba(225, 29, 72, 0.4);
        }
        .hud-glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        video { display: none; }
    </style>
</head>
<body class="h-screen w-full relative">

    <header class="absolute top-0 left-0 right-0 h-16 hud-glass flex items-center justify-between px-6 z-50">
        <div class="flex items-center gap-4">
            <a href="#" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div class="h-5 w-px bg-slate-600"></div>
            <div>
                <h1 class="font-bold text-sm tracking-wide text-white">CHRONOS AI GAMES</h1>
                <p class="text-[11px] text-indigo-400">Demo</p>
            </div>
        </div>
        <div class="flex gap-4 items-center">
            <div id="ui-score" class="hidden items-center gap-3 px-5 py-2 bg-indigo-600 rounded-xl font-bold shadow-lg shadow-indigo-600/30">
                <span class="material-symbols-outlined text-[20px]">stars</span>
                <span id="score" class="text-xl leading-none">0</span>
            </div>
            <button id="btn-exit" onclick="triggerGameOver()" class="hidden items-center gap-2 px-4 py-2 bg-rose-500/20 hover:bg-rose-600 border border-rose-500/50 rounded-xl text-white font-bold transition-all shadow-lg backdrop-blur-md cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">logout</span> Thoát
            </button>
        </div>
    </header>

    <div id="ai-cursor"></div>
    <canvas id="gameCanvas"></canvas>

    <div id="ai-status" class="absolute bottom-6 left-1/2 -translate-x-1/2 hud-glass px-6 py-3 rounded-full text-sm font-semibold flex items-center gap-3 z-50 text-indigo-300 hidden">
        <span class="material-symbols-outlined animate-spin">sync</span>
        <span id="status-text">Đang tải mô hình AI...</span>
    </div>

    <!-- MENU CHÍNH -->
    <div id="menu-game" class="absolute inset-0 bg-slate-900/60 backdrop-blur-md z-40 flex items-center justify-center">
        <div class="bg-white p-8 md:p-10 rounded-[2rem] shadow-2xl max-w-md w-full border border-slate-100 text-center">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-3xl">sports_esports</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 mb-2">Trung tâm Trò chơi AI</h2>
            <p class="text-sm text-slate-500 mb-8">Dùng <strong>Chuột</strong> để chọn Menu, dùng <strong>Tay</strong> để chơi game.</p>
            <div class="space-y-4">
                <button onclick="showControlMenu('fruit')" class="w-full flex items-center p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-rose-400 hover:bg-rose-50 hover:shadow-md transition-all group cursor-pointer">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🍎</div>
                    <div class="ml-4 text-left">
                        <h3 class="font-bold text-slate-800 group-hover:text-rose-600 transition-colors">Chém Trái Cây</h3>
                        <p class="text-xs text-slate-500">Quẹt tay để chém trái cây</p>
                    </div>
                </button>
                <button onclick="showControlMenu('race')" class="w-full flex items-center p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-indigo-400 hover:bg-indigo-50 hover:shadow-md transition-all group cursor-pointer">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🏎️</div>
                    <div class="ml-4 text-left">
                        <h3 class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Đua Xe Siêu Tốc</h3>
                        <p class="text-xs text-slate-500">Di chuyển tay để né vật cản</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- MENU ĐIỀU KHIỂN -->
    <div id="menu-control" class="absolute inset-0 bg-slate-900/60 backdrop-blur-md z-40 flex items-center justify-center hidden opacity-0 scale-95" style="transition: opacity .3s, transform .3s;">
        <div class="bg-white p-8 md:p-10 rounded-[2rem] shadow-2xl max-w-md w-full border border-slate-100 text-center relative">
            <button onclick="backToMenu()" class="absolute top-6 left-6 text-slate-400 hover:text-slate-600 z-10 w-10 h-10 flex items-center justify-center bg-slate-100 rounded-full cursor-pointer">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-5 mt-2">
                <span class="material-symbols-outlined text-3xl">settings_input_component</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 mb-8">Phương thức điều khiển</h2>
            <div class="space-y-4">
                <button onclick="startGame('ai')" class="w-full flex items-center p-4 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-600/30 transition-all group active:scale-95 cursor-pointer">
                    <span class="material-symbols-outlined text-2xl ml-2">front_hand</span>
                    <div class="ml-4 text-left">
                        <h3 class="font-bold text-lg">Camera AI (Hand)</h3>
                        <p class="text-xs text-indigo-200">Phát hiện tay qua Webcam</p>
                    </div>
                </button>
                <button onclick="startGame('touch')" class="w-full flex items-center p-4 bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-100 transition-all group active:scale-95 cursor-pointer">
                    <span class="material-symbols-outlined text-2xl ml-2 text-slate-400">mouse</span>
                    <div class="ml-4 text-left">
                        <h3 class="font-bold text-lg">Chuột / Cảm ứng</h3>
                        <p class="text-xs text-slate-500">Chơi trực tiếp trên màn hình</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- GAME OVER -->
    <div id="menu-gameover" class="absolute inset-0 bg-slate-900/80 backdrop-blur-md z-40 flex items-center justify-center hidden opacity-0 scale-95" style="transition: opacity .3s, transform .3s;">
        <div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-2xl max-w-sm w-full border border-slate-100 text-center">
            <h2 class="text-3xl font-black text-slate-800 mb-2">GAME OVER</h2>
            <p class="text-sm text-slate-500 font-medium mb-6">Thành tích của bạn</p>
            <div class="text-[80px] font-black text-indigo-600 leading-none mb-8" id="final-score">0</div>
            <button onclick="backToMenu()" class="w-full py-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-2xl transition-colors flex items-center justify-center gap-2 cursor-pointer">
                <span class="material-symbols-outlined">home</span> Về màn hình chính
            </button>
        </div>
    </div>

    <video id="input_video" autoplay playsinline></video>

<script>
// ==========================================
// UI & NAVIGATION
// ==========================================
let currentGame = null;
let controlMode = null;

function showControlMenu(game) {
    currentGame = game;
    document.getElementById('menu-game').classList.add('hidden');
    const ctrlMenu = document.getElementById('menu-control');
    ctrlMenu.classList.remove('hidden');
    requestAnimationFrame(() => ctrlMenu.classList.remove('opacity-0', 'scale-95'));
}

function backToMenu() {
    gameRunning = false;
    stopAI();

    const ctrlEl = document.getElementById('menu-control');
    const overEl = document.getElementById('menu-gameover');
    ctrlEl.classList.add('opacity-0', 'scale-95');
    overEl.classList.add('opacity-0', 'scale-95');
    document.getElementById('ui-score').classList.add('hidden');
    document.getElementById('btn-exit').classList.add('hidden');
    aiCursor.style.opacity = '0';

    setTimeout(() => {
        ctrlEl.classList.add('hidden');
        overEl.classList.add('hidden');
        document.getElementById('menu-game').classList.remove('hidden');
        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }, 300);
}

function startGame(mode) {
    controlMode = mode;
    const ctrlEl = document.getElementById('menu-control');
    ctrlEl.classList.add('opacity-0', 'scale-95');
    setTimeout(() => ctrlEl.classList.add('hidden'), 300);

    document.getElementById('ui-score').classList.remove('hidden');
    document.getElementById('ui-score').classList.add('flex');
    document.getElementById('btn-exit').classList.remove('hidden');
    document.getElementById('btn-exit').classList.add('flex');
    aiCursor.style.opacity = '1';

    if (mode === 'ai') initAI();
    initGameData();
    gameRunning = true;
}

function triggerGameOver() {
    gameRunning = false;
    stopAI();
    document.getElementById('btn-exit').classList.add('hidden');
    document.getElementById('final-score').innerText = score;
    const overEl = document.getElementById('menu-gameover');
    overEl.classList.remove('hidden');
    requestAnimationFrame(() => overEl.classList.remove('opacity-0', 'scale-95'));
    aiCursor.style.opacity = '0';
}

// ==========================================
// CANVAS SETUP
// ==========================================
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d', { alpha: false }); // alpha:false = faster compositing
const scoreEl = document.getElementById('score');
const aiCursor = document.getElementById('ai-cursor');

function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    // Re-cache road stripe after resize
    buildRoadStripe();
}
window.addEventListener('resize', resize);

// ==========================================
// HAND STATE (single source of truth)
// ==========================================
const H = {
    x: 0, y: 0,       // smoothed position
    tx: 0, ty: 0,      // target position
    grabbing: false
};

// ==========================================
// GAME STATE
// ==========================================
let score = 0, gameRunning = false;
let objects = [], particles = [];

// ---- Trail ring buffer ----
const TRAIL_MAX = 14;
const trailX = new Float32Array(TRAIL_MAX);
const trailY = new Float32Array(TRAIL_MAX);
let trailHead = 0, trailLen = 0;

function trailPush(x, y) {
    trailX[trailHead] = x;
    trailY[trailHead] = y;
    trailHead = (trailHead + 1) % TRAIL_MAX;
    if (trailLen < TRAIL_MAX) trailLen++;
}

// ==========================================
// GAME DATA / ASSETS
// ==========================================
const fruitList  = ['🍎','🍌','🍉','🍊','🍍','🍓','🥝','🍇'];
const obstacleList = ['🚘','🚖','🚔','🚍','🚚','🚧'];
const playerCar  = '🏎️';

// Pre-build emoji fonts once (avoid repeated ctx.font set)
let emojiFont = '60px Arial';
let playerFontSize = 60;
let obstacleFontSize = 60;
let fruitFontBase = 70; // 2 * r at base

let player = { x: 0, y: 0, w: 0, h: 0 };
let roadOffset = 0;

// ---- Offscreen road stripe (OffscreenCanvas or regular canvas) ----
let stripeCanvas = null, stripeCtx = null;
function buildRoadStripe() {
    // Build a tall stripe pattern once and reuse it
    // (road center line drawn as an offscreen pattern)
    const w = 8, h = 80;
    stripeCanvas = document.createElement('canvas');
    stripeCanvas.width = w; stripeCanvas.height = h;
    stripeCtx = stripeCanvas.getContext('2d');
    stripeCtx.fillStyle = 'rgba(255,255,255,0.05)';
    stripeCtx.fillRect(0, 0, w, 40); // dash
    // gap is transparent
    // We'll tile this using createPattern
}

let roadPattern = null;
function ensureRoadPattern() {
    if (!roadPattern && stripeCanvas) {
        roadPattern = ctx.createPattern(stripeCanvas, 'repeat');
    }
}

function initGameData() {
    score = 0;
    scoreEl.textContent = '0';
    objects = [];
    particles = [];
    trailHead = 0; trailLen = 0;
    lastObstacleTime = 0;
    lastFruitTime = 0;
    roadOffset = 0;

    // Scale sizes once
    const s = canvas.width / 800;
    playerFontSize = Math.round(60 * s);
    obstacleFontSize = playerFontSize;
    player.w = playerFontSize;
    player.h = playerFontSize;

    emojiFont = `${playerFontSize}px Arial`;

    // Center hand at start
    H.x = H.tx = canvas.width / 2;
    H.y = H.ty = canvas.height / 2;
}

// ==========================================
// OBJECT POOLS — avoid GC pressure
// ==========================================
const obstaclePool = [];
function getObstacle() {
    return obstaclePool.pop() || {};
}
function releaseObstacle(o) {
    if (obstaclePool.length < 40) obstaclePool.push(o);
}

const particlePool = [];
function getParticle() {
    return particlePool.pop() || {};
}
function releaseParticle(p) {
    if (particlePool.length < 100) particlePool.push(p);
}

const fruitPool = [];
function getFruit() {
    return fruitPool.pop() || {};
}
function releaseFruit(f) {
    if (fruitPool.length < 40) fruitPool.push(f);
}

// ==========================================
// GAME 1: ĐUA XE
// ==========================================
let lastObstacleTime = 0;

function updateRaceGame(dt) {
    // Background
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Road stripe — use pattern translated by roadOffset
    roadOffset = (roadOffset + 8 * dt * 60) % 80;
    ensureRoadPattern();
    if (roadPattern) {
        ctx.save();
        ctx.translate(canvas.width / 2 - 4, roadOffset - 80);
        ctx.fillStyle = roadPattern;
        ctx.fillRect(0, 0, 8, canvas.height + 160);
        ctx.restore();
    }

    // Player position
    player.x = H.x - player.w / 2;
    player.y = canvas.height - player.h - 80;

    // Batch draw: set font once
    ctx.font = emojiFont;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    ctx.fillText(playerCar, player.x + player.w / 2, player.y + player.h / 2);

    // Spawn obstacles
    const now = performance.now();
    const spawnInterval = Math.max(400, 800 - score * 10);
    if (now - lastObstacleTime > spawnInterval) {
        lastObstacleTime = now;
        const o = getObstacle();
        o.x = Math.random() * (canvas.width - player.w);
        o.y = -100;
        o.w = player.w;
        o.h = player.h;
        o.type = obstacleList[Math.floor(Math.random() * obstacleList.length)];
        o.speed = (6 + Math.random() * 4 + score * 0.1) * (canvas.width / 800);
        objects.push(o);
    }

    // Update & draw obstacles (font already set above)
    const margin = player.w * 0.2;
    const px1 = player.x + margin, px2 = player.x + player.w - margin;
    const py1 = player.y + margin, py2 = player.y + player.h - margin;

    for (let i = objects.length - 1; i >= 0; i--) {
        const o = objects[i];
        o.y += o.speed * dt * 60;

        ctx.fillText(o.type, o.x + o.w / 2, o.y + o.h / 2);

        // AABB collision
        if (px1 < o.x + o.w - margin && px2 > o.x + margin &&
            py1 < o.y + o.h - margin && py2 > o.y + margin) {
            triggerGameOver();
            return;
        }

        if (o.y > canvas.height + 50) {
            objects.splice(i, 1);
            releaseObstacle(o);
            score++;
            scoreEl.textContent = score;
        }
    }
}

// ==========================================
// GAME 2: CHÉM TRÁI CÂY
// ==========================================
let lastFruitTime = 0;

// Pre-built gradient for trail (avoid recreating each frame)
let trailGrad = null;
function buildTrailGradient(ax, ay, bx, by) {
    // Only build when we have meaningful length
    const len = Math.hypot(bx - ax, by - ay);
    if (len < 2) return null;
    try {
        const g = ctx.createLinearGradient(ax, ay, bx, by);
        g.addColorStop(0, 'rgba(99,102,241,0)');
        g.addColorStop(1, 'rgba(99,102,241,0.9)');
        return g;
    } catch(e) { return null; }
}

function updateFruitGame(dt) {
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Spawn fruit
    const now = performance.now();
    const spawnInterval = Math.max(400, 1000 - score * 20);
    if (now - lastFruitTime > spawnInterval) {
        lastFruitTime = now;
        const s = canvas.height / 600;
        const f = getFruit();
        f.x  = canvas.width * (0.1 + Math.random() * 0.8);
        f.y  = canvas.height + 50;
        f.vx = (Math.random() - 0.5) * 6;
        f.vy = -(Math.random() * 4 + 14) * s;
        f.r  = (35 + Math.random() * 10) * (canvas.width / 800);
        f.type = fruitList[Math.floor(Math.random() * fruitList.length)];
        f.gravity = 0.25 * s;
        f.hit = false;
        objects.push(f);
    }

    // Draw trail — single path for all segments
    if (trailLen > 1) {
        ctx.save();
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Head and tail of trail for gradient
        const tailI = (trailHead - trailLen + TRAIL_MAX) % TRAIL_MAX;
        const headI = (trailHead - 1 + TRAIL_MAX) % TRAIL_MAX;
        const grad = buildTrailGradient(trailX[tailI], trailY[tailI], trailX[headI], trailY[headI]);

        if (grad) {
            ctx.strokeStyle = grad;
            ctx.lineWidth = 10;
            ctx.beginPath();
            const si = (trailHead - trailLen + TRAIL_MAX) % TRAIL_MAX;
            ctx.moveTo(trailX[si], trailY[si]);
            for (let i = 1; i < trailLen; i++) {
                const idx = (trailHead - trailLen + i + TRAIL_MAX) % TRAIL_MAX;
                ctx.lineTo(trailX[idx], trailY[idx]);
            }
            ctx.stroke();
        }
        ctx.restore();
    }

    // Draw particles
    ctx.save();
    for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.x  += p.vx * dt * 60;
        p.y  += p.vy * dt * 60;
        p.vy += 0.4 * dt * 60;
        p.alpha -= 0.03 * dt * 60;
        if (p.alpha <= 0) {
            particles.splice(i, 1);
            releaseParticle(p);
            continue;
        }
        ctx.globalAlpha = p.alpha;
        ctx.fillStyle = p.color;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fill();
    }
    ctx.globalAlpha = 1;
    ctx.restore();

    // Draw fruits & collision (font set once per batch)
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    for (let i = objects.length - 1; i >= 0; i--) {
        const f = objects[i];
        f.x += f.vx * dt * 60;
        f.y += f.vy * dt * 60;
        f.vy += f.gravity * dt * 60;

        // Set font per-fruit (r varies) — but only change when needed
        const fSize = Math.round(f.r * 2);
        ctx.font = `${fSize}px Arial`;
        ctx.fillText(f.type, f.x, f.y);

        // Collision check against trail
        let hit = false;
        const hitR = f.r + 15;
        const hitR2 = hitR * hitR;
        for (let t = 0; t < trailLen; t++) {
            const ti = (trailHead - trailLen + t + TRAIL_MAX) % TRAIL_MAX;
            const dx = trailX[ti] - f.x;
            const dy = trailY[ti] - f.y;
            if (dx * dx + dy * dy < hitR2) { hit = true; break; }
        }

        if (hit) {
            // Burst particles
            for (let k = 0; k < 5; k++) {
                const p = getParticle();
                p.x = f.x; p.y = f.y;
                p.vx = (Math.random() - 0.5) * 10;
                p.vy = (Math.random() - 0.5) * 10;
                p.r = f.r * (0.15 + Math.random() * 0.1);
                p.color = '#fbbf24';
                p.alpha = 1;
                particles.push(p);
            }
            objects.splice(i, 1);
            releaseFruit(f);
            score++;
            scoreEl.textContent = score;
            continue;
        }

        if (f.y > canvas.height + 100) {
            objects.splice(i, 1);
            releaseFruit(f);
        }
    }
}

// ==========================================
// MAIN LOOP
// ==========================================
let lastTime = 0;

// DOM cursor update — batch with rAF, avoid thrashing
let cursorX = 0, cursorY = 0, cursorGrabbing = false;
let cursorDirty = false;

function mainLoop(ts) {
    const dt = Math.min((ts - lastTime) / 1000, 0.05);
    lastTime = ts;

    // Smooth hand position — tuned lerp (less aggressive = smoother)
    const alpha = 1 - Math.pow(0.1, dt * 60); // slower follow = smoother
    H.x += (H.tx - H.x) * alpha;
    H.y += (H.ty - H.y) * alpha;

    // Cursor DOM update — only write when changed
    const nx = Math.round(H.x);
    const ny = Math.round(H.y);
    if (nx !== cursorX || ny !== cursorY || H.grabbing !== cursorGrabbing) {
        cursorX = nx; cursorY = ny; cursorGrabbing = H.grabbing;
        aiCursor.style.transform = `translate3d(${nx}px,${ny}px,0)`;
        if (H.grabbing) aiCursor.classList.add('grabbing');
        else aiCursor.classList.remove('grabbing');
    }

    if (gameRunning) {
        trailPush(H.x, H.y);
        if (currentGame === 'fruit') updateFruitGame(dt);
        else updateRaceGame(dt);
    }
    // No clearRect when not running — avoid unnecessary GPU work

    requestAnimationFrame(mainLoop);
}

// ==========================================
// INPUT HANDLERS
// ==========================================
window.addEventListener('mousedown',  () => H.grabbing = true);
window.addEventListener('mouseup',    () => H.grabbing = false);
window.addEventListener('mousemove',  e  => {
    if (controlMode === 'touch') { H.tx = e.clientX; H.ty = e.clientY; }
});
window.addEventListener('touchstart', () => H.grabbing = true);
window.addEventListener('touchend',   () => H.grabbing = false);
window.addEventListener('touchmove',  e  => {
    if (controlMode === 'touch') {
        H.tx = e.touches[0].clientX;
        H.ty = e.touches[0].clientY;
    }
}, { passive: true }); // passive:true = no scroll-block overhead

// ==========================================
// AI — MEDIAPIPE HAND TRACKING
// ==========================================
let handsInstance = null;
let cameraInstance = null;

function initAI() {
    const statusDiv  = document.getElementById('ai-status');
    const statusText = document.getElementById('status-text');
    statusDiv.classList.remove('hidden');

    handsInstance = new Hands({
        locateFile: f => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${f}`
    });
    handsInstance.setOptions({
        maxNumHands: 1,
        modelComplexity: 0,           // lightest model
        minDetectionConfidence: 0.6,
        minTrackingConfidence: 0.5
    });

    let lastHandTime = 0;
    handsInstance.onResults(results => {
        const now = performance.now();
        if (now - lastHandTime < 50) return; // throttle to ~20fps (was 30ms → 33fps)
        lastHandTime = now;

        if (results.multiHandLandmarks?.length > 0) {
            statusDiv.classList.add('hidden');
            const lm = results.multiHandLandmarks[0];
            const idx = lm[8]; // index fingertip
            const thm = lm[4]; // thumb tip
            // Mirror X (camera is flipped)
            H.tx = (1 - idx.x) * canvas.width;
            H.ty = idx.y * canvas.height;
            H.grabbing = Math.hypot(thm.x - idx.x, thm.y - idx.y) < 0.08;
        }
    });

    const videoEl = document.getElementById('input_video');
    cameraInstance = new Camera(videoEl, {
        onFrame: async () => {
            if (handsInstance) await handsInstance.send({ image: videoEl });
        },
        width: 256,   // lower res = faster inference (was 320x240)
        height: 192
    });

    cameraInstance.start().catch(() => {
        statusText.innerText = 'Lỗi truy cập Camera!';
        const icon = statusDiv.querySelector('.material-symbols-outlined');
        icon.innerText = 'error';
        icon.classList.remove('animate-spin');
        statusDiv.classList.replace('text-indigo-300', 'text-rose-400');
    });
}

function stopAI() {
    if (cameraInstance) {
        try { cameraInstance.stop(); } catch(e) {}
        cameraInstance = null;
    }
    if (handsInstance) {
        try { handsInstance.close(); } catch(e) {}
        handsInstance = null;
    }
}

// ==========================================
// BOOT
// ==========================================
resize();
buildRoadStripe();

// Init hand center after resize
H.x = H.tx = canvas.width / 2;
H.y = H.ty = canvas.height / 2;

requestAnimationFrame(mainLoop);
</script>
</body>
</html>