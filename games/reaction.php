<?php
// Jeu de Réflexes
?>

<div class="min-h-screen bg-gradient-to-br from-yellow-400 to-orange-500 p-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">⚡ Jeu de Réflexes</h2>
                    <p class="text-gray-600">Round <span id="roundNum">1</span>/5 - Clique dès que tu vois la cible !</p>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Zone de jeu -->
        <div 
            id="gameZone"
            class="rounded-2xl shadow-2xl flex items-center justify-center cursor-pointer transition-all bg-gray-800"
            style="height: 400px;"
        >
            <div class="text-white text-2xl font-bold" id="gameMessage">Prépare-toi...</div>
        </div>

        <!-- Scores -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mt-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">⚡ Scores</h3>
            <div id="scoreList"></div>
        </div>

    </div>
</div>

<script>
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const players = <?php echo json_encode($players); ?>;
const scores = {};
let round = 1;
let waiting = false;
let showTarget = false;
let startTime = 0;

// Initialiser les scores
players.forEach(p => scores[p] = 0);

function displayScores() {
    const scoreList = document.getElementById('scoreList');
    const sortedPlayers = [...players].sort((a, b) => scores[b] - scores[a]);
    
    scoreList.innerHTML = '';
    sortedPlayers.forEach((player, idx) => {
        const medals = ['🥇', '🥈', '🥉', '👤'];
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between mb-3 p-3 bg-gray-50 rounded-lg';
        div.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="text-xl">${medals[idx] || '👤'}</span>
                <span class="font-medium text-gray-800">${player}</span>
            </div>
            <span class="text-xl font-bold text-yellow-600">${scores[player]} pts</span>
        `;
        scoreList.appendChild(div);
    });
}

function startRound() {
    if (waiting || round > 5) return;
    
    const gameZone = document.getElementById('gameZone');
    const gameMessage = document.getElementById('gameMessage');
    
    gameZone.classList.remove('bg-red-500');
    gameZone.classList.add('bg-gray-800');
    gameMessage.textContent = 'Prépare-toi...';
    gameMessage.style.display = 'block';
    showTarget = false;
    
    // Délai aléatoire avant d'afficher la cible
    const delay = Math.random() * 3000 + 2000;
    setTimeout(() => {
        if (!waiting) {
            gameZone.classList.remove('bg-gray-800');
            gameZone.classList.add('bg-red-500');
            gameMessage.innerHTML = '<div class="text-9xl animate-pulse">🎯</div>';
            showTarget = true;
            startTime = Date.now();
        }
    }, delay);
}

function handleClick() {
    if (waiting) return;
    
    if (!showTarget) {
        // Cliqué trop tôt
        document.getElementById('gameMessage').textContent = '❌ Trop tôt !';
        setTimeout(startRound, 1000);
        return;
    }
    
    // Calculer le temps de réaction
    const reactionTime = Date.now() - startTime;
    const points = Math.max(0, 200 - Math.floor(reactionTime / 10));
    scores[myUsername] += points;
    
    waiting = true;
    showTarget = false;
    
    const gameZone = document.getElementById('gameZone');
    const gameMessage = document.getElementById('gameMessage');
    gameZone.classList.remove('bg-red-500');
    gameZone.classList.add('bg-gray-800');
    gameMessage.textContent = `✓ ${reactionTime}ms (+${points} pts)`;
    
    // Simuler les scores des bots
    setTimeout(() => {
        players.forEach(p => {
            if (p !== myUsername) {
                const botScore = Math.floor(Math.random() * 150) + 50;
                scores[p] += botScore;
            }
        });
        displayScores();
        
        if (round < 5) {
            round++;
            document.getElementById('roundNum').textContent = round;
            gameMessage.textContent = 'Attends le prochain round...';
            setTimeout(() => {
                waiting = false;
                startRound();
            }, 2000);
        } else {
            // Fin de la partie
            const sortedPlayers = [...players].sort((a, b) => scores[b] - scores[a]);
            gameMessage.innerHTML = `
                <div class="text-center">
                    <div class="text-6xl mb-4">🎉</div>
                    <div class="text-white text-2xl font-bold">Partie terminée !</div>
                    <div class="text-white text-lg mt-2">Gagnant : ${sortedPlayers[0]}</div>
                    <div class="text-white text-lg">${scores[sortedPlayers[0]]} points</div>
                </div>
            `;
        }
    }, 1000);
}

document.getElementById('gameZone').onclick = handleClick;

// Démarrer le premier round
displayScores();
startRound();
</script>
