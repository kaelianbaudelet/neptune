# Makefile pour Hôtel Neptune

# Variables
PHP = php
COMPOSER = composer
PHP_SERVER = $(PHP) -S localhost:8000 -t public
PHPUNIT = ./vendor/bin/phpunit
PHPCS = ./vendor/bin/phpcs
PHPCBF = ./vendor/bin/phpcbf

# Objectif par défaut
.PHONY: help
help:
	@echo "Commandes disponibles:"
	@echo " make help      - Afficher ce message d'aide"
	@echo " make install   - Installer les dépendances"
	@echo " make serve     - Démarrer le serveur de développement"
	@echo " make test      - Exécuter les tests"
	@echo " make lint      - Vérifier le style de code"
	@echo " make fix       - Corriger les problèmes de style de code"
	@echo " make clean     - Supprimer les fichiers générés"

# Installer les dépendances
.PHONY: install
install:
	$(COMPOSER) install

# Démarrer le serveur de développement
.PHONY: serve
serve:
	$(PHP_SERVER)

# Exécuter les tests
.PHONY: test
test:
	$(PHPUNIT)

# Vérifier le style de code
.PHONY: lint
lint:
	$(PHPCS)

# Corriger le style de code
.PHONY: fix
fix:
	$(PHPCBF)

# Nettoyer les fichiers générés
.PHONY: clean
clean:
	rm -rf vendor
	rm -rf composer.lock
	rm -rf logs/*