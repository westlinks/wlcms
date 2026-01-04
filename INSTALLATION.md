# WLCMS Installation & Asset Building

## Installation

```bash
composer require westlinks/wlcms
```

## Asset Publishing & Building

### 1. Publish Package Assets
```bash
# Publish raw assets to your app's resources directory
php artisan vendor:publish --tag=wlcms-assets

# Publish build configuration (optional - for custom builds)
php artisan vendor:publish --tag=wlcms-build-config
```

### 2. Add WLCMS to Your App's Vite Build

**Option A: Include in Main Build (Recommended)**

Add to your `resources/js/app.js`:
```javascript
// Import WLCMS functionality
import './vendor/wlcms/js/wlcms.js';
```

Add to your `resources/css/app.css`:
```css
/* Import WLCMS styles */
@import './vendor/wlcms/css/wlcms.css';
```

**Option B: Separate WLCMS Build**

Update your `vite.config.js`:
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/vendor/wlcms/js/wlcms.js', // Add WLCMS
                'resources/vendor/wlcms/css/wlcms.css'
            ],
            refresh: true,
        }),
    ],
});
```

### 3. Build Assets
```bash
npm run build
# or for development
npm run dev
```

### 4. Include Assets in Blade Templates

**Option A: If included in main build:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

**Option B: If separate build:**
```blade
@vite([
    'resources/css/app.css', 
    'resources/js/app.js',
    'resources/vendor/wlcms/css/wlcms.css',
    'resources/vendor/wlcms/js/wlcms.js'
])
```

## Database Migration

```bash
php artisan vendor:publish --tag=wlcms-migrations
php artisan migrate
```

## Configuration (Optional)

```bash
php artisan vendor:publish --tag=wlcms-config
```

Edit `config/wlcms.php` to customize settings.

---

## Modern Architecture

WLCMS follows Laravel 11-12 best practices:
- ✅ ES6+ modular JavaScript components  
- ✅ Vite build process integration
- ✅ Raw asset publishing (not pre-built)
- ✅ Consumer controls their own build process
- ✅ TypeScript ready structure