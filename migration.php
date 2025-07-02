<?php

# Ceci est un script de migration de base de données pour créer les tables nécessaires à l'application.

use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Migration de la base de données\n";

try {
    // Connexion à la base de données
    echo "Connexion à la base de données...\n";
    $conn = new PDO('mysql:host=' . '127.0.0.1' . ';port=' . $_ENV['DATABASE_PORT'] . ';dbname=' .
        $_ENV['DATABASE_NAME'] . ';charset=utf8', $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD']);
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
