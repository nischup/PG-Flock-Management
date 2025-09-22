# Hostinger Deployment Guide

## Overview
Since Hostinger shared hosting doesn't support Node.js, you need to build assets locally and upload them.

## Development Workflow

### Local Development
```bash
# Install dependencies (first time only)
npm install

# Start development server with hot reload
npm run dev

# Your app will be available at http://localhost:5173
# Laravel will be available at http://localhost:8000
```

### Building for Production
```bash
# Build assets for production
npm run build

# This creates optimized assets in public/build/
```

## Deployment Steps

### 1. Build Assets Locally
```bash
# Run the deployment script
deploy.bat    # Windows
# or
./deploy.sh   # Linux/Mac
```

### 2. Upload to Hostinger
Upload these files/folders to your Hostinger hosting:

**Required Files:**
- All PHP files and directories (app/, bootstrap/, config/, etc.)
- `public/` folder (including `public/build/` with compiled assets)
- `vendor/` folder (or run `composer install` on server)
- `.env` file (configure for production)

**Optional Files:**
- `database/` folder (if you need to run migrations)
- `storage/` folder (create and set permissions)

### 3. Server Configuration

**Document Root:** Set to `public/` folder

**Environment Variables (.env):**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Other production settings...
```

### 4. File Permissions (if needed)
```bash
# Set storage permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

## Development vs Production

### Development
- Use `npm run dev` for hot reload
- Assets are served from Vite dev server
- Source maps available for debugging

### Production
- Use `npm run build` to compile assets
- Assets are served from `public/build/`
- Optimized and minified for performance

## Troubleshooting

### Assets Not Loading
1. Ensure `public/build/` directory is uploaded
2. Check that `public/build/manifest.json` exists
3. Verify Laravel Vite plugin configuration

### Build Errors
1. Run `npm install` to ensure dependencies are installed
2. Check for TypeScript/ESLint errors
3. Ensure all imports are correct

### Performance
- Assets are automatically optimized for production
- Consider enabling gzip compression on Hostinger
- Use CDN for static assets if needed

## Alternative: Git-based Deployment

If you have Git access on Hostinger:

1. Push code to Git repository
2. Pull on server
3. Run `composer install --no-dev`
4. Run `npm run build` (if Node.js is available)
5. Set up webhooks for automatic deployment

## Notes
- Always test locally before deploying
- Keep a backup of your production database
- Monitor error logs after deployment
