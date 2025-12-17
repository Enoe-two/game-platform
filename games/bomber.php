<?php
// Bomber Battle - Pose des bombes et évite les explosions !
?>

<div class="min-h-screen bg-gradient-to-br from-gray-700 to-gray-900 p-4">
    <div class="max-w-5xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">💣 Bomber Battle</h2>
                    <p class="text-gray-600">ZQSD pour bouger, ESPACE pour poser une bombe</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">Survivants: <span id="alive">0</span></div>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Terrain -->
        <canvas id="bomberCanvas" width="650" height="650" class="bg-gray-800 rounded-2xl shadow-2xl mx-auto block"></canvas>

    </div>
</div>

<script>
const canvas = document.getElementById('bomberCanvas');
const ctx = canvas.getContext('2d');
const tileSize = 50;
const gridWidth = 13;
const gridHeight = 13;

const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const players = <?php echo json_encode($players); ?>;

// Positions des joueurs
const playerPositions = {};
const playerColors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];
const bombs = [];
const explosions = [];
let alive = [];

// Initialiser les joueurs
players.forEach((player, idx) => {
    const startPositions = [
        {x: 1, y: 1},
        {x: gridWidth - 2, y: 1},
        {x: 1, y: gridHeight - 2},
        {x: gridWidth - 2, y: gridHeight - 2},
        {x: 6, y: 1},
        {x: 6, y: gridHeight - 2}
    ];
    playerPositions[player] = {
        ...startPositions[idx % startPositions.length],
        color: playerColors[idx % playerColors.length],
        alive: true
    };
    alive.push(player);
});

// Murs (obstacles)
const walls = [];
for (let y = 0; y < gridHeight; y++) {
    for (let x = 0; x < gridWidth; x++) {
        if (x === 0 || y === 0 || x === gridWidth - 1 || y === gridHeight - 1) {
            walls.push({x, y, type: 'solid'});
        } else if (x % 2 === 0 && y % 2 === 0) {
            walls.push({x, y, type: 'solid'});
        } else if (Math.random() < 0.3 && !isSpawnArea(x, y)) {
            walls.push({x, y, type: 'breakable'});
        }
    }
}

function isSpawnArea(x, y) {
    const spawns = [[1,1], [1,2], [2,1], [11,1], [10,1], [11,2], [1,11], [1,10], [2,11], [11,11], [11,10], [10,11]];
    return spawns.some(s => s[0] === x && s[1] === y);
}

// Contrôles
const keys = {};
document.addEventListener('keydown', (e) => {
    keys[e.key.toLowerCase()] = true;
    if (e.key === ' ') {
        placeBomb();
        e.preventDefault();
    }
});
document.addEventListener('keyup', (e) => {
    keys[e.key.toLowerCase()] = false;
});

function placeBomb() {
    const pos = playerPositions[myUsername];
    if (!pos.alive) return;
    
    if (!bombs.some(b => b.x === Math.floor(pos.x) && b.y === Math.floor(pos.y))) {
        bombs.push({
            x: Math.floor(pos.x),
            y: Math.floor(pos.y),
            timer: 3000,
            owner: myUsername
        });
    }
}

function movePlayer() {
    const pos = playerPositions[myUsername];
    if (!pos.alive) return;

    const speed = 0.1;
    let newX = pos.x;
    let newY = pos.y;

    if (keys['z']) newY -= speed;
    if (keys['s']) newY += speed;
    if (keys['q']) newX -= speed;
    if (keys['d']) newX += speed;

    if (!checkCollision(newX, pos.y)) pos.x = newX;
    if (!checkCollision(pos.x, newY)) pos.y = newY;
}

function checkCollision(x, y) {
    const gridX = Math.floor(x);
    const gridY = Math.floor(y);
    return walls.some(w => w.x === gridX && w.y === gridY);
}

function updateBombs(deltaTime) {
    bombs.forEach((bomb, idx) => {
        bomb.timer -= deltaTime;
        if (bomb.timer <= 0) {
            explode(bomb.x, bomb.y);
            bombs.splice(idx, 1);
        }
    });
}

function explode(x, y) {
    const explosion = [{x, y}];
    const directions = [[0,1], [0,-1], [1,0], [-1,0]];
    
    directions.forEach(([dx, dy]) => {
        for (let i = 1; i <= 2; i++) {
            const newX = x + dx * i;
            const newY = y + dy * i;
            const wall = walls.find(w => w.x === newX && w.y === newY);
            
            if (wall) {
                if (wall.type === 'breakable') {
                    walls.splice(walls.indexOf(wall), 1);
                    explosion.push({x: newX, y: newY});
                }
                break;
            }
            explosion.push({x: newX, y: newY});
        }
    });
    
    explosions.push({cells: explosion, timer: 500});
    
    // Vérifier si des joueurs sont touchés
    Object.entries(playerPositions).forEach(([player, pos]) => {
        if (pos.alive && explosion.some(e => Math.floor(pos.x) === e.x && Math.floor(pos.y) === e.y)) {
            pos.alive = false;
            alive = alive.filter(p => p !== player);
            document.getElementById('alive').textContent = alive.length;
            
            if (alive.length === 1) {
                setTimeout(() => {
                    alert(`🎉 ${alive[0]} gagne !`);
                    window.location.href = '?';
                }, 1000);
            } else if (alive.length === 0) {
                setTimeout(() => {
                    alert('Match nul !');
                    window.location.href = '?';
                }, 1000);
            }
        }
    });
}

function updateExplosions(deltaTime) {
    explosions.forEach((exp, idx) => {
        exp.timer -= deltaTime;
        if (exp.timer <= 0) {
            explosions.splice(idx, 1);
        }
    });
}

function draw() {
    ctx.fillStyle = '#1F2937';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Murs
    walls.forEach(wall => {
        ctx.fillStyle = wall.type === 'solid' ? '#4B5563' : '#9CA3AF';
        ctx.fillRect(wall.x * tileSize, wall.y * tileSize, tileSize, tileSize);
        ctx.strokeStyle = '#374151';
        ctx.strokeRect(wall.x * tileSize, wall.y * tileSize, tileSize, tileSize);
    });

    // Bombes
    bombs.forEach(bomb => {
        const pulse = Math.sin(Date.now() / 100) * 0.2 + 0.8;
        ctx.fillStyle = '#000000';
        ctx.beginPath();
        ctx.arc(
            bomb.x * tileSize + tileSize / 2,
            bomb.y * tileSize + tileSize / 2,
            tileSize / 3 * pulse,
            0,
            Math.PI * 2
        );
        ctx.fill();
    });

    // Explosions
    explosions.forEach(exp => {
        exp.cells.forEach(cell => {
            ctx.fillStyle = 'rgba(251, 146, 60, 0.8)';
            ctx.fillRect(cell.x * tileSize, cell.y * tileSize, tileSize, tileSize);
        });
    });

    // Joueurs
    Object.entries(playerPositions).forEach(([player, pos]) => {
        if (pos.alive) {
            ctx.fillStyle = pos.color;
            ctx.beginPath();
            ctx.arc(
                pos.x * tileSize + tileSize / 2,
                pos.y * tileSize + tileSize / 2,
                tileSize / 3,
                0,
                Math.PI * 2
            );
            ctx.fill();
        }
    });
}

// IA simple pour les autres joueurs
function moveAI() {
    Object.entries(playerPositions).forEach(([player, pos]) => {
        if (player !== myUsername && pos.alive) {
            const directions = ['up', 'down', 'left', 'right', 'stay'];
            const dir = directions[Math.floor(Math.random() * directions.length)];
            const speed = 0.08;
            
            let newX = pos.x;
            let newY = pos.y;
            
            switch(dir) {
                case 'up': newY -= speed; break;
                case 'down': newY += speed; break;
                case 'left': newX -= speed; break;
                case 'right': newX += speed; break;
            }
            
            if (!checkCollision(newX, pos.y)) pos.x = newX;
            if (!checkCollision(pos.x, newY)) pos.y = newY;
            
            if (Math.random() < 0.02 && !bombs.some(b => b.x === Math.floor(pos.x) && b.y === Math.floor(pos.y))) {
                bombs.push({
                    x: Math.floor(pos.x),
                    y: Math.floor(pos.y),
                    timer: 3000,
                    owner: player
                });
            }
        }
    });
}

document.getElementById('alive').textContent = alive.length;

let lastTime = Date.now();
function gameLoop() {
    const now = Date.now();
    const deltaTime = now - lastTime;
    lastTime = now;

    movePlayer();
    moveAI();
    updateBombs(deltaTime);
    updateExplosions(deltaTime);
    draw();
    requestAnimationFrame(gameLoop);
}

gameLoop();
</script>
