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
                ['Service d\'étage', 'bell'],
                ['Vue sur Mer', 'anchor'],
                ['Smart TV', 'monitor'],
                ['Douche Italienne', 'droplet'],
                ['Baignoire Balnéo', 'heart'],
                ['Enceinte Bluetooth', 'speaker']
            ];

            foreach ($equipments as $eq) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Equipment (name, icon) VALUES (?, ?)");
                $stmt->execute($eq);
            }

            // 2. Types de chambres
            $types = ['Standard', 'Deluxe', 'Suite Luxueuse', 'Chambre Familiale', 'Suite Junior', 'Penthouse', 'Suite Executive'];
            foreach ($types as $type) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Type (name) VALUES (?)");
                $stmt->execute([$type]);
            }

            // 3. Options
            $optionsData = [
                ['Petit-déjeuner Buffet', 'Un délicieux petit-déjeuner complet chaque matin avec des produits locaux.', 15.0, 10.0, 1, 1, 1, 0, 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=600&q=80'],
                ['Parking Sécurisé', 'Place de parking dans notre garage souterrain privé et surveillé 24h/24.', 12.0, 20.0, 0, 0, 1, 0, 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?w=600&q=80'],
                ['Accès Spa & Bien-être', 'Accès illimité à notre espace détente : Sauna, Hammam, et Piscine chauffée.', 25.0, 20.0, 1, 0, 1, 0, 'csjjk2u8kktgzwqacmsp.jpg'], 
                ['Accueil Romantique', 'Bouteille de champagne, pétales de roses et chocolats fins à l\'arrivée.', 45.0, 20.0, 0, 0, 0, 0, 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=600&q=80'],
                ['Animaux de compagnie', 'Accueil VIP pour votre compagnon (panier, gamelle et friandises).', 24.0, 20.0, 0, 0, 1, 1, 'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?w=600&q=80'], 
                ['Late Check-out', 'Profitez de votre chambre plus longtemps, jusqu\'à 16h.', 36.0, 20.0, 0, 0, 0, 0, 'https://images.unsplash.com/photo-1508962914676-134849a727f0?w=600&q=80'],
                ['Transfert Aéroport', 'Navette privée de luxe depuis ou vers l\'aéroport.', 55.0, 10.0, 0, 0, 0, 1, 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&q=80'],
                ['Bouteille de Vin', 'Une sélection de nos meilleurs crus locaux directement en chambre.', 30.0, 20.0, 0, 0, 0, 1, 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=600&q=80']
            ];

            $uploadDir = __DIR__ . '/public/assets/upload/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($optionsData as $opt) {
                $name = $opt[0];
                $urlOrFile = $opt[8];
                $fileKey = '';
                $extension = 'jpg';

                if (strpos($urlOrFile, 'http') === 0) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $name)));
                    $fileKey = 'option_' . $slug;
                    $fileName = $fileKey . '.jpg';
                    $destPath = $uploadDir . $fileName;

                    if (!file_exists($destPath)) {
                        $imgContent = @file_get_contents($urlOrFile);
                        if ($imgContent) {
                            file_put_contents($destPath, $imgContent);
                        }
                    }
                } else {
                    $fileKey = pathinfo($urlOrFile, PATHINFO_FILENAME);
                    $extension = pathinfo($urlOrFile, PATHINFO_EXTENSION);
                }

                // Nettoyage des anciennes clés problématiques
                $pdo->exec("DELETE FROM Image WHERE file_key LIKE 'option_accès_spa%'");

                $stmt = $pdo->prepare("INSERT IGNORE INTO Image (file_key, name, extension) VALUES (?, ?, ?)");
                $stmt->execute([$fileKey, $name, $extension]);
                
                $imageId = $pdo->query("SELECT id FROM Image WHERE file_key = '$fileKey'")->fetchColumn();

                $stmt = $pdo->prepare("INSERT IGNORE INTO Option (name, description, price, tva, per_adult, per_child, per_night, per_quantity, image_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$opt[0], $opt[1], $opt[2], $opt[3], $opt[4], $opt[5], $opt[6], $opt[7], $imageId]);
            }

            // 4. Utilisateurs (Plus de comptes)
            $users = [
                ['Admin Neptune', 'admin@neptune.fr', 'Admin'],
                ['Kaelian Baudelet', 'kaelian@kaelian.dev', 'Admin'],
                ['Jean Dupont', 'jean.dupont@example.com', 'User'],
                ['Marie Curie', 'marie.curie@science.fr', 'User'],
                ['Sophie Martin', 'sophie.martin@test.com', 'User'],
                ['Thomas Durand', 'thomas.durand@mail.com', 'User'],
                ['Julie Lefebvre', 'julie.l@provider.net', 'User'],
                ['Nicolas Petit', 'nico.petit@work.com', 'User']
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
                ['David Roux', 'david@neptune.fr', '0655443322', 'Directeur', 4500, '1980-08-25'],
                ['Emma Leroy', 'emma@neptune.fr', '0788990011', 'Chef de cuisine', 3200, '1985-06-12'],
                ['Fabien Morel', 'fabien@neptune.fr', '0611223344', 'Serveur', 1700, '1998-02-28']
            ];
            foreach ($employees as $e) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Employee (name, email, phone, position, salary, date_of_birth) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute($e);
            }

            // 6. Chambres (Plus de chambres)
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
                ['Le Penthouse', 'Une expérience unique sur le toit de l\'hôtel.', 0, 3, 6, $typeIds['Penthouse'], 1200.0],
                ['Suite Executive 302', 'Parfait pour allier travail et détente.', 0, 1, 2, $typeIds['Suite Executive'], 280.0],
                ['Suite Executive 303', 'Vue panoramique et équipements haute technologie.', 0, 1, 2, $typeIds['Suite Executive'], 300.0]
            ];

            $eqIds = $pdo->query("SELECT id FROM Equipment")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($rooms as $room) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Room (name, description, bed_single, bed_double, capacity, type_id, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($room);
                
                $roomIdQuery = $pdo->prepare("SELECT id FROM Room WHERE name = ?");
                $roomIdQuery->execute([$room[0]]);
                $roomId = $roomIdQuery->fetchColumn();

                if ($roomId) {
                    shuffle($eqIds);
                    $numEq = rand(6, 12);
                    $assignedEq = array_slice($eqIds, 0, $numEq);
                    foreach ($assignedEq as $eqId) {
                        $pdo->prepare("INSERT IGNORE INTO Room_Equipment (room_id, equipment_id) VALUES (?, ?)")->execute([$roomId, $eqId]);
                    }
                }
            }

            // 7. Images (Arrizul)
            $imageNames = [
                ['arrizul_congress_gallery_suite_011-580x380', 'Suite Gallery 1', 'jpg'],
                ['arrizul_congress_gallery_suite_021-580x380', 'Suite Gallery 2', 'jpg'],
                ['arrizul_hotel_congress_gallery_suite_01-580x380', 'Hôtel Congress Suite 1', 'jpg'],
                ['arrizul_hotel_congress_gallery_suite_02-580x380', 'Hôtel Congress Suite 2', 'jpg'],
                ['arrizul_hotel_congress_gallery_suite_03-580x380', 'Hôtel Congress Suite 3', 'jpg']
            ];

            foreach ($imageNames as $img) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Image (file_key, name, extension) VALUES (?, ?, ?)");
                $stmt->execute($img);
            }

            // 8. Associer les 5 images à TOUTES les chambres
            $roomIds = $pdo->query("SELECT id FROM Room")->fetchAll(PDO::FETCH_COLUMN);
            $imageIds = $pdo->query("SELECT id FROM Image WHERE file_key LIKE 'arrizul%'")->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($roomIds) && !empty($imageIds)) {
                foreach ($roomIds as $roomId) {
                    foreach ($imageIds as $imageId) {
                        $pdo->prepare("INSERT IGNORE INTO Room_Image (room_id, image_id) VALUES (?, ?)")->execute([$roomId, $imageId]);
                    }
                }
            }

            // 9. Coupons
            $coupons = [
                ['NEPTUNE20', 20, 10, 100],
                ['WELCOME', 15, 5, 50],
                ['ETE2025', 10, 0, 200],
                ['PROMO10', 10, 2, 500],
                ['VIP50', 50, 1, 5]
            ];
            foreach ($coupons as $c) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO Coupon (code, discount, used, max_use) VALUES (?, ?, ?, ?)");
                $stmt->execute($c);
            }

            // 10. Avis (Reviews) - Plus d'avis
            $roomIdsForReviews = $pdo->query("SELECT id FROM Room")->fetchAll(PDO::FETCH_COLUMN);
            $userIdsForReviews = $pdo->query("SELECT id FROM User WHERE role = 'User'")->fetchAll(PDO::FETCH_COLUMN);
            
            $reviews = [
                ['Super séjour ! La chambre était magnifique et très propre.', 5],
                ['Très bon accueil, service impeccable. On reviendra !', 4],
                ['Le spa est incroyable, je recommande vivement l\'expérience.', 5],
                ['Chambre un peu bruyante le matin mais literie excellente.', 3],
                ['Petit déjeuner varié et délicieux, produits de qualité.', 5],
                ['Le personnel est aux petits soins, bravo à l\'équipe.', 5],
                ['Une vue imprenable sur la ville depuis le Penthouse.', 5],
                ['Parfait pour un week-end en amoureux.', 4],
                ['Hôtel très bien situé, calme et élégant.', 4],
                ['L\'option romantique est un vrai plus, très réussi.', 5]
            ];

            foreach ($reviews as $index => $rev) {
                $rid = $roomIdsForReviews[array_rand($roomIdsForReviews)];
                $uid = $userIdsForReviews[array_rand($userIdsForReviews)];
                $stmt = $pdo->prepare("INSERT INTO Review (room_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
                $stmt->execute([$rid, $uid, $rev[0], $rev[1]]);
            }

        } catch (PDOException $e) {
            error_log("Erreur lors du seeding : " . $e->getMessage());
            throw $e;
        }
    }
}

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
