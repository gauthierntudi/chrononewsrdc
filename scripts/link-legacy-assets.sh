#!/usr/bin/env bash
# Lie les assets legacy (racine repo) dans laravel/public pour le front Bopea.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PUBLIC="$ROOT/laravel/public"

cd "$PUBLIC"

for target in img wp-content wp-includes publication; do
  if [ -e "$ROOT/$target" ]; then
    ln -sfn "../../$target" "$target"
  fi
done

if [ -d "$ROOT/css" ]; then
  mkdir -p "$PUBLIC/css"
  for f in "$ROOT/css"/*; do
    base="$(basename "$f")"
    if [ ! -e "$PUBLIC/css/$base" ]; then
      ln -sfn "../../../css/$base" "$PUBLIC/css/$base"
    fi
  done
fi

# assets/ racine (video-section.css, etc.) — ne pas écraser laravel/public/assets/img
mkdir -p "$PUBLIC/assets"
if [ -d "$ROOT/assets/css" ] && [ ! -e "$PUBLIC/assets/css" ]; then
  ln -sfn "../../../assets/css" "$PUBLIC/assets/css"
fi
if [ -d "$ROOT/assets/js" ] && [ ! -e "$PUBLIC/assets/js" ]; then
  ln -sfn "../../../assets/js" "$PUBLIC/assets/js"
fi

# Chunks Webpack Elementor (dummy /js/*.bundle.min.js → public/js/)
if [ -d "$ROOT/js" ]; then
  mkdir -p "$PUBLIC/js"
  for f in "$ROOT/js"/*.bundle.min.js; do
    [ -f "$f" ] || continue
    base="$(basename "$f")"
    ln -sfn "../../../js/$base" "$PUBLIC/js/$base"
  done
fi

# Même chunks dans le dossier Elementor (publicPath = urls.assets + "js/")
ELEMENTOR_JS="$ROOT/wp-content/plugins/elementor/assets/js"
if [ -d "$ROOT/js" ] && [ -d "$ELEMENTOR_JS" ]; then
  for f in "$ROOT/js"/*.bundle.min.js; do
    [ -f "$f" ] || continue
    base="$(basename "$f")"
    ln -sfn "../../../../../js/$base" "$ELEMENTOR_JS/$base"
  done
fi

echo "Assets legacy liés dans laravel/public"
