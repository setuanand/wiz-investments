#!/usr/bin/env bash
set -euo pipefail

THEME_DIR="wp-content/themes/wiz-theme"

printf "Checking PHP syntax...\n"
find "$THEME_DIR" -name '*.php' -print0 | xargs -0 php -l

printf "Checking theme header...\n"
if ! grep -q "Theme Name: Wiz Theme" "$THEME_DIR/style.css"; then
  echo "Missing or invalid theme header in $THEME_DIR/style.css"
  exit 1
fi

printf "Checking recommended files...\n"
for file in header.php footer.php functions.php index.php page.php front-page.php 404.php style.css; do
  if [[ ! -f "$THEME_DIR/$file" ]]; then
    echo "Missing theme file: $file"
    exit 1
  fi
 done

printf "All checks passed.\n"