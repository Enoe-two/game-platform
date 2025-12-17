<?php
// Pong Battle - Le classique revisité !
?>

<div class="min-h-screen bg-gradient-to-br from-indigo-900 to-purple-900 p-4">
    <div class="max-w-5xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🏓 Pong Battle</h2>
                    <p class="text-gray-600">Flèches ↑↓ pour bouger la raquette</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-indigo-600">
                        <span id="score1">0</span> - <span id="score2">0</span>
                    </div>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Canvas -->
        <canvas id="pongCanvas" width="800" height="500" class="bg-gray-900 rounded-2xl shadow-2xl mx-auto block"></canvas>

    </div>
</div>

<script>
const canvas = document.getElementById('pongCanvas');
const ctx = canvas.getContext('2d');
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const players = <?php echo json_encode(array_slice($players, 0, 2)); ?>; // Max 2 joueurs

// Configuration
const paddleWidth = 15;
const paddleHeight = 100;
const ballSize = 15;

// État du jeu
let ball = {
    x: canvas.width / 2,
    y: canvas.height / 2,
    dx: 4,
    dy: 3,
    speed: 4
};

let paddle1 = {
    x: 20,
    y: canvas.height / 2 - paddleHeight / 2,
    dy: 0,
    player: players[0]
};

let paddle2 = {
    x: canvas.width - 20 - paddleWidth,
    y: canvas.height / 2 - paddleHeight / 2,
    dy: 0,
    player: players[1] || 'CPU'
};

let score1 = 0;
let score2 = 0;

// Contrôles
const keys = {};
document.addEventListener('keydown', (e) => {
    keys[e.key] = true;
    e.preventDefault();
});
document.addEventListener('keyup', (e) => {
    keys[e.key] = false;
});

function update() {
    // Déplacement de la raquette du joueur
    if (paddle1.player === myUsername) {
        if (keys['ArrowUp'] && paddle1.y > 0) {
            paddle1.y -= 7;
        }
        if (keys['ArrowDown'] && paddle1.y < canvas.height - paddleHeight) {
            paddle1.y += 7;
        }
    } else if (paddle2.player === myUsername) {
        if (keys['ArrowUp'] && paddle2.y > 0) {
            paddle2.y -= 7;
        }
        if (keys['ArrowDown'] && paddle2.y < canvas.height - paddleHeight) {
            paddle2.y += 7;
        }
    }

    // IA simple pour l'autre joueur ou CPU
    if (paddle1.player !== myUsername) {
        if (ball.y < paddle1.y + paddleHeight / 2) {
            paddle1.y -= 5;
        } else if (ball.y > paddle1.y + paddleHeight / 2) {
            paddle1.y += 5;
        }
    }
    
    if (paddle2.player !== myUsername && paddle2.player === 'CPU') {
        if (ball.y < paddle2.y + paddleHeight / 2) {
            paddle2.y -= 5;
        } else if (ball.y > paddle2.y + paddleHeight / 2) {
            paddle2.y += 5;
        }
    }

    // Déplacement de la balle
    ball.x += ball.dx;
    ball.y += ball.dy;

    // Rebond haut/bas
    if (ball.y <= 0 || ball.y >= canvas.height - ballSize) {
        ball.dy *= -1;
    }

    // Collision avec raquette gauche
    if (ball.x <= paddle1.x + paddleWidth && 
        ball.y + ballSize >= paddle1.y && 
        ball.y <= paddle1.y + paddleHeight) {
        ball.dx *= -1.05;
        ball.x = paddle1.x + paddleWidth;
        // Effet selon où la balle touche la raquette
        const hitPos = (ball.y - paddle1.y) / paddleHeight;
        ball.dy = (hitPos - 0.5) * 10;
    }

    // Collision avec raquette droite
    if (ball.x + ballSize >= paddle2.x && 
        ball.y + ballSize >= paddle2.y && 
        ball.y <= paddle2.y + paddleHeight) {
        ball.dx *= -1.05;
        ball.x = paddle2.x - ballSize;
        const hitPos = (ball.y - paddle2.y) / paddleHeight;
        ball.dy = (hitPos - 0.5) * 10;
    }

    // Point marqué
    if (ball.x < 0) {
        score2++;
        document.getElementById('score2').textContent = score2;
        resetBall();
        checkWin();
    }
    if (ball.x > canvas.width) {
        score1++;
        document.getElementById('score1').textContent = score1;
        resetBall();
        checkWin();
    }
}

function resetBall() {
    ball.x = canvas.width / 2;
    ball.y = canvas.height / 2;
    ball.dx = (Math.random() > 0.5 ? 1 : -1) * 4;
    ball.dy = (Math.random() - 0.5) * 6;
}

function checkWin() {
    if (score1 >= 5) {
        alert(`🎉 ${paddle1.player} gagne !`);
        window.location.href = '?';
    } else if (score2 >= 5) {
        alert(`🎉 ${paddle2.player} gagne !`);
        window.location.href = '?';
    }
}

function draw() {
    // Fond
    ctx.fillStyle = '#111827';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Ligne centrale
    ctx.strokeStyle = '#374151';
    ctx.setLineDash([10, 10]);
    ctx.beginPath();
    ctx.moveTo(canvas.width / 2, 0);
    ctx.lineTo(canvas.width / 2, canvas.height);
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.setLineDash([]);

    // Raquettes
    ctx.fillStyle = '#3B82F6';
    ctx.fillRect(paddle1.x, paddle1.y, paddleWidth, paddleHeight);
    
    ctx.fillStyle = '#EF4444';
    ctx.fillRect(paddle2.x, paddle2.y, paddleWidth, paddleHeight);

    // Balle
    ctx.fillStyle = '#FFFFFF';
    ctx.beginPath();
    ctx.arc(ball.x + ballSize/2, ball.y + ballSize/2, ballSize/2, 0, Math.PI * 2);
    ctx.fill();

    // Noms des joueurs
    ctx.fillStyle = '#3B82F6';
    ctx.font = 'bold 16px Arial';
    ctx.fillText(paddle1.player, 30, 30);
    
    ctx.fillStyle = '#EF4444';
    ctx.textAlign = 'right';
    ctx.fillText(paddle2.player, canvas.width - 30, 30);
    ctx.textAlign = 'left';
}

function gameLoop() {
    update();
    draw();
    requestAnimationFrame(gameLoop);
}

gameLoop();
</script>
