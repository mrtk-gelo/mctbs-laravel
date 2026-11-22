#!/bin/sh
set -eu

APP_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
PUBLIC_STORAGE="$APP_ROOT/public/storage"
PUBLIC_STORAGE_TARGET="$APP_ROOT/storage/app/public"

cd "$APP_ROOT"

mkdir -p "$PUBLIC_STORAGE_TARGET/houses" "$PUBLIC_STORAGE_TARGET/units"

if [ -L "$PUBLIC_STORAGE" ]; then
    LINK_TARGET=$(readlink -f "$PUBLIC_STORAGE" 2>/dev/null || true)
    STORAGE_TARGET=$(readlink -f "$PUBLIC_STORAGE_TARGET")

    if [ "$LINK_TARGET" != "$STORAGE_TARGET" ]; then
        rm -- "$PUBLIC_STORAGE"
    fi
elif [ -e "$PUBLIC_STORAGE" ]; then
    printf 'Refusing to replace existing non-symlink path: %s\n' "$PUBLIC_STORAGE" >&2
    exit 1
fi

if [ ! -L "$PUBLIC_STORAGE" ]; then
    ln -s "$PUBLIC_STORAGE_TARGET" "$PUBLIC_STORAGE"
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
