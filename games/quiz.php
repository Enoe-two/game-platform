<?php
// Quiz Party
$questions = [
    ['q' => 'Quelle est la capitale de la France ?', 'options' => ['Paris', 'Lyon', 'Marseille', 'Bordeaux'], 'answer' => 0],
    ['q' => 'Combien font 7 x 8 ?', 'options' => ['54', '56', '64', '48'], 'answer' => 1],
    ['q' => 'Quel est le plus grand océan ?', 'options' => ['Atlantique', 'Indien', 'Arctique', 'Pacifique'], 'answer' => 3],
    ['q' => 'En quelle année l\'homme a-t-il marché sur la Lune ?', 'options' => ['1965', '1969', '1972', '1975'], 'answer' => 1],
    ['q' => 'Quelle est la planète la plus proche du Soleil ?', 'options' => ['Vénus', 'Mercure', 'Mars', 'Terre'], 'answer' => 1],
    ['q' => 'Combien de continents y a-t-il ?', 'options' => ['5', '6', '7', '8'], 'answer' => 2],
    ['q' => 'Qui a peint la Joconde ?', 'options' => ['Picasso', 'Van Gogh', 'Léonard de Vinci', 'Monet'], 'answer' => 2],
    ['q' => 'Quelle est la capitale du Japon ?', 'options' => ['Séoul', 'Pékin', 'Tokyo', 'Bangkok'], 'answer' => 2],
];
?>

<div class="min-h-screen bg-gradient-to-br from-green-400 to-emerald-500 p-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🏆 Quiz Party</h2>
                    <p class="text-gray-600">Question <span id="currentQ">1</span>/<?php echo count($questions); ?></p>
                </div>
                <a href="?" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Question -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 mb-4" id="questionCard">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center" id="question"></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="options"></div>
        </div>

        <!-- Scores -->
        <div class="bg-white rounded-2xl shadow-2xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">🏆 Scores</h3>
            <div id="scoreList"></div>
        </div>

    </div>
</div>

<script>
const questions = <?php echo json_encode($questions); ?>;
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;
const players = <?php echo json_encode($players); ?>;
let currentQuestion = 0;
let answered = false;
const scores = {};

// Initialiser les scores
players.forEach(p => scores[p] = 0);

function displayQuestion() {
    const q = questions[currentQuestion];
    document.getElementById('currentQ').textContent = currentQuestion + 1;
    document.getElementById('question').textContent = q.q;
    
    const optionsDiv = document.getElementById('options');
    optionsDiv.innerHTML = '';
    
    q.options.forEach((opt, idx) => {
        const btn = document.createElement('button');
        btn.className = 'p-6 rounded-xl font-medium text-lg transition-all bg-gray-100 text-gray-800 hover:bg-gray-200 active:scale-95';
        btn.textContent = opt;
        btn.onclick = () => handleAnswer(idx);
        optionsDiv.appendChild(btn);
    });
    
    answered = false;
}

function handleAnswer(selectedIdx) {
    if (answered) return;
    answered = true;
    
    const q = questions[currentQuestion];
    const correctIdx = q.answer;
    const buttons = document.querySelectorAll('#options button');
    
    buttons.forEach((btn, idx) => {
        btn.disabled = true;
        btn.classList.remove('hover:bg-gray-200', 'active:scale-95');
        
        if (idx === correctIdx) {
            btn.classList.remove('bg-gray-100', 'text-gray-800');
            btn.classList.add('bg-green-500', 'text-white');
        } else if (idx === selectedIdx) {
            btn.classList.remove('bg-gray-100', 'text-gray-800');
            btn.classList.add('bg-red-500', 'text-white');
        } else {
            btn.classList.add('bg-gray-200', 'text-gray-600');
        }
    });
    
    // Mettre à jour les scores
    if (selectedIdx === correctIdx) {
        scores[myUsername] += 10;
    }
    
    // Simuler les réponses des bots
    setTimeout(() => {
        players.forEach(p => {
            if (p !== myUsername) {
                const botAnswer = Math.random() > 0.3 ? correctIdx : Math.floor(Math.random() * 4);
                if (botAnswer === correctIdx) {
                    scores[p] += 10;
                }
            }
        });
        updateScores();
    }, 500);
    
    // Passer à la question suivante
    setTimeout(() => {
        currentQuestion++;
        if (currentQuestion < questions.length) {
            displayQuestion();
        } else {
            showFinalResults();
        }
    }, 2500);
}

function updfunction updateScores() {
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
            <span class="text-xl font-bold text-green-600">${scores[player]} pts</span>
        `;
        scoreList.appendChild(div);
    });
}

function showFinalResults() {
    const sortedPlayers = [...players].sort((a, b) => scores[b] - scores[a]);
    const winner = sortedPlayers[0];
    
    document.getElementById('questionCard').innerHTML = `
        <div class="text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Partie terminée !</h2>
            <p class="text-xl text-gray-600 mb-4">Gagnant : ${winner} avec ${scores[winner]} points !</p>
            <a href="?" class="inline-block px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-semibold hover:shadow-lg">
                Retour au menu
            </a>
        </div>
    `;
}

// Démarrer le quiz
displayQuestion();
updateScores();
</script>
