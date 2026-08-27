#!/usr/bin/env bash

set -Eeuo pipefail

# ============================================================
# Configurazione
# ============================================================

PHP_SERVICE="php"

# ============================================================
# Funzioni
# ============================================================

step() {
    echo
    echo "============================================================"
    echo ">>> $1"
    echo "============================================================"
}

error_handler() {
    echo
    echo "❌ ERRORE: lo script è stato interrotto."
    echo "   Comando: ${BASH_COMMAND}"
    echo "   Riga: ${BASH_LINENO[0]}"
    exit 1
}

trap error_handler ERR

# ============================================================
# 1. Git pull
# ============================================================

step "1/5 - Aggiornamento repository Git"

echo "Verifico eventuali modifiche locali..."

if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "⚠️  Sono presenti modifiche locali."
    echo "Eseguo git stash..."

    git stash push -u -m "auto-stash-before-deploy-$(date '+%Y-%m-%d_%H-%M-%S')"

    echo "✓ Modifiche locali messe in stash."
fi

echo "Eseguo git pull..."

git pull

echo "✓ Git pull completato con successo."

# ============================================================
# 2. Stop Docker
# ============================================================

step "2/5 - Arresto ambiente Docker"

docker compose down

echo "✓ Docker arrestato."

# ============================================================
# 3. Ricostruzione servizio PHP
# ============================================================

step "3/5 - Ricostruzione servizio PHP production"

docker compose -f compose.yaml -f compose.prod.yaml build "$PHP_SERVICE"

echo "✓ Servizio PHP ricostruito."

# ============================================================
# 4. Avvio ambiente Docker
# ============================================================

step "4/5 - Avvio ambiente Docker"

# Configurazione delle variabili d'ambiente
export SERVER_NAME=""
export APP_SECRET=""
export CADDY_MERCURE_JWT_SECRET=""

# Stampa un messaggio di avvio
echo "Avvio di Docker Compose in modalità di produzione per ${SERVER_NAME}..."

# Esecuzione del comando Docker Compose
docker compose -f compose.yaml -f compose.prod.yaml up --wait

# Verifica se il comando è andato a buon fine
if [ $? -eq 0 ]; then
    echo "🚀 I servizi sono stati avviati correttamente e sono pronti!"
else
    echo "❌ Si è verificato un errore durante l'avvio dei servizi."
    exit 1
fi

echo "✓ Ambiente Docker avviato."

# ============================================================
# 5. Compilazione asset
# ============================================================

step "5/5 - Compilazione asset Symfony"

docker compose run "$PHP_SERVICE" php bin/console asset-map:compile

echo "✓ Asset compilati."

# ============================================================
# Fine
# ============================================================

echo
echo "============================================================"
echo "✅ DEPLOY COMPLETATO CON SUCCESSO"
echo "============================================================"

