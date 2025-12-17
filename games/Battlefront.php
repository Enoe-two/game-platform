<?php
// Battlefront - Jeu de conquête territoriale stratégique
?>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-800 to-slate-900 p-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-indigo-900 to-purple-900 rounded-2xl shadow-2xl p-6 mb-4 border-2 border-yellow-500">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">🌍 Battlefront - Conquête du Monde</h2>
                    <p class="text-gray-300">Clique sur tes territoires pour envoyer des troupes et conquérir le monde !</p>
                </div>
                <a href="?" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold">
                    ← Quitter la Guerre
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            
            <!-- Carte principale -->
            <div class="lg:col-span-3">
                <div class="bg-gray-900 rounded-2xl shadow-2xl p-4 border-2 border-blue-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">🗺️ Carte du Monde</h3>
                        <div class="text-white text-sm">
                            Tour: <span id="turnNumber" class="text-yellow-400 font-bold">1</span>
                        </div>
                    </div>
                    <canvas id="mapCanvas" width="900" height="600" class="bg-slate-800 rounded-xl cursor-pointer mx-auto block border-2 border-gray-700"></canvas>
                    
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button id="endTurnBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition-all transform hover:scale-105">
                            ✅ Terminer mon Tour
                        </button>
                        <button id="fortifyBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition-all transform hover:scale-105">
                            🛡️ Fortifier Position
                        </button>
                    </div>
                </div>
            </div>

            <!-- Panneau latéral -->
            <div class="space-y-4">
                
                <!-- Stats du joueur -->
                <div class="bg-gradient-to-br from-blue-900 to-indigo-900 rounded-2xl shadow-2xl p-4 border-2 border-blue-400">
                    <h3 class="text-lg font-bold text-white mb-3">👤 Ton Empire</h3>
                    <div class="space-y-2">
                        <div class="bg-black bg-opacity-40 rounded p-2">
                            <div class="text-gray-300 text-sm">Territoires</div>
                            <div class="text-2xl font-bold text-yellow-400" id="myTerritories">0</div>
                        </div>
                        <div class="bg-black bg-opacity-40 rounded p-2">
                            <div class="text-gray-300 text-sm">Armée Totale</div>
                            <div class="text-2xl font-bold text-red-400" id="myTroops">0</div>
                        </div>
                        <div class="bg-black bg-opacity-40 rounded p-2">
                            <div class="text-gray-300 text-sm">Renfort Prochain Tour</div>
                            <div class="text-2xl font-bold text-green-400" id="nextReinforcement">0</div>
                        </div>
                    </div>
                </div>

                <!-- Info territoire sélectionné -->
                <div class="bg-gradient-to-br from-purple-900 to-pink-900 rounded-2xl shadow-2xl p-4 border-2 border-purple-400" id="territoryInfo" style="display: none;">
                    <h3 class="text-lg font-bold text-white mb-3">📍 Territoire Sélectionné</h3>
                    <div class="space-y-2">
                        <div class="text-white font-bold text-lg" id="terrName"></div>
                        <div class="text-gray-300">
                            Propriétaire: <span id="terrOwner" class="font-bold"></span>
                        </div>
                        <div class="text-gray-300">
                            Troupes: <span id="terrTroops" class="font-bold text-red-400"></span>
                        </div>
                        <div class="text-gray-300 text-sm" id="terrNeighbors"></div>
                    </div>
                </div>

                <!-- Classement -->
                <div class="bg-gradient-to-br from-yellow-900 to-orange-900 rounded-2xl shadow-2xl p-4 border-2 border-yellow-400">
                    <h3 class="text-lg font-bold text-white mb-3">🏆 Classement</h3>
                    <div id="leaderboard" class="space-y-2"></div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
const canvas = document.getElementById('mapCanvas');
const ctx = canvas.getContext('2d');

const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const allPlayers = <?php echo json_encode($players); ?>;

// Couleurs des joueurs
const playerColors = {
    [allPlayers[0]]: '#3B82F6',
    [allPlayers[1]]: '#EF4444', 
    [allPlayers[2]]: '#10B981',
    [allPlayers[3]]: '#F59E0B',
    [allPlayers[4]]: '#8B5CF6',
    [allPlayers[5]]: '#EC4899',
};

// Définir les territoires (régions du monde)
const territories = [
    // Europe
    { id: 'france', name: 'France', x: 450, y: 200, neighbors: ['espagne', 'allemagne', 'italie', 'angleterre'] },
    { id: 'allemagne', name: 'Allemagne', x: 500, y: 180, neighbors: ['france', 'pologne', 'italie'] },
    { id: 'italie', name: 'Italie', x: 500, y: 230, neighbors: ['france', 'allemagne', 'grece'] },
    { id: 'espagne', name: 'Espagne', x: 420, y: 240, neighbors: ['france', 'portugal'] },
    { id: 'angleterre', name: 'Angleterre', x: 440, y: 150, neighbors: ['france', 'scandinavie'] },
    { id: 'scandinavie', name: 'Scandinavie', x: 500, y: 130, neighbors: ['angleterre', 'russie'] },
    { id: 'russie', name: 'Russie', x: 580, y: 160, neighbors: ['scandinavie', 'allemagne', 'moyen_orient', 'asie_centrale'] },
    { id: 'grece', name: 'Grèce', x: 540, y: 250, neighbors: ['italie', 'turquie'] },
    { id: 'portugal', name: 'Portugal', x: 390, y: 240, neighbors: ['espagne'] },
    { id: 'pologne', name: 'Pologne', x: 540, y: 180, neighbors: ['allemagne', 'russie'] },
    
    // Asie
    { id: 'turquie', name: 'Turquie', x: 570, y: 260, neighbors: ['grece', 'moyen_orient'] },
    { id: 'moyen_orient', name: 'Moyen-Orient', x: 600, y: 280, neighbors: ['turquie', 'russie', 'inde', 'egypte'] },
    { id: 'inde', name: 'Inde', x: 670, y: 300, neighbors: ['moyen_orient', 'asie_centrale', 'chine', 'asie_sud_est'] },
    { id: 'chine', name: 'Chine', x: 720, y: 250, neighbors: ['inde', 'asie_centrale', 'mongolie', 'asie_sud_est'] },
    { id: 'japon', name: 'Japon', x: 800, y: 230, neighbors: ['mongolie'] },
    { id: 'asie_centrale', name: 'Asie Centrale', x: 640, y: 210, neighbors: ['russie', 'inde', 'chine'] },
    { id: 'mongolie', name: 'Mongolie', x: 730, y: 200, neighbors: ['chine', 'japon', 'siberie'] },
    { id: 'siberie', name: 'Sibérie', x: 680, y: 130, neighbors: ['russie', 'mongolie'] },
    { id: 'asie_sud_est', name: 'Asie Sud-Est', x: 730, y: 330, neighbors: ['inde', 'chine', 'indonesie'] },
    
    // Afrique
    { id: 'egypte', name: 'Égypte', x: 540, y: 310, neighbors: ['moyen_orient', 'afrique_nord', 'afrique_est'] },
    { id: 'afrique_nord', name: 'Afrique du Nord', x: 480, y: 320, neighbors: ['egypte', 'afrique_ouest', 'afrique_centrale'] },
    { id: 'afrique_ouest', name: 'Afrique de l\'Ouest', x: 440, y: 350, neighbors: ['afrique_nord', 'afrique_centrale'] },
    { id: 'afrique_centrale', name: 'Afrique Centrale', x: 510, y: 370, neighbors: ['afrique_nord', 'afrique_ouest', 'afrique_est', 'afrique_sud'] },
    { id: 'afrique_est', name: 'Afrique de l\'Est', x: 560, y: 360, neighbors: ['egypte', 'afrique_centrale', 'afrique_sud', 'madagascar'] },
    { id: 'afrique_sud', name: 'Afrique du Sud', x: 530, y: 430, neighbors: ['afrique_centrale', 'afrique_est'] },
    { id: 'madagascar', name: 'Madagascar', x: 600, y: 410, neighbors: ['afrique_est'] },
    
    // Amériques
    { id: 'alaska', name: 'Alaska', x: 120, y: 100, neighbors: ['territoires_nord_ouest', 'alberta'] },
    { id: 'territoires_nord_ouest', name: 'Territoires du Nord', x: 180, y: 120, neighbors: ['alaska', 'alberta', 'ontario', 'groenland'] },
    { id: 'groenland', name: 'Groenland', x: 280, y: 90, neighbors: ['territoires_nord_ouest', 'ontario', 'quebec', 'islande'] },
    { id: 'islande', name: 'Islande', x: 370, y: 130, neighbors: ['groenland', 'angleterre', 'scandinavie'] },
    { id: 'alberta', name: 'Alberta', x: 160, y: 160, neighbors: ['alaska', 'territoires_nord_ouest', 'ontario', 'usa_ouest'] },
    { id: 'ontario', name: 'Ontario', x: 210, y: 170, neighbors: ['territoires_nord_ouest', 'alberta', 'quebec', 'usa_est'] },
    { id: 'quebec', name: 'Québec', x: 250, y: 160, neighbors: ['groenland', 'ontario', 'usa_est'] },
    { id: 'usa_ouest', name: 'USA Ouest', x: 160, y: 210, neighbors: ['alberta', 'ontario', 'usa_est', 'amerique_centrale'] },
    { id: 'usa_est', name: 'USA Est', x: 220, y: 220, neighbors: ['ontario', 'quebec', 'usa_ouest', 'amerique_centrale'] },
    { id: 'amerique_centrale', name: 'Amérique Centrale', x: 180, y: 270, neighbors: ['usa_ouest', 'usa_est', 'venezuela'] },
    { id: 'venezuela', name: 'Venezuela', x: 230, y: 320, neighbors: ['amerique_centrale', 'perou', 'bresil'] },
    { id: 'perou', name: 'Pérou', x: 220, y: 370, neighbors: ['venezuela', 'bresil', 'argentine'] },
    { id: 'bresil', name: 'Brésil', x: 280, y: 370, neighbors: ['venezuela', 'perou', 'argentine'] },
    { id: 'argentine', name: 'Argentine', x: 250, y: 440, neighbors: ['perou', 'bresil'] },
    
    // Océanie
    { id: 'indonesie', name: 'Indonésie', x: 750, y: 380, neighbors: ['asie_sud_est', 'australie_ouest', 'nouvelle_guinee'] },
    { id: 'nouvelle_guinee', name: 'Nouvelle-Guinée', x: 800, y: 380, neighbors: ['indonesie', 'australie_est'] },
    { id: 'australie_ouest', name: 'Australie Ouest', x: 760, y: 450, neighbors: ['indonesie', 'australie_est'] },
    { id: 'australie_est', name: 'Australie Est', x: 820, y: 460, neighbors: ['nouvelle_guinee', 'australie_ouest'] },
];

// État du jeu
let gameState = {};
territories.forEach((terr, idx) => {
    const owner = allPlayers[idx % allPlayers.length];
    gameState[terr.id] = {
        owner: owner,
        troops: Math.floor(Math.random() * 3) + 3
    };
});

let selectedTerritory = null;
let currentPlayer = myUsername;
let turnNumber = 1;
let reinforcements = 0;
let attackMode = false;
let sourceTerritory = null;

// Calculer les renforts
function calculateReinforcements(player) {
    const territoriesOwned = Object.values(gameState).filter(t => t.owner === player).length;
    return Math.max(3, Math.floor(territoriesOwned / 3));
}

// Dessiner la carte
function drawMap() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Dessiner les connexions
    ctx.strokeStyle = '#4B5563';
    ctx.lineWidth = 2;
    territories.forEach(terr => {
        terr.neighbors.forEach(neighborId => {
            const neighbor = territories.find(t => t.id === neighborId);
            if (neighbor) {
                ctx.beginPath();
                ctx.moveTo(terr.x, terr.y);
                ctx.lineTo(neighbor.x, neighbor.y);
                ctx.stroke();
            }
        });
    });
    
    // Dessiner les territoires
    territories.forEach(terr => {
        const state = gameState[terr.id];
        const color = playerColors[state.owner];
        
        // Cercle du territoire
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(terr.x, terr.y, 25, 0, Math.PI * 2);
        ctx.fill();
        
        // Bordure
        ctx.strokeStyle = terr === selectedTerritory ? '#FFF' : '#000';
        ctx.lineWidth = terr === selectedTerritory ? 4 : 2;
        ctx.stroke();
        
        // Nombre de troupes
        ctx.fillStyle = '#FFF';
        ctx.font = 'bold 18px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(state.troops, terr.x, terr.y);
        
        // Nom du territoire
        ctx.fillStyle = '#FFF';
        ctx.font = '11px Arial';
        ctx.fillText(terr.name, terr.x, terr.y + 40);
    });
}

// Clic sur la carte
canvas.addEventListener('click', (e) => {
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    // Trouver le territoire cliqué
    const clicked = territories.find(terr => {
        const dist = Math.hypot(terr.x - x, terr.y - y);
        return dist < 25;
    });
    
    if (clicked) {
        handleTerritoryClick(clicked);
    }
});

function handleTerritoryClick(territory) {
    const state = gameState[territory.id];
    
    if (currentPlayer !== myUsername) {
        alert('Ce n\'est pas ton tour !');
        return;
    }
    
    // Si on a des renforts à placer
    if (reinforcements > 0 && state.owner === myUsername) {
        state.troops++;
        reinforcements--;
        updateUI();
        return;
    }
    
    // Mode attaque
    if (!sourceTerritory) {
        // Sélectionner le territoire source
        if (state.owner === myUsername && state.troops > 1) {
            sourceTerritory = territory;
            selectedTerritory = territory;
            showTerritoryInfo(territory);
        }
    } else {
        // Attaquer le territoire cible
        if (sourceTerritory.neighbors.includes(territory.id)) {
            if (state.owner !== myUsername) {
                attack(sourceTerritory, territory);
            } else {
                // Déplacer des troupes
                moveTroops(sourceTerritory, territory);
            }
        }
        sourceTerritory = null;
    }
    
    drawMap();
}

function attack(source, target) {
    const sourceState = gameState[source.id];
    const targetState = gameState[target.id];
    
    if (sourceState.troops <= 1) {
        alert('Tu as besoin de plus de troupes pour attaquer !');
        return;
    }
    
    // Combat simplifié
    const attackDice = Math.min(3, sourceState.troops - 1);
    const defendDice = Math.min(2, targetState.troops);
    
    const attackRoll = Math.floor(Math.random() * 6 * attackDice) + attackDice;
    const defendRoll = Math.floor(Math.random() * 6 * defendDice) + defendDice;
    
    if (attackRoll > defendRoll) {
        // Attaquant gagne
        targetState.troops -= 2;
        sourceState.troops -= 1;
        
        if (targetState.troops <= 0) {
            // Conquête !
            targetState.owner = myUsername;
            targetState.troops = sourceState.troops - 1;
            sourceState.troops = 1;
            alert(`🎉 Tu as conquis ${target.name} !`);
            checkVictory();
        }
    } else {
        // Défenseur gagne
        sourceState.troops -= 2;
        targetState.troops -= 1;
        
        if (sourceState.troops <= 0) {
            sourceState.troops = 1;
        }
    }
    
    updateUI();
}

function moveTroops(source, target) {
    const sourceState = gameState[source.id];
    const targetState = gameState[target.id];
    
    if (sourceState.troops > 1) {
        const toMove = Math.floor(sourceState.troops / 2);
        sourceState.troops -= toMove;
        targetState.troops += toMove;
        updateUI();
    }
}

function showTerritoryInfo(territory) {
    const state = gameState[territory.id];
    const info = document.getElementById('territoryInfo');
    info.style.display = 'block';
    
    document.getElementById('terrName').textContent = territory.name;
    document.getElementById('terrOwner').textContent = state.owner;
    document.getElementById('terrOwner').style.color = playerColors[state.owner];
    document.getElementById('terrTroops').textContent = state.troops;
    
    const neighbors = territory.neighbors.map(id => 
        territories.find(t => t.id === id).name
    ).join(', ');
    document.getElementById('terrNeighbors').textContent = 'Voisins: ' + neighbors;
}

function endTurn() {
    // Passer au joueur suivant
    const currentIndex = allPlayers.indexOf(currentPlayer);
    currentPlayer = allPlayers[(currentIndex + 1) % allPlayers.length];
    
    if (currentPlayer === myUsername) {
        turnNumber++;
        reinforcements = calculateReinforcements(myUsername);
        alert(`Tour ${turnNumber} ! Tu reçois ${reinforcements} renforts à placer.`);
    }
    
    sourceTerritory = null;
    selectedTerritory = null;
    
    // IA simple pour les bots
    if (currentPlayer !== myUsername) {
        setTimeout(() => {
            playBotTurn();
        }, 1000);
    }
    
    updateUI();
}

function playBotTurn() {
    // IA très simple : attaque aléatoire
    const botTerritories = territories.filter(t => gameState[t.id].owner === currentPlayer);
    
    botTerritories.forEach(terr => {
        const state = gameState[terr.id];
        if (state.troops > 2) {
            const enemyNeighbors = terr.neighbors.filter(nId => 
                gameState[nId].owner !== currentPlayer
            );
            
            if (enemyNeighbors.length > 0 && Math.random() < 0.5) {
                const target = territories.find(t => t.id === enemyNeighbors[0]);
                attack(terr, target);
            }
        }
    });
    
    setTimeout(endTurn, 500);
}

function checkVictory() {
    const myTerritories = Object.values(gameState).filter(t => t.owner === myUsername).length;
    if (myTerritories === territories.length) {
        setTimeout(() => {
            alert('🎉🌍 VICTOIRE ! Tu as conquis le monde entier !');
            window.location.href = '?';
        }, 1000);
    }
}

function updateUI() {
    // Stats du joueur
    const myTerritories = Object.values(gameState).filter(t => t.owner === myUsername).length;
    const myTroops = Object.entries(gameState)
        .filter(([_, state]) => state.owner === myUsername)
        .reduce((sum, [_, state]) => sum + state.troops, 0);
    
    document.getElementById('myTerritories').textContent = myTerritories;
    document.getElementById('myTroops').textContent = myTroops;
    document.getElementById('nextReinforcement').textContent = reinforcements || calculateReinforcements(myUsername);
    document.getElementById('turnNumber').textContent = turnNumber;
    
    // Leaderboard
    const leaderboard = allPlayers.map(player => ({
        player,
        territories: Object.values(gameState).filter(t => t.owner === player).length,
        troops: Object.entries(gameState)
            .filter(([_, state]) => state.owner === player)
            .reduce((sum, [_, state]) => sum + state.troops, 0)
    })).sort((a, b) => b.territories - a.territories);
    
    document.getElementById('leaderboard').innerHTML = leaderboard.map((item, idx) => `
        <div class="bg-black bg-opacity-40 rounded p-2 ${item.player === myUsername ? 'border-2 border-yellow-400' : ''}">
            <div class="flex items-center justify-between">
                <span class="font-bold" style="color: ${playerColors[item.player]}">${idx + 1}. ${item.player}</span>
                <span class="text-white text-sm">${item.territories} 🗺️</span>
            </div>
        </div>
    `).join('');
    
    drawMap();
}

document.getElementById('endTurnBtn').onclick = endTurn;
document.getElementById('fortifyBtn').onclick = () => {
    alert('Sélectionne deux de tes territoires adjacents pour déplacer des troupes.');
};

// Initialiser
reinforcements = calculateReinforcements(myUsername);
updateUI();
alert(`Bienvenue ! Place tes ${reinforcements} renforts en cliquant sur tes territoires.`);
</script>
