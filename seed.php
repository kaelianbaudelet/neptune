<?php

if (!function_exists('db_seed')) {
    function db_seed($pdo) {
        try {
            // Helper for password hashing
            $password = password_hash('password123', PASSWORD_DEFAULT);

            // 1. Equipements
            $equipments = [
                ['Wifi Haute Vitesse', 'wifi'],
                ['Télévision 4K', 'tv'],
                ['Mini-bar', 'refrigerator'],
                ['Climatisation', 'wind'],
                ['Coffre-fort', 'lock'],
                ['Sèche-cheveux', 'scissors'],
                ['Machine à café', 'coffee'],
                ['Peignoirs et chaussons', 'shirt'],
                ['Balcon privé', 'layout'],
                ['Bureau de travail', 'briefcase'],
                ['Insonorisation', 'volume-x'],
                ['Service d\'étage', 'bell']
            ];

            foreach ($equipments as $eq) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Equipment (name, icon) VALUES (?, ?)");
                $stmt->execute($eq);
            }

            // 2. Types de chambres
            $types = ['Standard', 'Deluxe', 'Suite Luxueuse', 'Chambre Familiale', 'Suite Junior', 'Penthouse'];
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
                ['Animaux de compagnie', 'Accueil pour votre fidèle compagnon.', 20.0, 20.0, 0, 0, 1, 1],
                ['Late Check-out', 'Gardez votre chambre jusqu\'à 16h.', 30.0, 20.0, 0, 0, 0, 0],
                ['Transfert Aéroport', 'Navette privée depuis ou vers l\'aéroport.', 50.0, 10.0, 0, 0, 0, 1]
            ];

            foreach ($options as $opt) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Option (name, description, price, tva, per_adult, per_child, per_night, per_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($opt);
            }

            // 4. Utilisateurs
            $users = [
                ['Admin Neptune', 'admin@neptune.fr', 'Admin'],
                ['Kaelian Baudelet', 'kaelian@kaelian.dev', 'Admin'],
                ['Jean Dupont', 'jean.dupont@example.com', 'User'],
                ['Marie Curie', 'marie.curie@science.fr', 'User'],
                ['Sophie Martin', 'sophie.martin@test.com', 'User']
            ];

            foreach ($users as $u) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO User (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$u[0], $u[1], $password, $u[2]]);
            }

            // 5. Employés
            $employees = [
                ['Alice Bernard', 'alice@neptune.fr', '0102030405', 'Réceptionniste', 1800, '1995-05-15'],
                ['Bob Legrand', 'bob@neptune.fr', '0607080910', 'Concierge', 2100, '1988-11-20'],
                ['Claire Petit', 'claire@neptune.fr', '0122334455', 'Femme de chambre', 1650, '1992-03-10'],
                ['David Roux', 'david@neptune.fr', '0655443322', 'Directeur', 4500, '1980-08-25']
            ];
            foreach ($employees as $e) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Employee (name, email, phone, position, salary, date_of_birth) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute($e);
            }

            // 6. Chambres
            $roomCount = $pdo->query("SELECT COUNT(*) FROM Room")->fetchColumn();
            if ($roomCount == 0) {
                // IMPORTANT: Fetch name as key, id as value
                $typeIds = $pdo->query("SELECT name, id FROM Type")->fetchAll(PDO::FETCH_KEY_PAIR);
                
                $rooms = [
                    ['Chambre 101', 'Une chambre confortable pour vos déplacements professionnels.', 1, 1, 2, $typeIds['Standard'], 85.0],
                    ['Chambre 102', 'Idéal pour un couple avec un enfant.', 1, 1, 3, $typeIds['Standard'], 95.0],
                    ['Chambre 103', 'Simple et efficace pour un court séjour.', 0, 1, 2, $typeIds['Standard'], 75.0],
                    ['Chambre Deluxe 201', 'Superbe vue sur la ville et lit King Size.', 0, 1, 2, $typeIds['Deluxe'], 140.0],
                    ['Chambre Deluxe 202', 'Espace et confort avec balcon privé.', 0, 1, 2, $typeIds['Deluxe'], 155.0],
                    ['Chambre Deluxe 203', 'Ambiance feutrée et décoration moderne.', 0, 1, 2, $typeIds['Deluxe'], 150.0],
                    ['Suite Junior 301', 'Un espace de vie spacieux pour se relaxer.', 1, 1, 3, $typeIds['Suite Junior'], 210.0],
                    ['Suite Royale 401', 'Le summum du luxe avec salon séparé et jacuzzi.', 0, 2, 4, $typeIds['Suite Luxueuse'], 450.0],
                    ['Suite Présidentielle 402', 'Notre plus belle suite, élégance et prestige.', 0, 2, 4, $typeIds['Suite Luxueuse'], 550.0],
                    ['Chambre Famille 001', 'Parfait pour les familles nombreuses, accès direct jardin.', 2, 2, 6, $typeIds['Chambre Familiale'], 180.0],
                    ['Chambre Famille 002', 'Espace ludique pour les enfants et confort pour les parents.', 3, 1, 5, $typeIds['Chambre Familiale'], 170.0],
                    ['Le Penthouse', 'Une expérience unique sur le toit de l\'hôtel.', 0, 3, 6, $typeIds['Penthouse'], 1200.0]
                ];

                $eqIds = $pdo->query("SELECT id FROM Equipment")->fetchAll(PDO::FETCH_COLUMN);

                foreach ($rooms as $room) {
                    $stmt = $pdo->prepare("INSERT INTO Room (name, description, bed_single, bed_double, capacity, type_id, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute($room);
                    $roomId = $pdo->lastInsertId();

                    // Associer des équipements au hasard (entre 4 et 8)
                    shuffle($eqIds);
                    $numEq = rand(4, 8);
                    $assignedEq = array_slice($eqIds, 0, $numEq);
                    foreach ($assignedEq as $eqId) {
                        $pdo->prepare("INSERT IGNORE INTO Room_Equipment (room_id, equipment_id) VALUES (?, ?)")->execute([$roomId, $eqId]);
                    }
                }
            }

            // 7. Coupons
            $coupons = [
                ['NEPTUNE20', 20, 10, 100],
                ['WELCOME', 15, 5, 50],
                ['ETE2025', 10, 0, 200]
            ];
            foreach ($coupons as $c) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Coupon (code, discount, used, max_use) VALUES (?, ?, ?, ?)");
                $stmt->execute($c);
            }

            // 8. Avis (Reviews)
            $reviewCount = $pdo->query("SELECT COUNT(*) FROM Review")->fetchColumn();
            if ($reviewCount == 0) {
                $roomIds = $pdo->query("SELECT id FROM Room LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
                $userIds = $pdo->query("SELECT id FROM User WHERE role = 'User' LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
                
                $reviews = [
                    ['Super séjour ! La chambre était magnifique.', 5],
                    ['Très bon accueil, service impeccable.', 4],
                    ['Le spa est incroyable, je recommande.', 5],
                    ['Chambre un peu bruyante mais très propre.', 3],
                    ['Déjeuné varié et délicieux.', 5]
                ];

                foreach ($reviews as $index => $rev) {
                    if (isset($roomIds[$index % count($roomIds)]) && isset($userIds[$index % count($userIds)])) {
                        $stmt = $pdo->prepare("INSERT INTO Review (room_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$roomIds[$index % count($roomIds)], $userIds[$index % count($userIds)], $rev[0], $rev[1]]);
                    }
                }
            }

        } catch (PDOException $e) {
            error_log("Erreur lors du seeding : " . $e->getMessage());
            throw $e;
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
