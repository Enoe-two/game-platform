<?php
// Memory - Trouve les paires !
?>

<div class="min-h-screen bg-gradient-to-br from-pink-400 to-rose-600 p-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🧠 Memory</h2>
                    <p class="text-gray-600">Tour de: <span id="currentPlayer" class="font-bold"></span></p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-pink-600">Paires: <span id="pairsFound">0</span>/12</div>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Grille de cartes -->
        <div class="grid grid-cols-6 gap-3 mb-4" id="cardGrid"></div>

        <!-- Scores -->
        <div class="bg-white rounded-2xl shadow-2xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-3">🏆 Scores</h3>
            <div id="scoreBoard"></div>
        </div>

    </div>
</div>

<script>
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const players = <?php echo json_encode($players); ?>;

const emojis = ['🍎', '🍌', '🍇', '🍊', '🍓', '🍉', '🍒', '🍑', '🥝', '🍍', '🥥', '🥭'];
const cards = [...emojis, ...emojis].sort(() => Math.random() - 0.5);

let currentPlayerIndex = 0;
let flippedCards = [];
let matchedPairs = 0;
let scores = {};
let canFlip = true;

players.forEach(p => scores[p] = 0);

function createBoard() {
    const grid = document.getElementById('cardGrid');
    cards.forEach((emoji, idx) => {
        const card = document.createElement('div');
        card.className = 'card aspect-square bg-gradient-to-br from-pink-300 to-rose-400 rounded-xl flex items-center justify-center text-4xl cursor-pointer transform transition-all hover:scale-105 shadow-lg';
        card.dataset.index = idx;
        card.dataset.emoji = emoji;
        card.onclick = () => flipCard(card);
        grid.appendChild(card);
    });
}

function flipCard(card) {
    if (!canFlip || card.classList.contains('flipped') || card.classList.contains('matched')) return;
    if (players[currentPlayerIndex] !== myUsername) return;

    card.textContent = card.dataset.emoji;
    card.classList.add('flipped', 'bg-white');
    flippedCards.push(card);

    if (flippedCards.length === 2) {
        canFlip = false;
        setTimeout(checkMatch, 800);
    }
}

function checkMatch() {
    const [card1, card2] = flippedCards;
    
    if (card1.dataset.emoji === card2.dataset.emoji) {
        card1.classList.add('matched');
        card2.classList.add('matched');
        scores[players[currentPlayerIndex]]++;
        matchedPairs++;
        document.getElementById('pairsFound').textContent = matchedPairs;
        
        if (matchedPairs === 12) {
            setTimeout(() => {
                const winner = Object.entries(scores).sort((a, b) => b[1] - a[1])[0];
                alert(`🎉 Partie terminée ! Gagnant: ${winner[0]} avec ${winner[1]} paires !`);
                window.location.href = '?';
            }, 500);
        }
    } else {
        card1.textContent = '';
        card2.textContent = '';
        card1.classList.remove('flipped', 'bg-white');
        card2.classList.remove('flipped', 'bg-white');
        nextPlayer();
    }
    
    flippedCards = [];
    canFlip = true;
    updateScores();
}

function nextPlayer() {
    currentPlayerIndex = (currentPlayerIndex + 1) % players.length;
    document.getElementById('currentPlayer').textContent = players[currentPlayerIndex];
    
    // IA pour les autres joueurs
    if (players[currentPlayerIndex] !== myUsername) {
        setTimeout(() => {
            const availableCards = Array.from(document.querySelectorAll('.card:not(.matched)'));
            if (availableCards.length > 0) {
                const randomCard = availableCards[Math.floor(Math.random() * availableCards.length)];
                flipCard(randomCard);
                setTimeout(() => {
                    const remainingCards = Array.from(document.querySelectorAll('.card:not(.matched):not(.flipped)'));
                    if (remainingCards.length > 0) {
                        const secondCard = remainingCards[Math.floor(Math.random() * remainingCards.length)];
                        flipCard(secondCard);
                    }
                }, 600);
            }
        }, 1000);
    }
}

function updateScores() {
    const sorted = Object.entries(scores).sort((a, b) => b[1] - a[1]);
    document.getElementById('scoreBoard').innerHTML = sorted.map(([player, score]) => `
        <div class="flex items-center justify-between mb-2 p-2 bg-gray-50 rounded">
            <span class="font-medium">${player}</span>
            <span class="font-bold text-pink-600">${score} paires</span>
        </div>
    `).join('');
}

createBoard();
document.getElementById('currentPlayer').textContent = players[0];
updateScores();
</script>
