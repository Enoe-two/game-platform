<?php
session_start();

// Fichier pour stocker les joueurs connectés (remplace la base de données)
$playersFile = 'players.json';

// Fonction pour lire les joueurs
function getPlayers() {
    global $playersFile;
    if (file_exists($playersFile)) {
        $data = json_decode(file_get_contents($playersFile), true);
        // Nettoyer les joueurs inactifs (plus de 5 minutes)
        $now = time();
        $data = array_filter($data, function($player) use ($now) {
            return ($now - $player['last_activity']) < 300; // 5 minutes
        });
        file_put_contents($playersFile, json_encode($data));
        return array_keys($data);
    }
    return [];
}

// Fonction pour ajouter/mettre à jour un joueur
function updatePlayer($username) {
    global $playersFile;
    $data = [];
    if (file_exists($playersFile)) {
        $data = json_decode(file_get_contents($playersFile), true) ?: [];
    }
    $data[$username] = ['last_activity' => time()];
    file_put_contents($playersFile, json_encode($data));
}

// Fonction pour supprimer un joueur
function removePlayer($username) {
    global $playersFile;
    if (file_exists($playersFile)) {
        $data = json_decode(file_get_contents($playersFile), true) ?: [];
        unset($data[$username]);
        file_put_contents($playersFile, json_encode($data));
    }
}

// Gestion de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connect'])) {
    $_SESSION['username'] = htmlspecialchars($_POST['username']);
    $_SESSION['connected_at'] = time();
    updatePlayer($_SESSION['username']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    if (isset($_SESSION['username'])) {
        removePlayer($_SESSION['username']);
    }
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Mettre à jour l'activité du joueur connecté
if (isset($_SESSION['username'])) {
    updatePlayer($_SESSION['username']);
}

// Récupérer les joueurs en ligne
$players = getPlayers();

// Gestion du jeu sélectionné
$currentGame = isset($_GET['game']) ? $_GET['game'] : null;

// Liste des jeux disponibles
$games = [
    'tag' => ['name' => 'Loup Touche-Touche', 'icon' => '🎯', 'min' => 2, 'max' => 10, 'color' => 'orange'],
    'werewolf' => ['name' => 'Loup-Garou', 'icon' => '🌙', 'min' => 4, 'max' => 10, 'color' => 'purple'],
    'racing' => ['name' => 'Course de Voitures', 'icon' => '🏎️', 'min' => 2, 'max' => 8, 'color' => 'blue'],
    'quiz' => ['name' => 'Quiz Party', 'icon' => '🏆', 'min' => 2, 'max' => 10, 'color' => 'green'],
    'reaction' => ['name' => 'Jeu de Réflexes', 'icon' => '⚡', 'min' => 2, 'max' => 6, 'color' => 'yellow'],
    'snake' => ['name' => 'Snake Battle', 'icon' => '🐍', 'min' => 2, 'max' => 6, 'color' => 'lime'],
    'memory' => ['name' => 'Memory', 'icon' => '🧠', 'min' => 2, 'max' => 4, 'color' => 'pink'],
    'bomber' => ['name' => 'Bomber Battle', 'icon' => '💣', 'min' => 2, 'max' => 4, 'color' => 'gray'],
    'draw' => ['name' => 'Drawing Battle', 'icon' => '🎨', 'min' => 1, 'max' => 8, 'color' => 'cyan'],
    'pong' => ['name' => 'Pong Battle', 'icon' => '🏓', 'min' => 1, 'max' => 2, 'color' => 'indigo'],
    'battleroyale' => ['name' => 'Battle Royale', 'icon' => '⚔️', 'min' => 2, 'max' => 10, 'color' => 'purple'],
    'battlefront' => ['name' => 'Battlefront', 'icon' => '🌍', 'min' => 2, 'max' => 6, 'color' => 'slate'],
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Hub - Plateforme Multijoueur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 min-h-screen">

<?php if (!isset($_SESSION['username'])): ?>
    <!-- Page de connexion -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🎮</div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Game Hub</h1>
                <p class="text-gray-600">Plateforme de jeux multijoueur</p>
            </div>
            <form method="POST">
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Choisis ton pseudo" 
                    required 
                    maxlength="20"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:outline-none mb-4 text-lg"
                >
                <button 
                    type="submit" 
                    name="connect"
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 rounded-lg font-semibold text-lg hover:shadow-lg transition-all"
                >
                    Se connecter
                </button>
            </form>
        </div>
    </div>

<?php elseif ($currentGame && isset($games[$currentGame])): ?>
    <!-- Page de jeu -->
    <?php include "games/{$currentGame}.php"; ?>

<?php else: ?>
    <!-- Page d'accueil avec liste des jeux -->
    <div class="p-4">
        <div class="max-w-6xl mx-auto">
            
            <!-- En-tête utilisateur -->
            <div class="bg-white rounded-2xl shadow-2xl p-6 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            <?php echo strtoupper($_SESSION['username'][0]); ?>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                            <div class="flex items-center gap-2 text-green-600">
                                <div class="w-2 h-2 bg-green-600 rounded-full animate-pulse"></div>
                                <span class="text-sm">En ligne</span>
                            </div>
                        </div>
                    </div>
                    <a 
                        href="?logout" 
                        class="flex items-center gap-2 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                    >
                        🚪 Déconnexion
                    </a>
                </div>
            </div>

            <!-- Joueurs connectés -->
            <div class="bg-white rounded-2xl shadow-2xl p-6 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">👥</span>
                    <h3 class="text-xl font-bold text-gray-800">Joueurs connectés (<?php echo count($players); ?>)</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php if (count($players) > 0): ?>
                        <?php foreach ($players as $player): ?>
                            <div class="px-4 py-2 bg-purple-100 text-purple-800 rounded-full font-medium">
                                <?php echo htmlspecialchars($player); ?>
                                <?php if ($player === $_SESSION['username']): ?>
                                    <span class="text-xs">(Toi)</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500">Aucun autre joueur connecté pour le moment</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Liste des jeux -->
            <div class="bg-white rounded-2xl shadow-2xl p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Choisis ton jeu</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($games as $id => $game): ?>
                        <a 
                            href="?game=<?php echo $id; ?>"
                            class="group relative overflow-hidden rounded-xl p-6 bg-gradient-to-br from-gray-50 to-gray-100 hover:shadow-xl transition-all transform hover:scale-105"
                        >
                            <div class="absolute top-0 right-0 w-32 h-32 bg-<?php echo $game['color']; ?>-500 opacity-10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform"></div>
                            <div class="text-5xl mb-4"><?php echo $game['icon']; ?></div>
                            <h4 class="text-lg font-bold text-gray-800 mb-2"><?php echo $game['name']; ?></h4>
                            <p class="text-sm text-gray-600">
                                <?php echo $game['min']; ?>-<?php echo $game['max']; ?> joueurs
                            </p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Script pour actualiser automatiquement -->
    <script>
        // Actualiser la liste des joueurs toutes les 5 secondes
        setInterval(() => {
            fetch('update_activity.php')
                .then(() => {
                    location.reload();
                })
                .catch(console.error);
        }, 5000);
    </script>
<?php endif; ?>

</body>
</html>
