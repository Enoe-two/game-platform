<?php
session_start();

if (!isset($_SESSION['username'])) {
    exit;
}

$host = 'mysql.railway.internal';
$dbname = 'railway';
$username = 'root';
$password = 'nCZekprwbyHSWZHlRpylceqIWVAzdUAf';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mettre à jour l'activité du joueur
    $stmt = $pdo->prepare("UPDATE players SET last_activity = NOW() WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
