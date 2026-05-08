#!/bin/sh
set -e

# Attendre que la base de données soit prête
echo "Attente de la base de données ($DATABASE_HOST:$DATABASE_PORT)..."
max_tries=30
count=0
until nc -z "$DATABASE_HOST" "$DATABASE_PORT" || [ $count -eq $max_tries ]; do
    sleep 1
    count=$((count + 1))
done

if [ $count -eq $max_tries ]; then
    echo "Erreur : La base de données n'est pas accessible après $max_tries secondes."
else
    echo "Base de données prête !"
fi

# Exécuter les migrations
echo "Exécution des migrations..."
php migration.php

# Exécuter le seeding
echo "Exécution du seeding..."
php seed.php

# Lancer Apache en arrière-plan
echo "Lancement d'Apache..."
exec apache2-foreground
