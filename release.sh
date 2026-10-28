#!/bin/bash
VERSION="2.6.0"
OUTPUT="release-v${VERSION}.zip"

echo "Building frontend..."
npm run build

echo "Creating OTA update zip (release-v${VERSION}.zip)..."
rm -f "$OUTPUT"
zip -r "$OUTPUT" . \
  -x "*.git*" \
  -x "node_modules/*" \
  -x "vendor/*" \
  -x ".env*" \
  -x "public/.htaccess" \
  -x ".htaccess" \
  -x "storage/installed" \
  -x "storage/app/public/*" \
  -x "storage/uploads/*" \
  -x "storage/logs/*.log" \
  -x "storage/inertia-devtools/*" \
  -x "storage/framework/cache/data/*" \
  -x "storage/framework/cache/laravel-excel/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/testing/*" \
  -x "storage/framework/views/*.php" \
  -x "public/uploads/*" \
  -x "public/storage/*" \
  -x "database/*.sqlite" \
  -x "release.sh" \
  -x "release-*.zip" \
  -x "*.zip" \
  -x "*.DS_Store" \
  -x "*.orig" \
  -x "*.patch" \
  -x "*.rej" \
  -x "*.bak" \
  -x ".phpunit.result.cache" \
  -x "*scratch*" \
  -x "brain.md" \
  -x ".gemini/*"

echo "Done. Created $OUTPUT successfully."
