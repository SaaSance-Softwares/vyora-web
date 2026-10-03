#!/bin/bash
VERSION="2.3.0"
OUTPUT="release-v${VERSION}.zip"
echo "Building frontend..."
npm run build
echo "Creating release zip..."
zip -r "$OUTPUT" . -x "*.git*" -x "node_modules/*" -x "vendor/*" -x ".env" -x "public/.htaccess" -x ".htaccess" -x "storage/app/public/*" -x "public/uploads/*" -x "public/storage/*" -x "database/*.sqlite" -x "release.sh" -x "release-*.zip"
echo "Done."
