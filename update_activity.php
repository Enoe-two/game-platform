<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not connected']);
    exit;
}

// Fichier pour stocker les joueurs
$playersFile = 'players.json';

// Mettre à jour l'activité du joueur
$data = [];
if (file_exists($playersFile)) {
    $data = json_decode(file_get_contents($playersFile), true) ?: [];
}

$data[$_SESSION['username']] = ['last_activity' => time()];
file_put_contents($playersFile, json_encode($data));

echo json_encode(['success' => true]);
?>
