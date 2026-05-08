<?php

if (!function_exists('db_seed')) {
    function db_seed($pdo) {
        try {
            // 1. Equipements
            $equipments = [
                ['Wifi Haute Vitesse', 'wifi'],
                ['Télévision 4K', 'tv'],
                ['Mini-bar', 'refrigerator'],
                ['Climatisation', 'wind'],
                ['Coffre-fort', 'lock'],
                ['Sèche-cheveux', 'scissors']
            ];

            foreach ($equipments as $eq) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Equipment (name, icon) VALUES (?, ?)");
                $stmt->execute($eq);
            }

            // 2. Types de chambres
            $types = ['Standard', 'Deluxe', 'Suite Luxueuse', 'Chambre Familiale'];
            foreach ($types as $type) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Type (name) VALUES (?)");
                $stmt->execute([$type]);
            }

            // 3. Options
            $options = [
                ['Petit-déjeuner Buffet', 'Un délicieux petit-déjeuner complet chaque matin.', 15.0, 10.0, 1, 1, 1, 0],
                ['Parking Sécurisé', 'Place de parking dans notre garage souterrain.', 10.0, 20.0, 0, 0, 1, 0],
                ['Accès Spa & Bien-être', 'Accès illimité à notre espace détente.', 25.0, 20.0, 1, 0, 1, 0],
                ['Accueil Romantique', 'Bouteille de champagne et pétales de roses à l\'arrivée.', 45.0, 20.0, 0, 0, 0, 0],
                ['Animaux de compagnie', 'Accueil pour votre fidèle compagnon.', 20.0, 20.0, 0, 0, 1, 1]
            ];

            foreach ($options as $opt) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Option (name, description, price, tva, per_adult, per_child, per_night, per_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($opt);
            }

            // 4. Chambres (si aucune n'existe)
            $roomCount = $pdo->query("SELECT COUNT(*) FROM Room")->fetchColumn();
            if ($roomCount == 0) {
                $typeIds = $pdo->query("SELECT id, name FROM Type")->fetchAll(PDO::FETCH_KEY_PAIR);
                $rooms = [
                    ['Chambre 101', 'Une chambre confortable pour vos déplacements professionnels.', 1, 1, 2, $typeIds['Standard'], 85.0],
                    ['Chambre 102', 'Idéal pour un couple avec un enfant.', 1, 1, 3, $typeIds['Standard'], 95.0],
                    ['Chambre Deluxe 201', 'Superbe vue sur la ville et lit King Size.', 0, 1, 2, $typeIds['Deluxe'], 140.0],
                    ['Chambre Deluxe 202', 'Espace et confort avec balcon privé.', 0, 1, 2, $typeIds['Deluxe'], 155.0],
                    ['Suite Royale 301', 'Le summum du luxe avec salon séparé et jacuzzi.', 0, 2, 4, $typeIds['Suite Luxueuse'], 350.0],
                    ['Chambre Famille 001', 'Parfait pour les familles nombreuses, accès direct jardin.', 2, 2, 6, $typeIds['Chambre Familiale'], 180.0]
                ];

                foreach ($rooms as $room) {
                    $stmt = $pdo->prepare("INSERT INTO Room (name, description, bed_single, bed_double, capacity, type_id, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute($room);
                    $roomId = $pdo->lastInsertId();

                    // Associer quelques équipements au hasard
                    $eqIds = $pdo->query("SELECT id FROM Equipment ORDER BY RAND() LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($eqIds as $eqId) {
                        $pdo->prepare("INSERT IGNORE INTO Room_Equipment (room_id, equipment_id) VALUES (?, ?)")->execute([$roomId, $eqId]);
                    }
                }
            }

        } catch (PDOException $e) {
            error_log("Erreur lors du seeding : " . $e->getMessage());
        }
    }
}

// Si lancé directement depuis la CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    require 'vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $host = $_ENV['DATABASE_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DATABASE_PORT'] ?? '3306';
    $db   = $_ENV['DATABASE_NAME'] ?? 'neptune';
    $user = $_ENV['DATABASE_USER'] ?? 'root';
    $pass = $_ENV['DATABASE_PASSWORD'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        db_seed($pdo);
        echo "Seeding terminé avec succès !\n";
    } catch (PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage() . "\n";
    }
}
