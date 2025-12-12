<?php
// Jeu Loup-Garou
$roles = ['Loup-Garou', 'Loup-Garou', 'Voyante', 'Villageois', 'Villageois', 'Chasseur'];
shuffle($roles);

// Compléter avec des villageois si besoin
while (count($roles) < count($players)) {
    $roles[] = 'Villageois';
}

$playerRoles = array_combine($players, array_slice($roles, 0, count($players)));
$myRole = $playerRoles[$_SESSION['username']];
?>

<div class="min-h-screen bg-gradient-to-br from-purple-900 to-indigo-900 p-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🌙 Loup-Garou</h2>
                    <p class="text-gray-600">
                        Tour <span id="roundNum">1</span> - Phase: <span id="phaseText">🌙 Nuit</span>
                    </p>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Rôle du joueur -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="text-center mb-4">
                <div class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg font-bold text-xl">
                    <?php 
                    $roleIcon = ['Loup-Garou' => '🐺', 'Voyante' => '🔮', 'Chasseur' => '🏹', 'Villageois' => '👨‍🌾'];
                    echo $roleIcon[$myRole];
                    ?> <?php echo $myRole; ?>
                </div>
            </div>
            <p class="text-center text-gray-600" id="roleDescription">
                <?php if ($myRole === 'Loup-Garou'): ?>
                    Tu es un Loup-Garou ! Choisis une victime pendant la nuit.
                <?php elseif ($myRole === 'Voyante'): ?>
                    Tu es la Voyante ! Tu peux voir le rôle d'un joueur chaque nuit.
                <?php elseif ($myRole === 'Chasseur'): ?>
                    Tu es le Chasseur ! Si tu meurs, tu peux éliminer un joueur.
                <?php else: ?>
                    Tu es un Villageois ! Vote pour éliminer les suspects.
                <?php endif; ?>
            </p>
        </div>

        <!-- Liste des joueurs -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                Joueurs vivants (<span id="aliveCount"><?php echo count($players); ?></span>)
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3" id="playerList"></div>
        </div>

        <!-- Bouton de vote -->
        <button 
            id="voteBtn"
            class="w-full py-4 bg-gray-300 text-gray-500 rounded-2xl font-bold text-lg cursor-not-allowed"
            disabled
        >
            Choisis un joueur
        </button>

        <!-- Joueurs éliminés -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mt-4" id="deadSection" style="display: none;">
            <h3 class="text-xl font-bold text-gray-800 mb-3">💀 Éliminés</h3>
            <div class="flex flex-wrap gap-2" id="deadList"></div>
        </div>

    </div>
</div>

<script>
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const myRole = <?php echo json_encode($myRole); ?>;
const playerRoles = <?php echo json_encode($playerRoles); ?>;
const players = <?php echo json_encode($players); ?>;

let phase = 'night'; // night or day
let round = 1;
let alive = [...players];
let dead = [];
let selectedVote = null;

function displayPlayers() {
    const playerList = document.getElementById('playerList');
    playerList.innerHTML = '';
    
    alive.forEach(player => {
        const isMe = player === myUsername;
        const canVote = (phase === 'night' && myRole === 'Loup-Garou' && !isMe) || 
                        (phase === 'day' && !isMe);
        
        const btn = document.createElement('button');
        btn.className = `p-4 rounded-lg font-medium transition-all ${
            selectedVote === player 
                ? 'bg-red-500 text-white' 
                : isMe 
                ? 'bg-green-100 text-green-800' 
                : canVote
                ? 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                : 'bg-gray-100 text-gray-800 opacity-50 cursor-not-allowed'
        }`;
        btn.textContent = player + (isMe ? ' (Toi)' : '');
        btn.disabled = !canVote;
        btn.onclick = () => selectVote(player);
        
        playerList.appendChild(btn);
    });
    
    document.getElementById('aliveCount').textContent = alive.length;
}

function selectVote(player) {
    selectedVote = player;
    displayPlayers();
    
    const voteBtn = document.getElementById('voteBtn');
    voteBtn.disabled = false;
    voteBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    voteBtn.classList.add('bg-gradient-to-r', 'from-purple-600', 'to-pink-600', 'text-white', 'hover:shadow-lg');
    voteBtn.textContent = 'Confirmer le vote';
}

function endPhase() {
    // Simuler l'élimination
    if (selectedVote) {
        dead.push(selectedVote);
        alive = alive.filter(p => p !== selectedVote);
        
        const deadSection = document.getElementById('deadSection');
        deadSection.style.display = 'block';
        
        const deadList = document.getElementById('deadList');
        const span = document.createElement('span');
        span.className = 'px-3 py-1 bg-gray-200 text-gray-600 rounded-full line-through';
        span.textContent = selectedVote;
        deadList.appendChild(span);
        
        // Vérifier fin de partie
        const wolves = alive.filter(p => playerRoles[p] === 'Loup-Garou').length;
        const villagers = alive.filter(p => playerRoles[p] !== 'Loup-Garou').length;
        
        if (wolves === 0) {
            alert('🎉 Les villageois gagnent ! Tous les loups-garous ont été éliminés !');
            window.location.href = '?';
            return;
        } else if (wolves >= villagers) {
            alert('🐺 Les loups-garous gagnent ! Ils sont majoritaires !');
            window.location.href = '?';
            return;
        }
    }
    
    // Changer de phase
    if (phase === 'night') {
        phase = 'day';
        document.getElementById('phaseText').textContent = '☀️ Jour';
        document.getElementById('roleDescription').textContent = 'Vote pour éliminer un suspect';
    } else {
        phase = 'night';
        round++;
        document.getElementById('roundNum').textContent = round;
        document.getElementById('phaseText').textContent = '🌙 Nuit';
        document.getElementById('roleDescription').textContent = 
            myRole === 'Loup-Garou' ? 'Choisis une victime' : 'Dors paisiblement...';
    }
    
    selectedVote = null;
    displayPlayers();
    
    const voteBtn = document.getElementById('voteBtn');
    voteBtn.disabled = true;
    voteBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    voteBtn.classList.remove('bg-gradient-to-r', 'from-purple-600', 'to-pink-600', 'text-white', 'hover:shadow-lg');
    voteBtn.textContent = 'Choisis un joueur';
}

document.getElementById('voteBtn').onclick = endPhase;

// Initialiser
displayPlayers();
</script>
