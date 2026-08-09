#!/bin/bash

set -e

echo "🚀 Déploiement CampaignPlanner..."

echo "→ Récupération des modifications"
git pull --ff-only

echo "→ Compilation des assets"
APP_ENV=prod APP_DEBUG=0 php bin/console asset-map:compile

echo "→ Nettoyage du cache"
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

echo "→ Redémarrage des workers Messenger"
APP_ENV=prod APP_DEBUG=0 php bin/console messenger:stop-workers

echo "✅ Déploiement terminé"
