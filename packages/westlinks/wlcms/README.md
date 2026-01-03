# CMS Pro - Laravel Package

Professional Content Management System for Laravel applications with WordPress-level functionality.

## Installation

```bash
composer require westlinks/cms-pro
```

## Quick Start

```bash
# Install the package
php artisan cms-pro:install

# Run migrations
php artisan migrate

# Visit the admin interface
# Navigate to: /admin/cms
```

## Features

### ✅ Core Features (v1.0)
- **Content Management**: Create, edit, and organize content with hierarchical structure
- **Media Library**: Upload, organize, and manage media files with folder structure
- **User Integration**: Works with your existing User model and authentication
- **Template System**: Flexible template selection for different page layouts
- **SEO Ready**: Built-in meta fields and sitemap generation
- **Version Control**: Content revisions with restore capability

### 🚧 Planned Features
- **Rich Text Editor**: Tiptap-based block editor (WordPress Gutenberg-style)
- **Visual Menu Builder**: Drag-and-drop menu management
- **Advanced Media**: Image editing, thumbnails, and optimization
- **Content Blocks**: Reusable content snippets
- **Import Tools**: WordPress import functionality

## Configuration

The package can be configured via `config/cms-pro.php`. Publish the config file:

```bash
php artisan vendor:publish --tag=cms-pro-config
```

### Key Configuration Options

```php
// Admin interface settings
'admin' => [
    'prefix' => 'admin/cms',        // Admin URL prefix
    'middleware' => ['web', 'auth'], // Admin middleware
    'layout' => 'layouts.admin',    // Admin layout view
],

// Media settings
'media' => [
    'disk' => 'public',             // Storage disk
    'max_file_size' => 10,          // Max file size in MB
    'path' => 'cms/media',          // Upload path
],

// User model integration
'user' => [
    'model' => App\Models\User::class, // Your User model
    'name_field' => 'name',            // User name field
],
```

## Usage

### Creating Content

```php
use Westlinks\CmsPro\Models\ContentItem;

$content = ContentItem::create([
    'title' => 'My Page',
    'slug' => 'my-page',
    'content' => '<p>Page content goes here...</p>',
    'published' => true,
    'template' => 'default',
]);
```

### Displaying Content

The package automatically registers frontend routes for content display. Content can be accessed at:

- Top-level pages: `/page-slug`
- Child pages: `/parent-slug/child-slug`

## Database Schema

The package uses a clean, separate database schema:

- `cms_content_items` - Main content storage
- `cms_media_assets` - Media file information
- `cms_media_folders` - Media organization
- `cms_content_revisions` - Version history
- `cms_content_media` - Content-media relationships

## Migration from Existing Systems

```bash
# Migrate existing articles and media
php artisan cms-pro:migrate-content

# Verify migration first (dry run)
php artisan cms-pro:migrate-content --verify
```

## Requirements

- PHP 8.1+
- Laravel 11.x
- MySQL/PostgreSQL/SQLite

## License

MIT License. See [LICENSE](LICENSE) for details.

## Support

For support and questions, please contact the West Links development team.