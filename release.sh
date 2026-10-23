#!/bin/bash
VERSION="2.5.4"
OUTPUT="release-v${VERSION}.zip"
echo "Building frontend..."
npm run build
echo "Creating release zip..."
zip -r "$OUTPUT" . -x "*.git*" -x "node_modules/*" -x "vendor/*" -x ".env*" -x "public/.htaccess" -x ".htaccess" -x "storage/app/public/*" -x "storage/uploads/*" -x "storage/logs/*.log" -x "storage/inertia-devtools/*" -x "storage/framework/cache/data/*" -x "storage/framework/sessions/*" -x "storage/framework/views/*.php" -x "public/uploads/*" -x "public/storage/*" -x "database/*.sqlite" -x "release.sh" -x "release-*.zip" -x "*.zip" -x "*.DS_Store" -x "*.orig" -x "*.patch" -x "*.rej" -x "*.bak" -x ".phpunit.result.cache" -x "*scratch*"
echo "Done."
