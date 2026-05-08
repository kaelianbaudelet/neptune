<?php

# Ceci est un script de migration de base de données pour créer les tables nécessaires à l'application.

use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "Migration de la base de données\n";

try {
    // Connexion à la base de données
    echo "Connexion à la base de données...\n";
    $host = $_ENV['DATABASE_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DATABASE_PORT'] ?? '3306';
    $db   = $_ENV['DATABASE_NAME'] ?? 'neptune';
    $user = $_ENV['DATABASE_USER'] ?? 'root';
    $pass = $_ENV['DATABASE_PASSWORD'] ?? '';

    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    // Configuration de la connexion à la base de données
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Récupération du fichier SQL 'database.sql'...\n";
    // Lire le fichier SQL
    $sql = file_get_contents('./database.sql');

    if ($sql === false) {
        die("Error reading SQL file");
    }

    // Execute le script SQL
    echo "Migration de la base de données...\n";
    $conn->exec($sql);
    echo "La migration de la base de données a été effectuée avec succès.\n";
} catch (PDOException $e) {
    echo "La migration de la base de données a échoué.\n";
    echo "Erreur: " . $e->getMessage() . "\n";
}

// Fermeture de la connexion
$conn = null;
