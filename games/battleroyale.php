<?php
// Battle Royale - Style Fortnite en 2D !
?>

<div class="min-h-screen bg-gradient-to-br from-blue-900 via-purple-900 to-pink-900 p-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-black bg-opacity-60 backdrop-blur rounded-2xl shadow-2xl p-4 mb-4 border border-purple-500">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="text-3xl">🎮</div>
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-1">⚔️ Battle Royale</h2>
                        <p class="text-gray-300 text-sm">ZQSD: Bouger | Souris: Viser | Clic: Tirer</p>
                    </div>
                </div>
                
                <div class="flex gap-4 items-center">
                    <!-- Stats joueur -->
                    <div class="bg-red-600 bg-opacity-80 px-4 py-2 rounded-lg">
                        <div class="text-white font-bold">❤️ <span id="health">100</span></div>
                    </div>
                    <div class="bg-blue-600 bg-opacity-80 px-4 py-2 rounded-lg">
                        <div class="text-white font-bold">🎯 <span id="kills">0</span> Kills</div>
                    </div>
                    <div class="bg-purple-600 bg-opacity-80 px-4 py-2 rounded-lg">
                        <div class="text-white font-bold">👥 <span id="alive">0</span> Vivants</div>
                    </div>
                    <a href="?" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                        ← Quitter
                    </a>
                </div>
            </div>
        </div>

        <!-- Zone de tempête -->
        <div class="bg-black bg-opacity-60 backdrop-blur rounded-xl p-3 mb-4 border border-blue-500">
            <div class="flex items-center justify-between">
                <span class="text-white font-bold">⚠️ La tempête arrive dans: <span id="stormTimer" class="text-red-500">30s</span></span>
                <div class="bg-gray-700 rounded-full h-2 w-64">
                    <div id="stormBar" class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <!-- Canvas de jeu -->
        <div class="relative">
            <canvas id="gameCanvas" width="1200" height="600" class="bg-green-800 rounded-2xl shadow-2xl mx-auto block border-4 border-purple-500"></canvas>
            
            <!-- Minimap -->
            <div class="absolute top-4 right-4 bg-black bg-opacity-70 rounded-lg p-2 border-2 border-yellow-500">
                <canvas id="minimap" width="150" height="150"></canvas>
            </div>

            <!-- Crosshair -->
            <div id="crosshair" class="absolute pointer-events-none" style="display: none;">
                <div class="w-6 h-6 border-2 border-red-500 rounded-full"></div>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="bg-black bg-opacity-60 backdrop-blur rounded-2xl p-4 mt-4 border border-yellow-500">
            <h3 class="text-xl font-bold text-yellow-400 mb-3">🏆 Top Joueurs</h3>
            <div id="leaderboard" class="grid grid-cols-1 md:grid-cols-2 gap-2"></div>
        </div>

    </div>
</div>

<script>
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const minimap = document.getElementById('minimap');
const minimapCtx = minimap.getContext('2d');

const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const allPlayers = <?php echo json_encode($players); ?>;

// Configuration du jeu
const WORLD_SIZE = 2000;
const PLAYER_SIZE = 20;
const BULLET_SIZE = 5;
const BULLET_SPEED = 12;
const MOVE_SPEED = 4;

// État du jeu
let camera = { x: 0, y: 0 };
let mousePos = { x: 0, y: 0 };
let keys = {};
let bullets = [];
let items = [];
let buildings = [];
let stormRadius = WORLD_SIZE;
let stormCenter = { x: WORLD_SIZE / 2, y: WORLD_SIZE / 2 };
let stormTimer = 30;
let gameTime = 0;

// Joueurs
const players = {};
const playerColors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#F97316'];

// Initialiser les joueurs
allPlayers.forEach((player, idx) => {
    const angle = (idx / allPlayers.length) * Math.PI * 2;
    const distance = WORLD_SIZE * 0.4;
    players[player] = {
        x: WORLD_SIZE / 2 + Math.cos(angle) * distance,
        y: WORLD_SIZE / 2 + Math.sin(angle) * distance,
        health: 100,
        kills: 0,
        alive: true,
        angle: 0,
        color: playerColors[idx % playerColors.length],
        weapon: 'pistol',
        lastShot: 0
    };
});

// Générer des bâtiments
for (let i = 0; i < 30; i++) {
    buildings.push({
        x: Math.random() * (WORLD_SIZE - 200) + 100,
        y: Math.random() * (WORLD_SIZE - 200) + 100,
        width: 50 + Math.random() * 100,
        height: 50 + Math.random() * 100,
        color: '#' + Math.floor(Math.random()*5 + 3).toString() + '33333'
    });
}

// Générer des items (armes, soin)
for (let i = 0; i < 50; i++) {
    const types = ['health', 'weapon', 'ammo'];
    items.push({
        x: Math.random() * WORLD_SIZE,
        y: Math.random() * WORLD_SIZE,
        type: types[Math.floor(Math.random() * types.length)],
        collected: false
    });
}

// Contrôles
document.addEventListener('keydown', (e) => keys[e.key.toLowerCase()] = true);
document.addEventListener('keyup', (e) => keys[e.key.toLowerCase()] = false);

canvas.addEventListener('mousemove', (e) => {
    const rect = canvas.getBoundingClientRect();
    mousePos.x = e.clientX - rect.left;
    mousePos.y = e.clientY - rect.top;
});

canvas.addEventListener('click', (e) => {
    shoot();
});

// Déplacer le joueur
function updatePlayer() {
    const player = players[myUsername];
    if (!player || !player.alive) return;

    let dx = 0, dy = 0;
    if (keys['z']) dy -= MOVE_SPEED;
    if (keys['s']) dy += MOVE_SPEED;
    if (keys['q']) dx -= MOVE_SPEED;
    if (keys['d']) dx += MOVE_SPEED;

    // Normaliser le mouvement diagonal
    if (dx !== 0 && dy !== 0) {
        dx *= 0.707;
        dy *= 0.707;
    }

    // Appliquer le mouvement avec collision
    const newX = player.x + dx;
    const newY = player.y + dy;
    
    if (!checkCollisionBuilding(newX, player.y)) player.x = newX;
    if (!checkCollisionBuilding(player.x, newY)) player.y = newY;

    // Limiter aux bords du monde
    player.x = Math.max(PLAYER_SIZE, Math.min(WORLD_SIZE - PLAYER_SIZE, player.x));
    player.y = Math.max(PLAYER_SIZE, Math.min(WORLD_SIZE - PLAYER_SIZE, player.y));

    // Angle vers la souris
    const worldMouseX = mousePos.x + camera.x - canvas.width / 2;
    const worldMouseY = mousePos.y + camera.y - canvas.height / 2;
    player.angle = Math.atan2(worldMouseY - player.y, worldMouseX - player.x);

    // Caméra suit le joueur
    camera.x = player.x;
    camera.y = player.y;

    // Ramasser des items
    items.forEach(item => {
        if (!item.collected) {
            const dist = Math.hypot(player.x - item.x, player.y - item.y);
            if (dist < 30) {
                item.collected = true;
                if (item.type === 'health') {
                    player.health = Math.min(100, player.health + 25);
                    document.getElementById('health').textContent = Math.round(player.health);
                }
            }
        }
    });

    // Dégâts de la tempête
    const distFromCenter = Math.hypot(player.x - stormCenter.x, player.y - stormCenter.y);
    if (distFromCenter > stormRadius) {
        player.health -= 0.5;
        document.getElementById('health').textContent = Math.round(player.health);
        if (player.health <= 0) {
            player.alive = false;
            alert('💀 Tu es mort dans la tempête !');
        }
    }
}

function checkCollisionBuilding(x, y) {
    return buildings.some(b => 
        x > b.x && x < b.x + b.width &&
        y > b.y && y < b.y + b.height
    );
}

function shoot() {
    const player = players[myUsername];
    if (!player || !player.alive) return;
    
    const now = Date.now();
    if (now - player.lastShot < 300) return; // Cadence de tir
    player.lastShot = now;

    bullets.push({
        x: player.x,
        y: player.y,
        dx: Math.cos(player.angle) * BULLET_SPEED,
        dy: Math.sin(player.angle) * BULLET_SPEED,
        owner: myUsername,
        distance: 0
    });
}

// IA simple pour les bots
function updateBots() {
    Object.entries(players).forEach(([name, player]) => {
        if (name === myUsername || !player.alive) return;

        // Se déplacer vers le centre de la zone
        const toCenter = {
            x: stormCenter.x - player.x,
            y: stormCenter.y - player.y
        };
        const dist = Math.hypot(toCenter.x, toCenter.y);
        
        if (dist > stormRadius * 0.8) {
            player.x += (toCenter.x / dist) * MOVE_SPEED * 0.8;
            player.y += (toCenter.y / dist) * MOVE_SPEED * 0.8;
        } else {
            // Mouvement aléatoire
            player.x += (Math.random() - 0.5) * 3;
            player.y += (Math.random() - 0.5) * 3;
        }

        // Limiter aux bords
        player.x = Math.max(0, Math.min(WORLD_SIZE, player.x));
        player.y = Math.max(0, Math.min(WORLD_SIZE, player.y));

        // Viser le joueur si proche
        const myPlayer = players[myUsername];
        if (myPlayer && myPlayer.alive) {
            const distToPlayer = Math.hypot(player.x - myPlayer.x, player.y - myPlayer.y);
            if (distToPlayer < 300) {
                player.angle = Math.atan2(myPlayer.y - player.y, myPlayer.x - player.x);
                
                // Tirer parfois
                if (Math.random() < 0.03) {
                    bullets.push({
                        x: player.x,
                        y: player.y,
                        dx: Math.cos(player.angle) * BULLET_SPEED,
                        dy: Math.sin(player.angle) * BULLET_SPEED,
                        owner: name,
                        distance: 0
                    });
                }
            }
        }

        // Dégâts tempête
        const distFromCenter = Math.hypot(player.x - stormCenter.x, player.y - stormCenter.y);
        if (distFromCenter > stormRadius) {
            player.health -= 0.5;
            if (player.health <= 0) {
                player.alive = false;
            }
        }
    });
}

// Mettre à jour les balles
function updateBullets() {
    bullets = bullets.filter(bullet => {
        bullet.x += bullet.dx;
        bullet.y += bullet.dy;
        bullet.distance += BULLET_SPEED;

        // Collision avec bâtiments
        if (checkCollisionBuilding(bullet.x, bullet.y)) {
            return false;
        }

        // Collision avec joueurs
        Object.entries(players).forEach(([name, player]) => {
            if (name !== bullet.owner && player.alive) {
                const dist = Math.hypot(bullet.x - player.x, bullet.y - player.y);
                if (dist < PLAYER_SIZE) {
                    player.health -= 20;
                    if (player.health <= 0) {
                        player.alive = false;
                        if (bullet.owner === myUsername) {
                            players[myUsername].kills++;
                            document.getElementById('kills').textContent = players[myUsername].kills;
                        }
                    }
                    if (name === myUsername) {
                        document.getElementById('health').textContent = Math.round(player.health);
                        if (player.health <= 0) {
                            alert('💀 Tu as été éliminé par ' + bullet.owner + ' !');
                        }
                    }
                    return false;
                }
            }
        });

        return bullet.distance < 500;
    });
}

// Système de tempête
function updateStorm() {
    gameTime++;
    
    if (gameTime % 60 === 0) {
        stormTimer--;
        document.getElementById('stormTimer').textContent = Math.max(0, stormTimer) + 's';
        
        if (stormTimer <= 0) {
            stormRadius = Math.max(100, stormRadius - 50);
            stormTimer = 20;
        }
    }
    
    const stormPercent = (stormRadius / WORLD_SIZE) * 100;
    document.getElementById('stormBar').style.width = stormPercent + '%';
}

// Dessiner
function draw() {
    // Fond (herbe)
    ctx.fillStyle = '#22c55e';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.save();
    ctx.translate(-camera.x + canvas.width / 2, -camera.y + canvas.height / 2);

    // Tempête
    ctx.fillStyle = 'rgba(139, 92, 246, 0.3)';
    ctx.fillRect(0, 0, WORLD_SIZE, WORLD_SIZE);
    
    ctx.fillStyle = '#22c55e';
    ctx.beginPath();
    ctx.arc(stormCenter.x, stormCenter.y, stormRadius, 0, Math.PI * 2);
    ctx.fill();

    // Bâtiments
    buildings.forEach(b => {
        ctx.fillStyle = b.color;
        ctx.fillRect(b.x, b.y, b.width, b.height);
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 2;
        ctx.strokeRect(b.x, b.y, b.width, b.height);
    });

    // Items
    items.forEach(item => {
        if (!item.collected) {
            const icons = { health: '❤️', weapon: '🔫', ammo: '📦' };
            ctx.font = '20px Arial';
            ctx.fillText(icons[item.type] || '❓', item.x - 10, item.y + 5);
        }
    });

    // Balles
    bullets.forEach(bullet => {
        ctx.fillStyle = '#FFF';
        ctx.beginPath();
        ctx.arc(bullet.x, bullet.y, BULLET_SIZE, 0, Math.PI * 2);
        ctx.fill();
    });

    // Joueurs
    Object.entries(players).forEach(([name, player]) => {
        if (!player.alive) return;

        // Corps
        ctx.fillStyle = player.color;
        ctx.beginPath();
        ctx.arc(player.x, player.y, PLAYER_SIZE, 0, Math.PI * 2);
        ctx.fill();

        // Direction (arme)
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(player.x, player.y);
        ctx.lineTo(
            player.x + Math.cos(player.angle) * 25,
            player.y + Math.sin(player.angle) * 25
        );
        ctx.stroke();

        // Nom
        ctx.fillStyle = name === myUsername ? '#FFF' : '#DDD';
        ctx.font = 'bold 12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(name, player.x, player.y - 30);

        // Barre de vie
        const barWidth = 40;
        ctx.fillStyle = '#FF0000';
        ctx.fillRect(player.x - barWidth/2, player.y + 25, barWidth, 5);
        ctx.fillStyle = '#00FF00';
        ctx.fillRect(player.x - barWidth/2, player.y + 25, barWidth * (player.health / 100), 5);
    });

    ctx.restore();

    // Minimap
    drawMinimap();
    
    // Nombre de vivants
    const aliveCount = Object.values(players).filter(p => p.alive).length;
    document.getElementById('alive').textContent = aliveCount;

    // Vérifier victoire
    if (aliveCount === 1 && players[myUsername].alive) {
        setTimeout(() => {
            alert('🎉 VICTORY ROYALE ! Tu as gagné !');
            window.location.href = '?';
        }, 1000);
    }
}

function drawMinimap() {
    minimapCtx.fillStyle = '#1F2937';
    minimapCtx.fillRect(0, 0, 150, 150);

    const scale = 150 / WORLD_SIZE;

    // Zone sûre
    minimapCtx.strokeStyle = '#3B82F6';
    minimapCtx.lineWidth = 2;
    minimapCtx.beginPath();
    minimapCtx.arc(
        stormCenter.x * scale,
        stormCenter.y * scale,
        stormRadius * scale,
        0,
        Math.PI * 2
    );
    minimapCtx.stroke();

    // Joueurs
    Object.entries(players).forEach(([name, player]) => {
        if (!player.alive) return;
        minimapCtx.fillStyle = name === myUsername ? '#FFF' : player.color;
        minimapCtx.beginPath();
        minimapCtx.arc(player.x * scale, player.y * scale, 3, 0, Math.PI * 2);
        minimapCtx.fill();
    });
}

function updateLeaderboard() {
    const sorted = Object.entries(players)
        .filter(([_, p]) => p.alive)
        .sort((a, b) => b[1].kills - a[1].kills)
        .slice(0, 6);

    document.getElementById('leaderboard').innerHTML = sorted.map(([name, player], idx) => `
        <div class="flex items-center justify-between p-2 bg-gray-800 rounded ${name === myUsername ? 'border-2 border-yellow-400' : ''}">
            <span class="text-white font-medium">${idx + 1}. ${name}</span>
            <span class="text-yellow-400 font-bold">${player.kills} 🎯</span>
        </div>
    `).join('');
}

function gameLoop() {
    updatePlayer();
    updateBots();
    updateBullets();
    updateStorm();
    draw();
    updateLeaderboard();
    requestAnimationFrame(gameLoop);
}

gameLoop();
</script>
