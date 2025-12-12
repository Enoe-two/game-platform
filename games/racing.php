<?php
// Jeu de Course de Voitures
?>

<div class="min-h-screen bg-gradient-to-br from-blue-400 to-cyan-500 p-4">
    <div class="max-w-6xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🏎️ Course de Voitures</h2>
                    <p class="text-gray-600">Clique rapidement pour accélérer !</p>
                </div>
                <div class="text-6xl font-bold text-blue-600" id="countdown">3</div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Pistes de course -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <?php foreach ($players as $player): ?>
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-gray-800"><?php echo htmlspecialchars($player); ?></span>
                        <span class="text-sm text-gray-600">
                            <span class="progress-text" data-player="<?php echo htmlspecialchars($player); ?>">0</span>%
                        </span>
                    </div>
                    <div class="relative h-8 bg-gray-200 rounded-full overflow-hidden">
                        <div 
                            class="progress-bar absolute h-full transition-all <?php echo $player === $_SESSION['username'] ? 'bg-gradient-to-r from-blue-500 to-cyan-500' : 'bg-gray-400'; ?>"
                            data-player="<?php echo htmlspecialchars($player); ?>"
                            style="width: 0%"
                        ></div>
                        <div 
                            class="car absolute text-2xl transition-all"
                            data-player="<?php echo htmlspecialchars($player); ?>"
                            style="left: 0%; top: -2px;"
                        >
                            🏎️
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Bouton Boost -->
        <button 
            id="boostBtn"
            class="w-full py-6 rounded-2xl font-bold text-xl transition-all bg-gray-300 text-gray-500 cursor-not-allowed"
            disabled
        >
            Prépare-toi...
        </button>

        <!-- Classement -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mt-4" id="leaderboard" style="display: none;">
            <h3 class="text-xl font-bold text-gray-800 mb-3">🏆 Classement</h3>
            <div id="ranking"></div>
        </div>

    </div>
</div>

<script>
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const players = <?php echo json_encode($players); ?>;
const positions = {};
const finished = [];
let countdown = 3;
let raceStarted = false;

// Initialiser les positions
players.forEach(p => positions[p] = 0);

// Compte à rebours
const countdownInterval = setInterval(() => {
    countdown--;
    document.getElementById('countdown').textContent = countdown;
    
    if (countdown <= 0) {
        clearInterval(countdownInterval);
        document.getElementById('countdown').textContent = 'GO!';
        setTimeout(() => {
            document.getElementById('countdown').style.display = 'none';
        }, 1000);
        
        raceStarted = true;
        const boostBtn = document.getElementById('boostBtn');
        boostBtn.disabled = false;
        boostBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
        boostBtn.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-cyan-600', 'text-white', 'hover:shadow-lg', 'active:scale-95');
        boostBtn.textContent = 'BOOST ! 🚀';
        
        startBotRace();
    }
}, 1000);

// Course des bots
function startBotRace() {
    const raceInterval = setInterval(() => {
        players.forEach(p => {
            if (p !== myUsername && !finished.includes(p)) {
                positions[p] = Math.min(100, positions[p] + Math.random() * 2.5);
                updateProgress(p);
                
                if (positions[p] >= 100 && !finished.includes(p)) {
                    finished.push(p);
                    updateLeaderboard();
                }
            }
        });
        
        if (finished.length >= players.length) {
            clearInterval(raceInterval);
        }
    }, 100);
}

// Boost du joueur
document.getElementById('boostBtn').addEventListener('click', function() {
    if (!raceStarted || finished.includes(myUsername)) return;
    
    positions[myUsername] = Math.min(100, positions[myUsername] + 5);
    updateProgress(myUsername);
    
    if (positions[myUsername] >= 100 && !finished.includes(myUsername)) {
        finished.push(myUsername);
        this.disabled = true;
        this.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
        this.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-cyan-600', 'text-white');
        this.textContent = '🏁 Arrivé !';
        updateLeaderboard();
    }
});

function updateProgress(player) {
    const progress = Math.round(positions[player]);
    const bar = document.querySelector(`.progress-bar[data-player="${player}"]`);
    const car = document.querySelector(`.car[data-player="${player}"]`);
    const text = document.querySelector(`.progress-text[data-player="${player}"]`);
    
    if (bar) bar.style.width = progress + '%';
    if (car) car.style.left = Math.max(0, progress - 3) + '%';
    if (text) text.textContent = progress;
}

function updateLeaderboard() {
    const leaderboard = document.getElementById('leaderboard');
    const ranking = document.getElementById('ranking');
    
    leaderboard.style.display = 'block';
    ranking.innerHTML = '';
    
    finished.forEach((player, idx) => {
        const medals = ['🥇', '🥈', '🥉', '🏅'];
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 mb-2';
        div.innerHTML = `
            <span class="text-2xl">${medals[idx] || '🏅'}</span>
            <span class="font-medium text-gray-800">${player}</span>
        `;
        ranking.appendChild(div);
    });
}
</script>
