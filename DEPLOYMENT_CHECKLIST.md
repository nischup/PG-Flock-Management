# Hostinger Deployment Checklist

## Pre-Deployment

### ✅ Local Development
- [ ] Code changes are complete and tested
- [ ] All tests pass (`php artisan test`)
- [ ] No linting errors (`npm run lint`)
- [ ] Code is formatted (`npm run format`)

### ✅ Build Process
- [ ] Run `npm run build` or `deploy.bat`
- [ ] Check that `public/build/` directory exists
- [ ] Verify `public/build/manifest.json` is created
- [ ] Test build locally with `php artisan serve`

## Deployment

### ✅ File Upload
- [ ] Upload entire project to Hostinger
- [ ] Ensure `public/build/` directory is included
- [ ] Upload `vendor/` directory (or run `composer install` on server)
- [ ] Upload `.env` file with production settings

### ✅ Server Configuration
- [ ] Set document root to `public/` folder
- [ ] Configure `.env` for production:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL=https://yourdomain.com`
  - [ ] Database credentials
  - [ ] Mail settings
  - [ ] Cache settings

### ✅ File Permissions
- [ ] Set `storage/` directory permissions (775)
- [ ] Set `bootstrap/cache/` permissions (775)
- [ ] Ensure Laravel can write to storage

## Post-Deployment

### ✅ Testing
- [ ] Visit your website
- [ ] Check that CSS/JS assets load correctly
- [ ] Test key functionality
- [ ] Check error logs for issues

### ✅ Performance
- [ ] Enable gzip compression (if available)
- [ ] Set up caching headers
- [ ] Consider CDN for static assets

## Troubleshooting

### Assets Not Loading
1. Check `public/build/manifest.json` exists
2. Verify Laravel Vite plugin is working
3. Check browser console for 404 errors

### Database Issues
1. Run migrations: `php artisan migrate`
2. Clear cache: `php artisan cache:clear`
3. Generate app key: `php artisan key:generate`

### Performance Issues
1. Enable OPcache (if available)
2. Use Redis for caching (if available)
3. Optimize images and assets

## Quick Commands

```bash
# Build for production
npm run build

# Deploy script
deploy.bat

# Test production build locally
php artisan serve
```

## Notes
- Always backup your database before deployment
- Test on staging environment first if possible
- Monitor error logs after deployment
- Keep local development environment in sync
