<?php
// Snake Battle - Mange les pommes et évite les autres serpents !
?>

<div class="min-h-screen bg-gradient-to-br from-lime-400 to-green-600 p-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🐍 Snake Battle</h2>
                    <p class="text-gray-600">Mange les pommes ! Score: <span id="score">0</span></p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600" id="timer">60s</div>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Terrain de jeu -->
        <canvas id="gameCanvas" width="600" height="400" class="bg-gray-900 rounded-2xl shadow-2xl mx-auto block"></canvas>

        <!-- Classement -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mt-4">
            <h3 class="text-xl font-bold text-gray-800 mb-3">🏆 Classement</h3>
            <div id="leaderboard"></div>
        </div>

    </div>
</div>

<script>
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const gridSize = 20;
const tileCount = canvas.width / gridSize;

const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const players = <?php echo json_encode($players); ?>;

let timeLeft = 60;
let gameOver = false;

// Serpents des joueurs
const snakes = {};
const scores = {};
const colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];

// Initialiser les serpents
players.forEach((player, idx) => {
    const startX = Math.floor(Math.random() * (tileCount - 4)) + 2;
    const startY = Math.floor(Math.random() * (tileCount - 4)) + 2;
    snakes[player] = {
        body: [{x: startX, y: startY}],
        direction: 'right',
        color: colors[idx % colors.length]
    };
    scores[player] = 0;
});

// Pommes
const apples = [];
for (let i = 0; i < 10; i++) {
    spawnApple();
}

function spawnApple() {
    apples.push({
        x: Math.floor(Math.random() * tileCount),
        y: Math.floor(Math.random() * tileCount)
    });
}

// Contrôles clavier
document.addEventListener('keydown', (e) => {
    const snake = snakes[myUsername];
    if (!snake) return;

    switch(e.key) {
        case 'ArrowUp':
            if (snake.direction !== 'down') snake.direction = 'up';
            break;
        case 'ArrowDown':
            if (snake.direction !== 'up') snake.direction = 'down';
            break;
        case 'ArrowLeft':
            if (snake.direction !== 'right') snake.direction = 'left';
            break;
        case 'ArrowRight':
            if (snake.direction !== 'left') snake.direction = 'right';
            break;
    }
});

// Boucle de jeu
function gameLoop() {
    if (gameOver) return;

    // Déplacer les serpents
    Object.entries(snakes).forEach(([player, snake]) => {
        const head = {...snake.body[0]};
        
        // Mouvement IA pour les autres joueurs
        if (player !== myUsername) {
            const nearestApple = apples.reduce((nearest, apple) => {
                const dist = Math.abs(apple.x - head.x) + Math.abs(apple.y - head.y);
                const nearestDist = Math.abs(nearest.x - head.x) + Math.abs(nearest.y - head.y);
                return dist < nearestDist ? apple : nearest;
            }, apples[0] || {x: head.x, y: head.y});

            if (nearestApple.x > head.x && snake.direction !== 'left') snake.direction = 'right';
            else if (nearestApple.x < head.x && snake.direction !== 'right') snake.direction = 'left';
            else if (nearestApple.y > head.y && snake.direction !== 'up') snake.direction = 'down';
            else if (nearestApple.y < head.y && snake.direction !== 'down') snake.direction = 'up';
        }

        // Nouvelle position de la tête
        switch(snake.direction) {
            case 'up': head.y--; break;
            case 'down': head.y++; break;
            case 'left': head.x--; break;
            case 'right': head.x++; break;
        }

        // Téléportation aux bords
        if (head.x < 0) head.x = tileCount - 1;
        if (head.x >= tileCount) head.x = 0;
        if (head.y < 0) head.y = tileCount - 1;
        if (head.y >= tileCount) head.y = 0;

        snake.body.unshift(head);

        // Vérifier collision avec pomme
        const appleIndex = apples.findIndex(a => a.x === head.x && a.y === head.y);
        if (appleIndex !== -1) {
            apples.splice(appleIndex, 1);
            spawnApple();
            scores[player]++;
            if (player === myUsername) {
                document.getElementById('score').textContent = scores[player];
            }
        } else {
            snake.body.pop();
        }
    });

    draw();
    updateLeaderboard();
}

function draw() {
    // Fond
    ctx.fillStyle = '#1F2937';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Grille
    ctx.strokeStyle = '#374151';
    ctx.lineWidth = 0.5;
    for (let i = 0; i <= tileCount; i++) {
        ctx.beginPath();
        ctx.moveTo(i * gridSize, 0);
        ctx.lineTo(i * gridSize, canvas.height);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(0, i * gridSize);
        ctx.lineTo(canvas.width, i * gridSize);
        ctx.stroke();
    }

    // Pommes
    apples.forEach(apple => {
        ctx.fillStyle = '#EF4444';
        ctx.beginPath();
        ctx.arc(apple.x * gridSize + gridSize/2, apple.y * gridSize + gridSize/2, gridSize/2 - 2, 0, Math.PI * 2);
        ctx.fill();
    });

    // Serpents
    Object.entries(snakes).forEach(([player, snake]) => {
        snake.body.forEach((segment, idx) => {
            ctx.fillStyle = idx === 0 ? snake.color : snake.color + 'CC';
            ctx.fillRect(segment.x * gridSize + 1, segment.y * gridSize + 1, gridSize - 2, gridSize - 2);
        });
    });
}

function updateLeaderboard() {
    const sorted = Object.entries(scores).sort((a, b) => b[1] - a[1]);
    const medals = ['🥇', '🥈', '🥉', '🏅'];
    document.getElementById('leaderboard').innerHTML = sorted.map(([player, score], idx) => `
        <div class="flex items-center justify-between mb-2 p-2 bg-gray-50 rounded">
            <div class="flex items-center gap-2">
                <span class="text-xl">${medals[idx] || '🏅'}</span>
                <span class="font-medium">${player}</span>
            </div>
            <span class="font-bold text-green-600">${score} pts</span>
        </div>
    `).join('');
}

// Timer
setInterval(() => {
    timeLeft--;
    document.getElementById('timer').textContent = timeLeft + 's';
    if (timeLeft <= 0) {
        gameOver = true;
        alert('Temps écoulé ! Score final : ' + scores[myUsername]);
        window.location.href = '?';
    }
}, 1000);

setInterval(gameLoop, 150);
</script>
