<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mini Game AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; overflow: hidden; background: #0a0a0f; font-family: 'Segoe UI', sans-serif; touch-action: none; color: white; }

        canvas { display: block; position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; }

        /* ---- OVERLAY ---- */
        .overlay {
            position: fixed; inset: 0;
            background: rgba(5,5,15,0.92);
            display: flex; flex-direction: column;
            justify-content: center; align-items: center; z-index: 100;
            backdrop-filter: blur(6px);
        }
        .hidden { display: none !important; }

        .menu-card {
            background: linear-gradient(135deg, #12122a 0%, #1e1e40 100%);
            padding: 40px 48px; border-radius: 24px;
            text-align: center;
            border: 1px solid rgba(100,120,255,0.25);
            box-shadow: 0 30px 80px rgba(0,0,100,0.5), inset 0 1px 0 rgba(255,255,255,0.06);
            max-width: 92%;
        }

        h1 {
            font-size: 32px; font-weight: 900; letter-spacing: 4px;
            text-transform: uppercase; margin-bottom: 28px;
            background: linear-gradient(90deg, #f97316, #ef4444, #ec4899);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .btn {
            display: flex; align-items: center; justify-content: center; gap: 12px;
            width: 280px; padding: 16px 24px; margin: 10px auto;
            font-size: 17px; font-weight: 700; border-radius: 14px;
            border: 2px solid transparent; cursor: pointer; color: white;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
        }
        .btn:hover, .btn.hovered {
            transform: scale(1.06); border-color: #facc15;
            box-shadow: 0 0 24px rgba(250,204,21,0.45);
        }
        .btn-green { background: linear-gradient(135deg, #16a34a, #15803d); }
        .btn-green:hover, .btn-green.hovered { box-shadow: 0 0 24px rgba(74,222,128,0.4); }

        /* ---- HUD ---- */
        #ui {
            position: fixed; top: 18px; left: 18px;
            pointer-events: none; z-index: 10;
        }
        #scoreBoard { font-size: 26px; font-weight: 800; letter-spacing: 1px; text-shadow: 0 2px 8px rgba(0,0,0,0.7); }
        #status { color: #facc15; font-size: 14px; margin-top: 6px; font-weight: 600; text-shadow: 0 1px 4px rgba(0,0,0,0.9); }

        /* ---- EXIT BTN ---- */
        #btnExit {
            position: fixed; top: 18px; right: 18px; z-index: 110;
            background: rgba(255,255,255,0.08); color: #f87171; text-decoration: none;
            padding: 10px 20px; border-radius: 10px; font-weight: 700;
            border: 1px solid rgba(248,113,113,0.35);
            transition: background 0.2s, transform 0.15s;
            cursor: pointer; font-size: 14px;
        }
        #btnExit:hover, #btnExit.hovered { background: #ef4444; color: white; transform: scale(1.05); }

        /* ---- CURSOR ---- */
        #ai-cursor {
            position: fixed; top: 0; left: 0; width: 28px; height: 28px;
            border-radius: 50%; pointer-events: none; z-index: 9999;
            background: rgba(52, 211, 153, 0.85); border: 3px solid rgba(255,255,255,0.9);
            box-shadow: 0 0 12px rgba(52,211,153,0.6);
            will-change: transform; transform-origin: center center;
            transition: background 0.1s, box-shadow 0.1s;
        }
        #ai-cursor.grabbing {
            background: rgba(239, 68, 68, 0.9);
            box-shadow: 0 0 20px rgba(239,68,68,0.7);
        }

        /* ---- GAME OVER POPUP ---- */
        #gameOverPanel {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(0,0,0,0.75); backdrop-filter: blur(8px);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        #gameOverPanel .card {
            background: linear-gradient(135deg,#1e1b4b,#312e81);
            border: 1px solid rgba(167,139,250,0.3);
            border-radius: 24px; padding: 44px 56px; text-align: center;
            box-shadow: 0 40px 80px rgba(0,0,80,0.5);
        }
        #gameOverPanel h2 { font-size: 40px; font-weight: 900; margin-bottom: 12px;
            background: linear-gradient(90deg,#f97316,#ef4444); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        #gameOverPanel .pts { font-size: 64px; font-weight: 900; color: #facc15; line-height:1; }
        #gameOverPanel p { color: #a5b4fc; margin-bottom: 24px; }

        video { display: none; }
    </style>
</head>
<body>

    <div id="ai-cursor"></div>

    <!-- EXIT -->
    <div id="btnExit" onclick="exitGame()">❌ Thoát Game</div>

    <!-- MENU -->
    <div id="menu-game" class="overlay">
        <div class="menu-card">
            <h1>CHỌN TRÒ CHƠI AI</h1>
            <button class="btn" onclick="selectGame('fruit')">🍎 CHÉM TRÁI CÂY</button>
            <button class="btn" onclick="selectGame('race')">🏎️ ĐUA XE AI</button>
            <div style="margin-top:24px; padding:16px; background:rgba(255,255,255,0.04); border-radius:12px; border:1px solid rgba(255,255,255,0.08)">
                <p style="font-size:13px; color:#fbbf24; font-weight:700; margin:0 0 8px">🖐️ HƯỚNG DẪN CỬ CHỈ:</p>
                <p style="font-size:12px; color:#94a3b8; margin:4px 0">1. Di chuyển bàn tay để điều khiển con trỏ.</p>
                <p style="font-size:12px; color:#94a3b8; margin:4px 0">2. <strong style="color:#e2e8f0">Chụm ngón cái + ngón trỏ</strong> để CLICK.</p>
            </div>
        </div>
    </div>

    <!-- GAME OVER -->
    <div id="gameOverPanel" class="hidden">
        <div class="card">
            <h2>💀 GAME OVER</h2>
            <div class="pts" id="finalScore">0</div>
            <p>ĐIỂM SỐ CỦA BẠN</p>
            <button class="btn btn-green" onclick="backToMenu()" style="margin:0 auto">🏠 Về Menu</button>
        </div>
    </div>

    <!-- HUD -->
    <div id="ui">
        <div id="scoreBoard">⭐ <span id="score">0</span></div>
        <div id="status">⏳ Đang tải AI Model...</div>
    </div>

    <video id="input_video" autoplay playsinline></video>
    <canvas id="gameCanvas"></canvas>

<script>
// =====================================================
// CORE SETUP
// =====================================================
const canvas  = document.getElementById('gameCanvas');
const ctx     = canvas.getContext('2d', { alpha: false }); // alpha:false = nhanh hơn
const scoreEl = document.getElementById('score');
const statusEl = document.getElementById('status');
const aiCursor = document.getElementById('ai-cursor');

function resize() {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
}
window.addEventListener('resize', resize);
resize();

// =====================================================
// HAND STATE  (tất cả qua 1 object, không scatter biến)
// =====================================================
const H = {
    x: innerWidth/2, y: innerHeight/2,   // vị trí hiển thị (sau lerp)
    tx: innerWidth/2, ty: innerHeight/2,  // target từ MediaPipe
    grabbing: false,
    prevGrab: false,
};

// =====================================================
// BLADE TRAIL  (pre-allocate fixed-size ring buffer)
// =====================================================
const TRAIL_MAX = 14;
const trailX = new Float32Array(TRAIL_MAX);
const trailY = new Float32Array(TRAIL_MAX);
let trailHead = 0, trailLen = 0;

function trailPush(x, y) {
    trailX[trailHead] = x; trailY[trailHead] = y;
    trailHead = (trailHead + 1) % TRAIL_MAX;
    if (trailLen < TRAIL_MAX) trailLen++;
}

function trailClear() { trailLen = 0; trailHead = 0; }

// =====================================================
// GAME STATE
// =====================================================
let currentGame = null;
let score = 0;
let gameRunning = false;
let objects = [];
let particles = []; // mảnh vỡ trái cây

// Hover debounce - chỉ check mỗi 4 frame
let hoverFrame = 0;
let hoveredEl = null;

// =====================================================
// MEDIAPIPE  (chạy ở ~20fps, không cần 60fps)
// =====================================================
function initAI() {
    const hands = new Hands({
        locateFile: f => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${f}`
    });
    hands.setOptions({
        maxNumHands: 1,
        modelComplexity: 0,          // Lite model = nhanh nhất
        minDetectionConfidence: 0.6,
        minTrackingConfidence: 0.5
    });

    hands.onResults(onHandResults);

    // Camera thấp nhất có thể → giảm decode cost
    const cam = new Camera(document.getElementById('input_video'), {
        onFrame: async () => { await hands.send({ image: document.getElementById('input_video') }); },
        width: 256, height: 192   // ↓ từ 320×240
    });
    cam.start().then(() => {
        statusEl.innerText = '✅ Sẵn sàng! Dùng tay di chuyển.';
        setTimeout(() => { statusEl.innerText = ''; }, 4000);
    }).catch(() => { statusEl.innerText = '⚠️ Không có camera — dùng chuột.'; });
}

let lastHandTime = 0;
function onHandResults(results) {
    const now = performance.now();
    if (now - lastHandTime < 30) return; // Throttle 30ms (~33fps max từ AI)
    lastHandTime = now;

    if (!results.multiHandLandmarks?.length) return;
    const lm = results.multiHandLandmarks[0];
    const idx = lm[8]; // ngón trỏ
    const thm = lm[4]; // ngón cái

    H.tx = (1 - idx.x) * canvas.width;
    H.ty = idx.y * canvas.height;

    const pinch = Math.hypot(thm.x - idx.x, thm.y - idx.y) < 0.07;
    H.prevGrab = H.grabbing;
    H.grabbing = pinch;

    if (H.grabbing && !H.prevGrab) onGrabStart();
    if (!H.grabbing && H.prevGrab) onGrabEnd();
}

function onGrabStart() {
    aiCursor.classList.add('grabbing');
    if (!gameRunning) doClick();
}
function onGrabEnd() {
    aiCursor.classList.remove('grabbing');
}

// =====================================================
// CLICK / HOVER LOGIC
// =====================================================
function doClick() {
    const el = document.elementFromPoint(H.x, H.y);
    const btn = el?.closest('.btn, #btnExit');
    if (btn) btn.click();
}

function updateHover() {
    if (gameRunning) return;
    hoverFrame++;
    if (hoverFrame % 4 !== 0) return; // chỉ chạy mỗi 4 frame

    document.querySelectorAll('.btn, #btnExit').forEach(b => b.classList.remove('hovered'));
    const el = document.elementFromPoint(H.x, H.y);
    hoveredEl = el?.closest('.btn, #btnExit') || null;
    if (hoveredEl) hoveredEl.classList.add('hovered');
}

// =====================================================
// GAME SELECT
// =====================================================
function selectGame(game) {
    currentGame = game;
    document.getElementById('menu-game').classList.add('hidden');
    document.getElementById('gameOverPanel').classList.add('hidden');
    score = 0;
    scoreEl.innerText = 0;
    objects = [];
    particles = [];
    trailClear();
    gameRunning = true;
}

function exitGame() {
    // Thay alert bằng panel
    gameRunning = false;
    document.getElementById('menu-game').classList.remove('hidden');
}

function backToMenu() {
    document.getElementById('gameOverPanel').classList.add('hidden');
    document.getElementById('menu-game').classList.remove('hidden');
    gameRunning = false;
}

function triggerGameOver() {
    gameRunning = false;
    document.getElementById('finalScore').innerText = score;
    document.getElementById('gameOverPanel').classList.remove('hidden');
}

// =====================================================
// OFFSCREEN CANVAS cho background đua xe (tĩnh)
// =====================================================
let roadBg = null;
function buildRoadBg() {
    roadBg = document.createElement('canvas');
    roadBg.width = canvas.width;
    roadBg.height = canvas.height;
    const rc = roadBg.getContext('2d');
    rc.fillStyle = '#1a1a2e';
    rc.fillRect(0, 0, roadBg.width, roadBg.height);
    // Đường kẻ giữa
    rc.setLineDash([30, 25]);
    rc.strokeStyle = 'rgba(255,255,255,0.2)';
    rc.lineWidth = 3;
    rc.beginPath();
    rc.moveTo(roadBg.width/2, 0);
    rc.lineTo(roadBg.width/2, roadBg.height);
    rc.stroke();
    rc.setLineDash([]);
}

// =====================================================
// RACE GAME
// =====================================================
const player = { x: 0, y: 0, w: 0, h: 0 };
let roadOffset = 0;

function initRace() {
    const s = canvas.width / 800;
    player.w = 54 * s;
    player.h = 92 * s;
    objects = [];
    buildRoadBg();
}

// Pre-drawn car body (tránh vẽ lại nhiều lần)
function drawCar(x, y, w, h, color) {
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.roundRect(x, y, w, h, 8);
    ctx.fill();
    // cửa sổ
    ctx.fillStyle = 'rgba(0,200,255,0.4)';
    ctx.fillRect(x + w*0.15, y + h*0.15, w*0.7, h*0.25);
    // bánh
    ctx.fillStyle = '#111';
    const bw = w*0.2, bh = h*0.15;
    ctx.fillRect(x - bw*0.4, y + h*0.1, bw, bh);
    ctx.fillRect(x + w - bw*0.6, y + h*0.1, bw, bh);
    ctx.fillRect(x - bw*0.4, y + h*0.7, bw, bh);
    ctx.fillRect(x + w - bw*0.6, y + h*0.7, bw, bh);
}

let lastObstacleTime = 0;
function updateRaceGame(dt) {
    ctx.drawImage(roadBg, 0, 0);

    // Vạch đường kẻ ngang di động
    roadOffset = (roadOffset + 6 * dt * 60) % 60;
    ctx.strokeStyle = 'rgba(255,255,255,0.08)';
    ctx.lineWidth = 1;
    for (let y = -60 + roadOffset; y < canvas.height; y += 60) {
        ctx.beginPath();
        ctx.moveTo(0, y); ctx.lineTo(canvas.width, y);
        ctx.stroke();
    }

    player.x = H.x - player.w / 2;
    player.y = canvas.height - player.h - 60;

    // Spawn xe địch (rate cố định theo thời gian, không theo frame)
    const now = performance.now();
    if (now - lastObstacleTime > 900 - Math.min(score * 8, 500)) {
        lastObstacleTime = now;
        const s = canvas.width / 800;
        objects.push({
            x: Math.random() * (canvas.width - 60),
            y: -110,
            w: 50*s, h: 80*s,
            speed: (5 + Math.random() * 4 + score * 0.05) * s,
            color: `hsl(${Math.random()*360},80%,55%)`
        });
    }

    for (let i = objects.length - 1; i >= 0; i--) {
        const o = objects[i];
        o.y += o.speed * dt * 60;
        drawCar(o.x, o.y, o.w, o.h, o.color);

        // Va chạm (thu nhỏ hitbox 20%)
        const margin = o.w * 0.2;
        if (H.x > o.x + margin && H.x < o.x + o.w - margin &&
            player.y < o.y + o.h - margin && player.y + player.h > o.y + margin) {
            triggerGameOver();
            return;
        }

        if (o.y > canvas.height + 120) {
            objects.splice(i, 1);
            score++;
            scoreEl.innerText = score;
        }
    }

    drawCar(player.x, player.y, player.w, player.h, '#3b82f6');
}

// =====================================================
// FRUIT GAME
// =====================================================
let lastFruitTime = 0;

function updateFruitGame(dt) {
    ctx.fillStyle = '#0f0f23';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Grid nền mờ (không dùng loop phức tạp)
    ctx.strokeStyle = 'rgba(255,255,255,0.03)';
    ctx.lineWidth = 1;

    // Spawn trái cây theo thời gian
    const now = performance.now();
    const spawnInterval = Math.max(400, 900 - score * 15);
    if (now - lastFruitTime > spawnInterval) {
        lastFruitTime = now;
        const s = canvas.height / 600;
        objects.push({
            x: canvas.width * (0.1 + Math.random() * 0.8),
            y: canvas.height + 50,
            vx: (Math.random() - 0.5) * 7,
            vy: -(Math.random() * 4 + 14) * s,
            r: (30 + Math.random() * 18) * (canvas.width / 800),
            hue: Math.random() * 360,
            gravity: 0.28 * s,
            sliced: false
        });
    }

    // Vẽ vệt kiếm (ring buffer, không shadow blur mỗi frame)
    if (trailLen > 1) {
        ctx.save();
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        // Vẽ 1 lần, gradient opacity
        for (let i = 1; i < trailLen; i++) {
            const ai = (trailHead - trailLen + i - 1 + TRAIL_MAX) % TRAIL_MAX;
            const bi = (trailHead - trailLen + i + TRAIL_MAX) % TRAIL_MAX;
            const alpha = i / trailLen;
            ctx.beginPath();
            ctx.strokeStyle = H.grabbing
                ? `rgba(239,68,68,${alpha * 0.85})`
                : `rgba(148,210,255,${alpha * 0.8})`;
            ctx.lineWidth = alpha * 10 + 2;
            ctx.moveTo(trailX[ai], trailY[ai]);
            ctx.lineTo(trailX[bi], trailY[bi]);
            ctx.stroke();
        }
        ctx.restore();
    }

    // Particles (mảnh vỡ)
    for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.x += p.vx * dt * 60;
        p.y += p.vy * dt * 60;
        p.vy += 0.4 * dt * 60;
        p.alpha -= 0.025 * dt * 60;
        if (p.alpha <= 0) { particles.splice(i, 1); continue; }
        ctx.globalAlpha = p.alpha;
        ctx.fillStyle = p.color;
        ctx.fillRect(p.x - p.r, p.y - p.r, p.r*2, p.r*2);
    }
    ctx.globalAlpha = 1;

    // Trái cây
    for (let i = objects.length - 1; i >= 0; i--) {
        const f = objects[i];
        f.x += f.vx * dt * 60;
        f.y += f.vy * dt * 60;
        f.vy += f.gravity * dt * 60;

        // Vẽ trái cây
        ctx.beginPath();
        ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
        const grad = ctx.createRadialGradient(f.x - f.r*0.3, f.y - f.r*0.3, f.r*0.1, f.x, f.y, f.r);
        grad.addColorStop(0, `hsl(${f.hue},100%,75%)`);
        grad.addColorStop(1, `hsl(${f.hue},100%,35%)`);
        ctx.fillStyle = grad;
        ctx.fill();
        ctx.strokeStyle = `hsla(${f.hue},100%,80%,0.5)`;
        ctx.lineWidth = 2;
        ctx.stroke();

        // Va chạm: kiểm tra tất cả điểm trong trail
        let hit = false;
        for (let t = 0; t < trailLen; t++) {
            const ti = (trailHead - trailLen + t + TRAIL_MAX) % TRAIL_MAX;
            if (Math.hypot(trailX[ti] - f.x, trailY[ti] - f.y) < f.r + 8) {
                hit = true; break;
            }
        }

        if (hit) {
            // Spawn mảnh vỡ
            for (let k = 0; k < 6; k++) {
                particles.push({
                    x: f.x, y: f.y,
                    vx: (Math.random()-0.5)*8, vy: (Math.random()-0.5)*8,
                    r: f.r * (0.15 + Math.random() * 0.2),
                    color: `hsl(${f.hue},100%,60%)`,
                    alpha: 1
                });
            }
            objects.splice(i, 1);
            score++;
            scoreEl.innerText = score;
        } else if (f.y > canvas.height + 160) {
            objects.splice(i, 1);
        }
    }
}

// =====================================================
// MAIN LOOP  (delta-time để không phụ thuộc FPS)
// =====================================================
let lastTime = 0;

function mainLoop(ts) {
    const dt = Math.min((ts - lastTime) / 1000, 0.05); // clamp 50ms tránh spiral of death
    lastTime = ts;

    // Lerp mượt nhưng không quá aggressive
    const lerpFactor = 1 - Math.pow(0.25, dt * 60); // frame-rate independent
    H.x += (H.tx - H.x) * lerpFactor;
    H.y += (H.ty - H.y) * lerpFactor;

    // Cập nhật cursor DOM (1 lần/frame, không layout thrash)
    const cs = H.grabbing ? 0.65 : 1;
    aiCursor.style.transform = `translate3d(${(H.x - 14)|0}px,${(H.y - 14)|0}px,0) scale(${cs})`;

    if (gameRunning) {
        trailPush(H.x, H.y);

        if (currentGame === 'fruit') updateFruitGame(dt);
        else updateRaceGame(dt);
    } else {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        updateHover();
    }

    requestAnimationFrame(mainLoop);
}

// =====================================================
// MOUSE FALLBACK
// =====================================================
window.addEventListener('mousedown', () => {
    H.grabbing = true; H.prevGrab = false;
    aiCursor.classList.add('grabbing');
    if (!gameRunning) doClick();
});
window.addEventListener('mouseup', () => {
    H.grabbing = false;
    aiCursor.classList.remove('grabbing');
});
canvas.addEventListener('mousemove', e => {
    H.tx = e.clientX; H.ty = e.clientY;
});

// =====================================================
// BOOT
// =====================================================
initAI();
requestAnimationFrame(mainLoop);
</script>
</body>
</html>