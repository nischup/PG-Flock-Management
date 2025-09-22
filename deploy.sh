#!/bin/bash

echo "Building assets for production..."
npm run build

if [ $? -ne 0 ]; then
    echo "Build failed! Please check the errors above."
    exit 1
fi

echo ""
echo "Build completed successfully!"
echo ""
echo "Next steps:"
echo "1. Upload your entire project folder to Hostinger"
echo "2. Make sure to include the public/build/ directory"
echo "3. Set your document root to the public/ folder"
echo "4. Configure your .env file for production"
echo ""
echo "Files to upload:"
echo "- All PHP files and directories"
echo "- public/build/ (contains compiled assets)"
echo "- vendor/ (or run composer install on server)"
echo "- .env (configure for production)"
echo ""
