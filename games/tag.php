<?php
// Jeu Loup Touche-Touche
$wolf = $players[array_rand($players)];
?>

<div class="p-4">
    <div class="max-w-6xl mx-auto">
        
        <!-- En-tête du jeu -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🎯 Loup Touche-Touche</h2>
                    <p class="text-gray-600">
                        <?php if ($wolf === $_SESSION['username']): ?>
                            🐺 Tu es le loup ! Attrape les autres en cliquant sur eux !
                        <?php else: ?>
                            🏃 Fuis le loup ! Déplace-toi en cliquant sur le terrain !
                        <?php endif; ?>
                    </p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-orange-600" id="timer">60</div>
                    <div class="text-sm text-gray-600">secondes</div>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Terrain de jeu -->
        <div class="bg-green-200 rounded-2xl shadow-2xl relative overflow-hidden" style="height: 500px;" id="gameField">
            <?php foreach ($players as $player): ?>
                <div 
                    class="player absolute w-12 h-12 rounded-full flex items-center justify-center font-bold text-white transition-all cursor-pointer <?php echo $player === $wolf ? 'bg-red-600 text-2xl' : 'bg-blue-600'; ?>"
                    data-player="<?php echo htmlspecialchars($player); ?>"
                    style="left: <?php echo rand(10, 80); ?>%; top: <?php echo rand(10, 80); ?>%; transform: translate(-50%, -50%);"
                >
                    <?php echo $player === $wolf ? '🐺' : strtoupper($player[0]); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Joueurs attrapés -->
        <div class="bg-white rounded-2xl shadow-2xl p-4 mt-4">
            <h3 class="font-bold text-gray-800 mb-2">Joueurs attrapés: <span id="caughtCount">0</span></h3>
            <div class="flex flex-wrap gap-2" id="caughtList"></div>
        </div>

    </div>
</div>

<script>
const isWolf = <?php echo json_encode($wolf === $_SESSION['username']); ?>;
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const caught = [];
let timeLeft = 60;

// Timer
const timerInterval = setInterval(() => {
    timeLeft--;
    document.getElementById('timer').textContent = timeLeft;
    if (timeLeft <= 0) {
        clearInterval(timerInterval);
        alert('Temps écoulé ! Le loup a attrapé ' + caught.length + ' joueur(s) !');
        window.location.href = '?';
    }
}, 1000);

// Déplacement des autres joueurs (simulation)
setInterval(() => {
    document.querySelectorAll('.player').forEach(player => {
        if (player.dataset.player !== myUsername && !caught.includes(player.dataset.player)) {
            const currentLeft = parseFloat(player.style.left);
            const currentTop = parseFloat(player.style.top);
            const newLeft = Math.max(5, Math.min(95, currentLeft + (Math.random() - 0.5) * 5));
            const newTop = Math.max(5, Math.min(90, currentTop + (Math.random() - 0.5) * 5));
            player.style.left = newLeft + '%';
            player.style.top = newTop + '%';
        }
    });
}, 500);

// Déplacement du joueur
document.getElementById('gameField').addEventListener('click', (e) => {
    if (caught.includes(myUsername)) return;
    
    const rect = e.currentTarget.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    
    const myPlayer = document.querySelector(`.player[data-player="${myUsername}"]`);
    if (myPlayer) {
        myPlayer.style.left = x + '%';
        myPlayer.style.top = y + '%';
        
        // Vérifier les collisions si on est le loup
        if (isWolf) {
            document.querySelectorAll('.player').forEach(player => {
                if (player.dataset.player !== myUsername && !caught.includes(player.dataset.player)) {
                    const targetLeft = parseFloat(player.style.left);
                    const targetTop = parseFloat(player.style.top);
                    const dist = Math.sqrt(Math.pow(targetLeft - x, 2) + Math.pow(targetTop - y, 2));
                    
                    if (dist < 8) {
                        caught.push(player.dataset.player);
                        player.classList.remove('bg-blue-600');
                        player.classList.add('bg-gray-400');
                        player.textContent = '❌';
                        document.getElementById('caughtCount').textContent = caught.length;
                        
                        const tag = document.createElement('span');
                        tag.className = 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm';
                        tag.textContent = player.dataset.player;
                        document.getElementById('caughtList').appendChild(tag);
                    }
                }
            });
        }
    }
});
</script>

<style>
.bg-gradient-to-br {
    background: linear-gradient(to bottom right, #fb923c, #ef4444);
}
</style>
