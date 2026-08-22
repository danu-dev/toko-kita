#!/bin/bash

# ==============================================================================
# TokoKita Local Dev Loop Script
# Auto-runs Laravel Server, Vite Asset Compiler, Queue/Scheduler, and Auto-Tests
# ==============================================================================

set -e

GREEN='\033[0;32m'
TEAL='\033[0;36m'
AMBER='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}====================================================${NC}"
echo -e "${TEAL}        🏪 TOKOKITA. AUTO-WORK & DEV LOOP           ${NC}"
echo -e "${GREEN}====================================================${NC}"

# Function to clean up background processes on exit
cleanup() {
    echo -e "\n${AMBER}Menghentikan dev loop processes...${NC}"
    kill $(jobs -p) 2>/dev/null || true
    exit 0
}
trap cleanup SIGINT SIGTERM EXIT

# 1. Run Migrations & Pre-checks
echo -e "${TEAL}[1/4] Menyiapkan database & storage...${NC}"
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --graceful > /dev/null 2>&1 || true

# 2. Run Test Suite
echo -e "${TEAL}[2/4] Menjalankan automated test suite...${NC}"
if php artisan test --stop-on-failure; then
    echo -e "${GREEN}✓ Seluruh automated test suite lolos!${NC}"
else
    echo -e "${RED}✗ Ada kegagalan test. Periksa kode Anda.${NC}"
fi

# 3. Start Background Hot-Compiler & Server
echo -e "${TEAL}[3/4] Menjalankan Vite hot compiler & Laravel server...${NC}"
npm run dev &
VITE_PID=$!

php artisan serve --port=8000 &
SERVER_PID=$!

echo -e "${GREEN}====================================================${NC}"
echo -e "${GREEN}✓ TokoKita Local Dev Loop Aktif:${NC}"
echo -e "  🌐 Local URL:  ${AMBER}http://localhost:8000${NC}"
echo -e "  🚀 Live Vercel: ${AMBER}https://toko-kita-phi.vercel.app${NC}"
echo -e "  🔄 GitHub Repo: ${AMBER}https://github.com/danu-dev/toko-kita${NC}"
echo -e "${GREEN}====================================================${NC}"
echo -e "Tekan [Ctrl+C] untuk keluar dari loop kapan saja.\n"

# Wait for background processes
wait
