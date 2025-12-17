<?php
// Drawing Battle - Dessine et devine !
?>

<div class="min-h-screen bg-gradient-to-br from-cyan-400 to-blue-600 p-4">
    <div class="max-w-5xl mx-auto">
        
        <!-- En-tête -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">🎨 Drawing Battle</h2>
                    <p class="text-gray-600">Mot à dessiner: <span id="word" class="font-bold text-blue-600"></span></p>
                </div>
                <div class="flex gap-2">
                    <button id="clearBtn" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        🗑️ Effacer
                    </button>
                    <a href="?" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <canvas id="drawCanvas" width="700" height="500" class="bg-white rounded-2xl shadow-2xl mx-auto block cursor-crosshair mb-4"></canvas>

        <!-- Couleurs -->
        <div class="bg-white rounded-2xl shadow-2xl p-4 mb-4">
            <div class="flex gap-2 justify-center flex-wrap" id="colorPalette"></div>
        </div>

    </div>
</div>

<script>
const canvas = document.getElementById('drawCanvas');
const ctx = canvas.getContext('2d');
const myUsername = <?php echo json_encode($_SESSION['username']); ?>;

const words = ['Maison', 'Chien', 'Soleil', 'Voiture', 'Arbre', 'Chat', 'Fleur', 'Bateau', 'Avion', 'Pizza'];
const currentWord = words[Math.floor(Math.random() * words.length)];
document.getElementById('word').textContent = currentWord;

const colors = ['#000000', '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#FFFFFF'];
let currentColor = '#000000';
let isDrawing = false;
let lastX = 0;
let lastY = 0;

// Palette de couleurs
colors.forEach(color => {
    const btn = document.createElement('button');
    btn.className = 'w-10 h-10 rounded-lg border-2 border-gray-300 hover:scale-110 transition-transform';
    btn.style.backgroundColor = color;
    btn.onclick = () => {
        currentColor = color;
        document.querySelectorAll('#colorPalette button').forEach(b => b.classList.remove('ring-4', 'ring-blue-500'));
        btn.classList.add('ring-4', 'ring-blue-500');
    };
    document.getElementById('colorPalette').appendChild(btn);
});

canvas.addEventListener('mousedown', (e) => {
    isDrawing = true;
    [lastX, lastY] = [e.offsetX, e.offsetY];
});

canvas.addEventListener('mousemove', (e) => {
    if (!isDrawing) return;
    ctx.strokeStyle = currentColor;
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    [lastX, lastY] = [e.offsetX, e.offsetY];
});

canvas.addEventListener('mouseup', () => isDrawing = false);
canvas.addEventListener('mouseout', () => isDrawing = false);

// Touch support
canvas.addEventListener('touchstart', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    isDrawing = true;
    lastX = touch.clientX - rect.left;
    lastY = touch.clientY - rect.top;
});

canvas.addEventListener('touchmove', (e) => {
    e.preventDefault();
    if (!isDrawing) return;
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    const x = touch.clientX - rect.left;
    const y = touch.clientY - rect.top;
    ctx.strokeStyle = currentColor;
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(x, y);
    ctx.stroke();
    lastX = x;
    lastY = y;
});

canvas.addEventListener('touchend', () => isDrawing = false);

document.getElementById('clearBtn').onclick = () => {
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
};

// Initialiser avec fond blanc
ctx.fillStyle = '#FFFFFF';
ctx.fillRect(0, 0, canvas.width, canvas.height);
</script>
