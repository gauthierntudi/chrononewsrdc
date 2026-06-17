#!/usr/bin/env bash
# Copie les assets front legacy dans laravel/public (fichiers réels pour Git / Laravel Cloud).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
LARAVEL_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
MONO_ROOT="$(cd "$LARAVEL_ROOT/.." && pwd)"
PUBLIC="$LARAVEL_ROOT/public"

copy_file() {
  local src="$1"
  local dest="$2"
  if [ -f "$src" ]; then
    mkdir -p "$(dirname "$dest")"
    cp -f "$src" "$dest"
  fi
}

ensure_dir() {
  local path="$1"
  if [ -L "$path" ]; then
    rm -f "$path"
  fi
  mkdir -p "$path"
}

# Monorepo local : sources à la racine CHRONONEWS/
CSS_SRC="$MONO_ROOT/css/styles-home.css"
VIDEO_CSS_SRC="$MONO_ROOT/assets/css/video-section.css"
VIDEO_JS_SRC="$MONO_ROOT/assets/js/video-section.js"
ELEMENTOR_CHUNK_SRC="$MONO_ROOT/js/shared-frontend-handlers.03caa53373b56d3bab67.bundle.min.js"

# Dépôt laravel/ autonome : les fichiers sont déjà sous public/
[ -f "$CSS_SRC" ] || CSS_SRC="$PUBLIC/css/styles-home.css"
[ -f "$VIDEO_CSS_SRC" ] || VIDEO_CSS_SRC="$PUBLIC/assets/css/video-section.css"
[ -f "$VIDEO_JS_SRC" ] || VIDEO_JS_SRC="$PUBLIC/assets/js/video-section.js"
[ -f "$ELEMENTOR_CHUNK_SRC" ] || ELEMENTOR_CHUNK_SRC="$PUBLIC/js/shared-frontend-handlers.03caa53373b56d3bab67.bundle.min.js"

ensure_dir "$PUBLIC/assets/css"
ensure_dir "$PUBLIC/assets/js"
ensure_dir "$PUBLIC/css"
ensure_dir "$PUBLIC/js"
ensure_dir "$PUBLIC/wp-content/plugins/elementor/assets/js"

copy_file "$CSS_SRC" "$PUBLIC/css/styles-home.css"
copy_file "$VIDEO_CSS_SRC" "$PUBLIC/assets/css/video-section.css"
copy_file "$VIDEO_JS_SRC" "$PUBLIC/assets/js/video-section.js"
copy_file "$ELEMENTOR_CHUNK_SRC" "$PUBLIC/wp-content/plugins/elementor/assets/js/shared-frontend-handlers.03caa53373b56d3bab67.bundle.min.js"
copy_file "$ELEMENTOR_CHUNK_SRC" "$PUBLIC/js/shared-frontend-handlers.03caa53373b56d3bab67.bundle.min.js"

echo "Assets front copiés dans laravel/public"
