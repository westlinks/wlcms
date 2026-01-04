# WLCMS - Westlinks Laravel Content Management System

Professional Laravel package providing WordPress-level content management functionality for SaaS applications. Self-contained, reusable package with rich text editing, file management, and advanced CMS features.

## ✅ Current Status: Production Ready

- **Phase 1 Complete**: Full CMS foundation with admin interface ✅
- **Phase 2 Complete**: Tiptap Rich Text Editor with source view ✅
- **Next Phase**: File Upload System with S3 support

## Installation

```bash
composer require westlinks/wlcms
```

## Quick Start

```bash
# Publish and run migrations
php artisan vendor:publish --tag=wlcms-migrations
php artisan migrate

# Publish assets (CSS/JS bundles)
php artisan vendor:publish --tag=wlcms-assets
```

### Vite Configuration (Required)

Add WLCMS JavaScript and CSS to your application's `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/vendor/wlcms/js/wlcms.js',    // Add this line
                'resources/vendor/wlcms/css/wlcms.css'   // Add this line
            ],
            refresh: true,
        }),
    ],
    // ... rest of your config
});
```

Then rebuild your assets:

```bash
npm run build
```

### Access Admin Interface

```
# Navigate to: /admin/wlcms/dashboard
```

## Features

### ✅ Core Features (Production Ready)

#### Content Management System
- **Professional Admin Interface**: Modern Tailwind CSS design with responsive layout
- **Complete CRUD Operations**: Create, edit, update, delete content with validation
- **Content Organization**: Categories, tags, and hierarchical content structure
- **Content Types**: Flexible content type system (page, article, event, etc.)
- **Status Management**: Draft, published, scheduled content workflow

#### Rich Text Editor (Tiptap Integration)
- **WYSIWYG Editor**: Professional editing experience with comprehensive toolbar
- **Formatting Options**: Bold, italic, code, headings (H1-H3), lists, blockquotes
- **Code Blocks**: Syntax-highlighted code block support
- **HTML Source View**: Toggle between visual and raw HTML editing modes
- **History Management**: Undo/redo functionality
- **Form Integration**: Seamless Laravel form integration with hidden textarea sync

#### Self-Contained Package Architecture
- **Zero Host App Dependencies**: No modifications to your app's JS/CSS required
- **Dedicated Assets**: Self-contained `wlcms.js` and `wlcms.css` bundles
- **Modern Build Process**: Vite-based asset compilation with optimization
- **Asset Publishing**: Automatic publishing to `public/vendor/wlcms/assets/`

#### Database Schema
- **Clean Separation**: All tables use `cms_*` prefix for clean organization
- **Proper Relationships**: Foreign keys and indexes for optimal performance
- **Version Control Ready**: Built-in support for content revisions
- **Media Integration**: Ready for file upload and media library features

### 🚧 In Development (Phase 2 Continuation)

- **File Upload System**: S3-compatible storage with image resizing
- **Media Library**: Professional media management interface  
- **Content Revisions**: Backend logic for revision management
- **SEO Meta Management**: Meta tags, Open Graph, Twitter Cards
- **Content Scheduling**: Background job-based publishing system

## Configuration

The package works out of the box with sensible defaults. For customization:

```bash
php artisan vendor:publish --tag=wlcms-config
```

### Key Configuration Options

```php
// config/wlcms.php
return [
    'admin' => [
        'prefix' => 'admin/wlcms',           // Admin URL prefix
        'middleware' => ['web', 'auth'],     // Admin middleware
    ],
    
    'database' => [
        'prefix' => 'cms_',                  // Table prefix
    ],
    
    'storage' => [
        'disk' => 'public',                  // Default storage disk
        'path' => 'wlcms',                   // Upload path
    ],
];
```

## Usage

### Admin Interface

Access the admin interface at `/admin/wlcms/dashboard` after authentication.

**Navigation:**
- **Dashboard**: Content overview and statistics
- **Content**: Create, edit, and manage content with rich text editor
- **Media**: File upload and media library (coming soon)

### Rich Text Editor Integration

The Tiptap editor is automatically included in content forms:

```blade
{{-- Automatically included in create/edit forms --}}
@include('wlcms::admin.components.editor', [
    'name' => 'content',
    'value' => $content->content ?? '',
    'label' => 'Content',
    'required' => false
])
```

**Editor Features:**
- Visual WYSIWYG editing with toolbar
- HTML source view toggle (`</>` button)
- Real-time content sync with form submission
- Professional typography and spacing

### Programmatic Content Management

```php
use Westlinks\Wlcms\Models\ContentItem;

// Create content
$content = ContentItem::create([
    'title' => 'Welcome to Our Site',
    'slug' => 'welcome',
    'content' => '<p>This content was created with the <strong>Tiptap editor</strong>!</p>',
    'excerpt' => 'A warm welcome message',
    'status' => 'published',
    'content_type' => 'page',
]);

// Retrieve content
$page = ContentItem::where('slug', 'welcome')->first();
$published = ContentItem::published()->get();
$drafts = ContentItem::drafts()->get();
```

## Database Schema

Clean, well-organized database structure with `cms_*` prefixes:

```sql
cms_content_items       # Main content storage
├── id, title, slug, content, excerpt
├── status, content_type, publish_at
├── meta (JSON), author_id
└── timestamps

cms_categories          # Hierarchical categories  
cms_tags               # Tag system with pivots
cms_media_assets       # Media file management
cms_content_revisions  # Version control
```

## Asset Management

### Development Workflow

In the package directory (`westlinks/wlcms`):

```bash
# Install dependencies
npm install

# Build for development
npm run dev

# Build for production  
npm run build
```

### Publishing Updates

After package updates:

```bash
# Update package
composer update westlinks/wlcms

# Republish assets
php artisan vendor:publish --tag=wlcms-assets --force

# Clear cached views
php artisan view:clear
```

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Progressive enhancement for older browsers

## Requirements

- **PHP**: 8.1+ 
- **Laravel**: 11.x or 12.x
- **Database**: MySQL, PostgreSQL, or SQLite
- **Node.js**: 16+ (for development only)

## Package Structure

```
westlinks/wlcms/
├── src/
│   ├── Http/Controllers/Admin/     # Admin controllers
│   ├── Models/                     # Eloquent models  
│   ├── WlcmsServiceProvider.php    # Service provider
│   └── Commands/                   # Artisan commands
├── resources/
│   ├── views/admin/                # Admin interface views
│   ├── js/wlcms.js                # JavaScript bundle
│   └── css/wlcms.css              # CSS bundle  
├── database/migrations/            # Database schema
├── public/build/assets/           # Built assets for distribution
└── package.json                   # NPM dependencies
```

## Development Status

### ✅ Completed (Production Ready)
- Core CMS functionality with admin interface
- Tiptap rich text editor with source view
- Self-contained package architecture  
- Professional UI/UX with responsive design
- Form validation and error handling
- Asset publishing and management

### 🎯 Next Phase
- File upload system with S3 integration
- Media library interface
- Content revisions backend
- SEO meta management
- Content scheduling system

## Contributing

This is a private package for Westlinks projects. For internal development:

1. Clone the repository
2. Install dependencies: `npm install`  
3. Make changes and test thoroughly
4. Build assets: `npm run build`
5. Commit and push changes

## License

Proprietary - Westlinks Internal Use

---

**Ready for production use with rich text editing capabilities!**
