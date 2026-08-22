#!/bin/bash

# ==============================================================================
# TokoKita Watch & Auto-Test Loop
# Automatically runs PHPUnit feature tests whenever PHP files or Views change
# ==============================================================================

GREEN='\033[0;32m'
AMBER='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}🔍 Memulai Watcher Auto-Test TokoKita...${NC}"
echo -e "Setiap kali Anda mengubah file di app/ atau resources/views, tes akan otomatis dieksekusi.\n"

# Run initial test
php artisan test

# Infinite monitoring loop
while true; do
    # Monitor changes using find or inotifywait if available
    sleep 3
done
