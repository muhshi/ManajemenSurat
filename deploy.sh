#!/usr/bin/env bash
# ==============================================================================
# Deployment Script - Manajemen Surat BPS Kabupaten Demak
# Fitur:
#  - Otomatis deteksi apakah image Docker perlu di-build ulang atau tidak
#  - Menjalankan migrasi, optimasi cache, dan restart queue-worker
# ==============================================================================

set -e

# Warna output terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

CONTAINER_APP="surat-franken"
QUEUE_SERVICE="queue-worker"
FORCE_BUILD=false
SKIP_PULL=false

# Parsing argumen baris perintah
for arg in "$@"; do
    case $arg in
        --build|-b)
            FORCE_BUILD=true
            shift
            ;;
        --skip-pull)
            SKIP_PULL=true
            shift
            ;;
        --help|-h)
            echo "Penggunaan: ./deploy.sh [OPSI]"
            echo ""
            echo "Opsi:"
            echo "  -b, --build       Paksa build ulang Docker image"
            echo "  --skip-pull       Lewati 'git pull' (deploy perubahan lokal)"
            echo "  -h, --help        Tampilkan bantuan ini"
            exit 0
            ;;
        *)
            ;;
    esac
done

echo -e "${BLUE}=== [1/5] Memulai Proses Deployment ===${NC}"

# 1. Cek ketersediaan Docker Compose
if docker compose version >/dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
    DOCKER_COMPOSE="docker-compose"
else
    echo -e "${RED}[ERROR] docker compose tidak ditemukan di sistem!${NC}"
    exit 1
fi

# 2. Sinkronisasi Git
NEED_BUILD=false

if [ "$SKIP_PULL" = true ]; then
    echo -e "${YELLOW}[INFO] Melewati git pull (--skip-pull aktif)...${NC}"
else
    echo -e "${BLUE}=== [2/5] Memeriksa & Mengambil Pembaruan Git ===${NC}"
    
    # Ambil commit hash sebelum pull
    PREV_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "")
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")

    echo "Mengambil perubahan dari origin/$CURRENT_BRANCH..."
    git pull origin "$CURRENT_BRANCH"

    NEW_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "")

    if [ -n "$PREV_COMMIT" ] && [ -n "$NEW_COMMIT" ] && [ "$PREV_COMMIT" != "$NEW_COMMIT" ]; then
        echo -e "${GREEN}[OK] Kode berhasil diperbarui dari commit ${PREV_COMMIT:0:7} ke ${NEW_COMMIT:0:7}.${NC}"

        # Periksa apakah ada file konfigurasi, aset, atau dependensi yang berubah
        CHANGED_FILES=$(git diff --name-only "$PREV_COMMIT" "$NEW_COMMIT")
        BUILD_TRIGGERS="^(Dockerfile|docker-compose\.ya?ml|Caddyfile|package\.json|package-lock\.json|composer\.json|composer\.lock|vite\.config\.js|resources/css/|resources/js/)"

        if echo "$CHANGED_FILES" | grep -Eq "$BUILD_TRIGGERS"; then
            echo -e "${YELLOW}[BUILD TRIGGER] Terdeteksi perubahan pada dependensi/asset/konfigurasi Docker:${NC}"
            echo "$CHANGED_FILES" | grep -E "$BUILD_TRIGGERS" | sed 's/^/  - /'
            NEED_BUILD=true
        else
            echo -e "${GREEN}[INFO] Tidak ada perubahan pada Dockerfile/Composer/NPM/Assets.${NC}"
        fi
    else
        echo -e "${GREEN}[INFO] Branch sudah up-to-date.${NC}"
    fi
fi

# Cek apakah container sedang berjalan atau image belum ada
RUNNING_CONTAINER=$($DOCKER_COMPOSE ps --filter "status=running" -q "$CONTAINER_APP" 2>/dev/null || true)
if [ -z "$RUNNING_CONTAINER" ]; then
    echo -e "${YELLOW}[INFO] Container '$CONTAINER_APP' belum berjalan. Memerlukan build awal/start.${NC}"
    NEED_BUILD=true
fi

if [ "$FORCE_BUILD" = true ]; then
    echo -e "${YELLOW}[INFO] Opsi --build aktif. Memaksa build ulang Docker image...${NC}"
    NEED_BUILD=true
fi

# 3. Build & Jalankan Container
echo -e "${BLUE}=== [3/5] Mengatur Container Docker ===${NC}"
if [ "$NEED_BUILD" = true ]; then
    echo -e "${YELLOW}Menjalankan: $DOCKER_COMPOSE up -d --build...${NC}"
    $DOCKER_COMPOSE up -d --build
else
    echo -e "${GREEN}Hanya perubahan logic/views PHP. Melewati proses build Docker image.${NC}"
    $DOCKER_COMPOSE up -d
fi

# 4. Pembersihan Cache & Migrasi Database
echo -e "${BLUE}=== [4/5] Menjalankan Optimasi & Migrasi Database ===${NC}"

echo "Membersihkan dan me-refresh cache Laravel..."
$DOCKER_COMPOSE exec -T "$CONTAINER_APP" php artisan optimize:clear

echo "Menjalankan migrasi database..."
$DOCKER_COMPOSE exec -T "$CONTAINER_APP" php artisan migrate --force

echo "Meng-cache konfigurasi, route, dan view untuk performa maksimal..."
$DOCKER_COMPOSE exec -T "$CONTAINER_APP" php artisan config:cache
$DOCKER_COMPOSE exec -T "$CONTAINER_APP" php artisan route:cache
$DOCKER_COMPOSE exec -T "$CONTAINER_APP" php artisan view:cache

# Restart queue-worker agar memuat kode PHP terbaru
if $DOCKER_COMPOSE ps -q "$QUEUE_SERVICE" >/dev/null 2>&1; then
    echo "Me-restart background worker ($QUEUE_SERVICE)..."
    $DOCKER_COMPOSE restart "$QUEUE_SERVICE"
fi

# 5. Memastikan Permission Storage & Bootstrap Cache
echo -e "${BLUE}=== [5/5] Memperbarui Permission Direktori ===${NC}"
$DOCKER_COMPOSE exec -T "$CONTAINER_APP" chown -R www-data:www-data storage bootstrap/cache
$DOCKER_COMPOSE exec -T "$CONTAINER_APP" chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}====================================================${NC}"
echo -e "${GREEN}✓ Deployment Selesai Berhasil!${NC}"
echo -e "${GREEN}====================================================${NC}"
