# CMS Improvement Plan
**West Links Convention Management Platform**  
*Planning Document for WordPress-Level Content Management System*

**Date:** December 3, 2025  
**Status:** Planning Phase  
**Priority:** High - Client Migration Dependent

---

## Planning Assessment & Architecture Decision

### Document Completeness: ✅ **Comprehensive & Ready for Implementation**

This planning document has been thoroughly reviewed and assessed as **exceptionally complete and well-structured**. It successfully covers all essential planning aspects:

- ✅ **Clear problem definition** with detailed current state analysis
- ✅ **Comprehensive feature comparison** with WordPress using structured tables
- ✅ **Phase-based implementation strategy** with realistic 10-week timeline
- ✅ **Technical specifications** including complete database schemas and file modifications
- ✅ **Risk assessment** with practical mitigation strategies
- ✅ **Success metrics** covering both quantitative and qualitative measures
- ✅ **Migration strategy** addressing both existing content and WordPress imports

The technical details demonstrate deep understanding of both current system capabilities and WordPress feature parity requirements. The document is **ready for immediate implementation**.

### Architecture Decision: Laravel Package vs. Direct Integration

**RECOMMENDATION: Build as Laravel Package from Day 1 (with new table strategy)**

After thorough analysis of the CMS requirements and the decision to use completely new table names/models, building this as a reusable Laravel package from the start provides significant strategic advantages while eliminating production risk:

#### 🟢 **Strong Arguments for Laravel Package:**

1. **Multi-Application Scalability**
   - Deploy identical CMS functionality across entire SaaS portfolio
   - Single codebase ensures consistent features and unified bug fixes
   - Economies of scale for long-term maintenance and feature development

2. **Maintenance Efficiency**
   - Centralized bug fixes benefit all applications simultaneously
   - Feature enhancements propagate across entire platform ecosystem
   - Unified testing and quality assurance processes

3. **Superior Architecture**
   - Clean separation of concerns through service provider pattern
   - Publishable migrations, views, and configuration files
   - Modular feature enablement (optional components)
   - Framework-standard integration patterns

4. **Strategic Market Position**
   - Potential for open-source community contribution or commercial licensing
   - Enhanced reputation within Laravel ecosystem
   - Possible additional revenue stream opportunity

#### 🔴 **Package Development Considerations:**

1. **Initial Complexity Increase**
   - Requires more sophisticated architectural planning
   - Service provider configuration and abstraction layer development

2. **Multi-Context Testing Requirements**
   - Compatibility testing across Laravel versions
   - Validation in multiple application environments

#### 📦 **Recommended Package Structure:**

```
westlinks/cms-pro/
├── src/
│   ├── CmsServiceProvider.php
│   ├── Models/
│   ├── Http/Controllers/
│   ├── Services/
│   └── View/Components/
├── database/migrations/
├── resources/views/
├── config/cms.php
└── routes/cms.php
```

#### 🎯 **New Table Strategy Implementation:**

**KEY INSIGHT: Using completely new table names (cms_content_items, cms_media_assets, etc.) eliminates all risk to existing production systems, making package-first development the optimal approach.**

1. **Phase 1: Package Development** (Clean Architecture)
   - Build complete Laravel package with new CMS v2 schema
   - Develop and test within WLCM as first implementation
   - Zero risk to existing articles/media tables and functionality
   - Clean, modern database design without legacy constraints

2. **Phase 2: Content Migration Tools** (Seamless Transition)
   - Build robust migration commands: `php artisan cms:migrate-content`
   - Implement parallel system operation (old + new running simultaneously)
   - Gradual URL transition with fallback routing
   - Comprehensive content validation and integrity checking

3. **Phase 3: Portfolio Rollout** (Rapid Deployment)
   - Deploy package to WLBFM and other SaaS applications
   - New apps get modern CMS schema immediately
   - Existing apps use migration tools for seamless upgrade

#### 🔧 **Package Configuration Requirements:**

Key components requiring configurable abstraction:
- **User Model Integration** (varying User model implementations)
- **Permission System Binding** (optional Spatie integration)
- **Storage Configuration** (application-specific disk configurations)
- **Route Structure** (customizable admin prefixes: `/admin/cms` vs `/dashboard/content`)
- **UI Theming** (publishable CSS/JS assets with customization hooks)

### Final Strategic Recommendation

**Build as Laravel Package from Day 1 using new CMS v2 table structure.** This approach delivers:

- ✅ **Zero production risk** due to completely separate table structure
- ✅ **Clean architecture** without legacy constraints or technical debt
- ✅ **Faster portfolio deployment** to other SaaS applications
- ✅ **Future-proof design** with modern Laravel conventions
- ✅ **Parallel operation** during migration period for maximum safety

**Implementation should commence with package scaffolding and Sprint 1 (Enhanced Media Library) to establish foundation and achieve immediate user experience improvements.**

---

## Development Environment Setup

### **Fresh Laravel 12 Installation for WLCMS Package Development**

Complete setup sequence for creating the WLCMS package development environment:

```bash
# 1. Install Laravel globally (if not already installed)
composer global require laravel/installer

# 2. Create new Laravel 12 project
laravel new public_html
cd public_html/

# 3. Configure database settings
vi .env
# Set: DB_DATABASE=wlcms
# Set: DB_USERNAME=your_username  
# Set: DB_PASSWORD=your_password
# Save and exit (:wq)

# 4. Create database
mariadb
# > create database wlcms;
# > quit;

# 5. Run initial migrations
php artisan migrate

# 6. Install CMS package dependencies
composer require livewire/livewire
npm install alpinejs @tailwindcss/forms
composer require intervention/image-laravel
composer require spatie/laravel-permission
npm install @tiptap/core @tiptap/pm @tiptap/starter-kit

# 7. Install development tools
composer require laravel/telescope --dev
composer require barryvdh/laravel-debugbar --dev

# 8. Update all dependencies
composer update
```

**Working Directory:** `/var/www/html/westlinks_online/wlcms/public_html`

**Package Development Target:** `westlinks/wlcms` repository

---

## Executive Summary

This document outlines the comprehensive plan to enhance the existing CMS (Content Management System) to provide state-of-the-art, WordPress-level functionality for clients migrating from WordPress to our platform. While our event management capabilities are industry-leading, the current article-based CMS is functional but "unintuitive and awkward" for non-technical users.

**Goal:** Create an intuitive, powerful CMS that rivals WordPress while maintaining seamless integration with our superior event management system.

---

## CMS v2 Table Strategy

### **Architecture Decision: New Tables & Models**

To eliminate any risk to existing production systems and enable clean package architecture, the new CMS will use completely separate table names and models:

#### **New Schema Naming Convention:**
```php
// CMS v2 Tables (Clean, Modern Architecture)
cms_content_items     // Replaces 'articles' - main content
cms_media_assets      // Replaces 'media' - file management  
cms_content_blocks    // NEW - reusable content snippets
cms_menu_items        // NEW - visual menu builder
cms_content_revisions // NEW - version history
cms_media_folders     // NEW - media organization
cms_redirects         // NEW - URL management
```

#### **Benefits of Parallel Architecture:**
- ✅ **Zero Risk Development:** Existing website continues functioning perfectly
- ✅ **Clean Design:** No legacy constraints or compromises
- ✅ **Package Ready:** Modern structure optimized for multi-app deployment
- ✅ **Gradual Migration:** Move content piece-by-piece with validation
- ✅ **Rollback Safety:** Easy fallback if issues arise

#### **Migration Strategy:**
1. **Parallel Operation:** Both old and new systems run simultaneously
2. **Content Migration Tools:** `php artisan cms:migrate-content --verify`
3. **URL Transition:** Automatic fallback routing for seamless user experience
4. **Legacy Deprecation:** Archive old system after 6+ months of stable operation

---

## Current State Analysis

### ✅ Strengths

#### 1. **Solid Data Architecture**
- **Article Model** (`app/Models/Article.php`)
  - Rich content fields: `title`, `subtitle`, `intro`, `abstract`, `description`, `menu_title`
  - Flexible hierarchy: `parent_id`, `is_parent` flags
  - Many-to-many parent relationships via `article_parent` pivot table (allows articles to appear under multiple parents)
  - SEO-friendly: `slug` generation, `published` scope
  - Media support: `image` fields with positioning and captions
  - Template system: `template_id` for different layouts
  - Audit trail: `created_by`, `updated_by`
  - Sort ordering: `sort` field for custom ordering

- **Media Model** (`app/Models/Media.php`)
  - Storage integration with Laravel's filesystem
  - Basic metadata: `filename`, `path`, `description`, `thumbnail`
  - Support for multiple file types (images, PDFs, MP3s)
  - Year-based filtering capability

#### 2. **Functional Admin Interface**
- **Article Management** (`resources/views/admin/articles/index.blade.php`)
  - AlpineJS-powered filtering (all, published, drafts, top menu)
  - Real-time search by title/slug
  - Inline editing: sort order, published status, top menu toggle
  - Hierarchical display (parent/child visualization)
  - Responsive design with dark mode support

- **Media Library** (`resources/views/admin/media/index.blade.php`)
  - Upload functionality with drag-and-drop
  - Year-based filtering (e.g., `?filter=2025`)
  - TinyMCE rich text editor for descriptions
  - Direct URL access for embedding

#### 3. **Dynamic Navigation System**
- Database-driven top menu (`USE_dB_NAV` setting)
- Parent/child dropdown menus
- Automatic URL routing: `/{parent_article}/{article}`
- Template-based rendering (`layouts.pages-full`, `layouts.pages-full-narrow-right`)
- Mobile-responsive hamburger menu

#### 4. **Developer-Friendly Features**
- Route model binding with slugs
- Eager loading for performance (`with(['event.location'])`)
- Permission-based editing (superuser inline edit links)
- Clean separation: controllers, models, views, components

### ❌ Current Limitations & Pain Points

#### 1. **User Experience Issues**
- **Complex Parent/Child Relationships:** Users struggle with the dual concept of:
  - `parent_id` (single parent for URL structure: `/parent/child`)
  - `parents()` many-to-many (for appearing in multiple menu locations)
  - Confusion: "Why do I need to set both parent_id AND parent_ids?"

- **Rich Text Editor Limitations:**
  - TinyMCE basic configuration (only `table`, `code`, `link` plugins)
  - No visual block editing (WordPress Gutenberg-style)
  - Limited formatting options compared to WordPress
  - No media browser integration from within editor

- **Media Management Gaps:**
  - No drag-and-drop upload in admin interface
  - No bulk operations (delete, rename, organize)
  - No folder/category organization
  - Manual URL copying required for embedding
  - No image editing (crop, resize, optimize)
  - No alt text for accessibility/SEO
  - Limited metadata (no tags, categories, dimensions)

- **Navigation Menu Builder:**
  - No visual menu builder
  - Sort order requires manual numbering
  - No drag-and-drop reordering
  - Difficult to understand which pages appear where

#### 2. **Missing WordPress Features**
- **Content Editing:**
  - No content revisions/version history
  - No auto-save drafts
  - No preview before publishing
  - No scheduled publishing
  - No content duplication/cloning

- **SEO Optimization:**
  - No meta descriptions
  - No Open Graph tags
  - No structured data
  - No XML sitemap generation
  - No redirect management

- **Media Library:**
  - No thumbnail generation for different sizes
  - No image optimization on upload
  - No media search/filtering by type
  - No attachment relationships (which pages use this image?)

- **User Roles:**
  - No content-specific roles (Editor, Author, Contributor)
  - All-or-nothing admin access
  - No content approval workflow

#### 3. **Technical Debt**
- Commented-out code in views (indicates uncertainty/incomplete features)
- Multiple rich text implementations (TinyMCE, CKEditor dark theme present but unused)
- Inconsistent form handling (Form facade vs html() helper)
- Template selection limited to match statement (no visual template picker)

---

## WordPress Feature Comparison

### Core WordPress CMS Features

| Feature | WordPress | Current System | Priority |
|---------|-----------|----------------|----------|
| **Block Editor (Gutenberg)** | ✅ Visual drag-and-drop blocks | ❌ Plain textarea + basic TinyMCE | 🔴 High |
| **Media Library** | ✅ Grid view, upload, edit, organize | ⚠️ Basic list with filtering | 🔴 High |
| **Menu Builder** | ✅ Visual drag-and-drop menu editor | ❌ Manual sort numbering | 🟡 Medium |
| **Content Revisions** | ✅ Full version history + restore | ❌ None | 🟡 Medium |
| **Preview Changes** | ✅ Preview before publish | ❌ None | 🟡 Medium |
| **Auto-Save** | ✅ Automatic draft saving | ❌ Manual save only | 🟢 Low |
| **Scheduled Publishing** | ✅ Date/time scheduling | ❌ Publish immediately only | 🟢 Low |
| **SEO Meta Fields** | ✅ (via Yoast/RankMath) | ❌ None | 🟡 Medium |
| **Content Duplication** | ✅ Clone pages | ❌ None | 🟢 Low |
| **Image Editing** | ✅ Crop, rotate, resize in browser | ❌ None | 🟡 Medium |
| **Media Organization** | ✅ Folders/categories | ⚠️ Year filtering only | 🟡 Medium |
| **User Roles** | ✅ Admin, Editor, Author, Contributor | ⚠️ Admin/Super Admin only | 🟢 Low |
| **Custom Fields** | ✅ ACF/meta boxes | ⚠️ Fixed schema | 🟢 Low |

---

## Proposed Architecture Improvements

### Phase 1: Core UX Enhancements (Immediate Priority)

#### 1.1 Modern Rich Text Editor
**Goal:** Replace basic TinyMCE with modern block-based editor

**Options:**
- **Tiptap** (Recommended)
  - Vue 3 compatible (Laravel + Inertia path)
  - Lightweight, extensible
  - Block-based architecture
  - Laravel packages available: `ueberdosis/tiptap`
  
- **FilamentPHP Editor** 
  - Built for Laravel admin panels
  - Markdown + WYSIWYG hybrid
  - Image upload integration
  
- **CKEditor 5**
  - Already have CKEditor 4 dark theme
  - Powerful but heavier
  - Inline editing capabilities

**Implementation Plan:**
1. Create new Blade component: `<x-tiptap-editor selector="#description" />`
2. Implement media browser integration (click image → select from media library)
3. Add formatting toolbar: headings, lists, links, images, tables, code blocks
4. Enable paste from Word/Google Docs cleanup
5. Auto-save drafts every 30 seconds to localStorage
6. Add character/word count

**Files to Modify:**
- `resources/views/components/tiptap-editor.blade.php` (NEW)
- `resources/views/admin/articles/create.blade.php`
- `resources/views/admin/articles/edit.blade.php`
- `package.json` (add Tiptap dependencies)

#### 1.2 Enhanced Media Library
**Goal:** WordPress-level media management

**Features:**
1. **Grid View with Thumbnails**
   - Replace table with responsive grid
   - Hover actions: view, edit, delete, copy URL
   - Bulk selection checkboxes
   - Image preview modal

2. **Advanced Upload**
   - Drag-and-drop zone on index page
   - Multiple file upload
   - Progress indicators
   - Automatic thumbnail generation (using Intervention Image)
   - Image optimization on upload (compress, resize)

3. **Better Organization**
   - Folder/collection system
   - Tags for categorization
   - Filter by: type (image/PDF/audio), year, folder, tag
   - Search by filename, description, tags

4. **Image Metadata**
   - Alt text (accessibility & SEO)
   - Caption, credit, copyright
   - Dimensions (auto-detected)
   - File size (auto-detected)
   - Usage tracking: "Used in 3 pages" with links

5. **In-Browser Editing**
   - Crop tool
   - Resize (with aspect ratio lock)
   - Rotate
   - Basic filters (brightness, contrast)
   - Generate multiple sizes (thumbnail, medium, large)

**New CMS v2 Database Schema:**
```php
// Main content table
Schema::create('cms_content_items', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->string('subtitle')->nullable();
    $table->text('excerpt')->nullable();
    $table->longText('content');
    $table->string('featured_image')->nullable();
    $table->string('template', 50)->default('default');
    $table->foreignId('parent_id')->nullable()->constrained('cms_content_items')->cascadeOnDelete();
    $table->integer('sort')->default(0);
    $table->boolean('published')->default(false);
    $table->timestamp('published_at')->nullable();
    $table->string('meta_description', 160)->nullable();
    $table->json('meta_data')->nullable(); // SEO, Open Graph, etc.
    $table->foreignId('created_by')->constrained('users');
    $table->foreignId('updated_by')->constrained('users');
    $table->timestamps();
});

// Enhanced media table
Schema::create('cms_media_assets', function (Blueprint $table) {
    $table->id();
    $table->string('filename');
    $table->string('original_filename');
    $table->string('path');
    $table->string('disk', 20)->default('public');
    $table->string('mime_type', 100);
    $table->integer('filesize'); // bytes
    $table->integer('width')->nullable();
    $table->integer('height')->nullable();
    $table->string('alt_text')->nullable();
    $table->text('description')->nullable();
    $table->string('caption')->nullable();
    $table->string('credit')->nullable();
    $table->json('tags')->nullable();
    $table->foreignId('folder_id')->nullable()->constrained('cms_media_folders')->nullOnDelete();
    $table->foreignId('uploaded_by')->constrained('users');
    $table->timestamps();
});

// Media organization
Schema::create('cms_media_folders', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('parent_id')->nullable()->constrained('cms_media_folders')->cascadeOnDelete();
    $table->integer('sort')->default(0);
    $table->timestamps();
});

// Content-Media relationships (track usage)
Schema::create('cms_content_media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('content_id')->constrained('cms_content_items')->cascadeOnDelete();
    $table->foreignId('media_id')->constrained('cms_media_assets')->cascadeOnDelete();
    $table->timestamps();
});

// Menu system
Schema::create('cms_menu_items', function (Blueprint $table) {
    $table->id();
    $table->string('menu_location'); // 'primary', 'footer', 'sidebar'
    $table->morphs('linkable'); // CmsContentItem, Category, CustomLink
    $table->string('label');
    $table->string('url')->nullable(); // For custom links
    $table->foreignId('parent_id')->nullable()->constrained('cms_menu_items')->cascadeOnDelete();
    $table->integer('sort')->default(0);
    $table->json('options')->nullable(); // target, CSS classes, etc.
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

**Files to Create/Modify:**
- `app/Models/MediaFolder.php` (NEW)
- `database/migrations/xxxx_add_enhanced_media_fields.php` (NEW)
- `database/migrations/xxxx_create_media_folders_table.php` (NEW)
- `database/migrations/xxxx_create_article_media_table.php` (NEW)
- `resources/views/admin/media/index.blade.php` (MAJOR REWRITE)
- `app/Http/Controllers/Admin/MediaController.php` (enhance methods)
- `app/Services/MediaService.php` (NEW - handle image processing)

#### 1.3 Simplified Parent/Child System
**Goal:** Eliminate confusion between single parent (URL) and multiple parents (menu)

**Proposed Solution:**
1. **Rename Fields for Clarity:**
   - `parent_id` → `url_parent_id` (labeled "URL Parent: /parent/this-page")
   - `parents()` relationship → `menu_locations()` (labeled "Show in Menus")

2. **Visual Menu Location Picker:**
   ```
   [ ] Home Menu
   [ ] About Submenu
   [ ] Events Submenu
   [✓] Resources Submenu
   ```

3. **URL Builder Preview:**
   ```
   URL Structure:
   yoursite.com/resources/this-page-title
                 ↑         ↑
                 URL Parent  This Page
   ```

4. **Validation:**
   - If `url_parent_id` set, warn if not also selected in `menu_locations`
   - Suggest: "Add to [Resources Menu] so visitors can find this page"

**Files to Modify:**
- `database/migrations/xxxx_rename_article_parent_fields.php` (NEW)
- `app/Models/Article.php` (rename relationships)
- `resources/views/admin/articles/create.blade.php`
- `resources/views/admin/articles/edit.blade.php`

---

### Phase 2: Content Management Features (Medium Priority)

#### 2.1 Content Revisions System
**Goal:** Track all changes with ability to restore

**Database Schema:**
```php
Schema::create('article_revisions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('article_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->json('content'); // Store all article fields
    $table->string('revision_note')->nullable();
    $table->boolean('is_autosave')->default(false);
    $table->timestamp('created_at');
});
```

**Features:**
- Auto-save every change as revision
- Compare revisions side-by-side (diff view)
- One-click restore
- Revision notes: "Updated event pricing section"
- Limit: Keep last 25 revisions per article

**UI Additions:**
- "Revisions" tab in edit form
- Revision timeline with user avatars
- Diff viewer: green (added) / red (removed) highlighting

#### 2.2 Preview Before Publishing
**Goal:** See exactly how page will look without publishing

**Implementation:**
1. "Preview" button in edit form
2. Generate temporary token: `?preview=a1b2c3d4e5`
3. Route: `/preview/{article:slug}?token={token}`
4. Bypass `published` scope if valid token
5. Token expires after 1 hour
6. Show banner: "This is a preview. Changes are not published."

**Files:**
- `routes/web.php` (add preview route)
- `app/Http/Controllers/PreviewController.php` (NEW)
- `resources/views/admin/articles/edit.blade.php` (add preview button)

#### 2.3 SEO Enhancements
**Goal:** Built-in SEO without plugins

**New Fields:**
```php
Schema::table('articles', function (Blueprint $table) {
    $table->string('meta_description', 160)->nullable();
    $table->string('meta_keywords')->nullable();
    $table->string('og_title')->nullable(); // Open Graph
    $table->text('og_description')->nullable();
    $table->string('og_image')->nullable();
    $table->string('canonical_url')->nullable();
    $table->boolean('noindex')->default(false);
    $table->boolean('nofollow')->default(false);
});
```

**Features:**
1. **SEO Panel in Editor:**
   - Meta description with 160-char counter
   - Google preview snippet
   - Keywords (comma-separated)
   - Open Graph preview (Facebook/Twitter)

2. **Automatic Sitemap:**
   - `/sitemap.xml` route
   - Auto-generate from published articles
   - Include priority, change frequency
   - Submit to Google Search Console

3. **Redirect Manager:**
   - Old URL → New URL redirects (301/302)
   - Helpful when changing slugs
   - Prevent broken links

**New Components:**
- `resources/views/components/seo-preview.blade.php` (NEW)
- `app/Http/Controllers/SitemapController.php` (NEW)
- `app/Models/Redirect.php` (NEW)

---

### Phase 3: Advanced Features (Lower Priority)

#### 3.1 Visual Menu Builder
**Goal:** Drag-and-drop menu management

**Options:**
- **Livewire Sortable** (`livewire/sortable`)
- **AlpineJS + SortableJS**

**Features:**
- Drag items to reorder
- Indent/outdent for hierarchy
- Add pages, custom links, categories
- Multiple menu locations: "Top Menu", "Footer Menu", "Sidebar Menu"
- Preview menu appearance

**Database:**
```php
Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->string('menu_location'); // 'top', 'footer', 'sidebar'
    $table->morphs('linkable'); // Article, Category, CustomLink
    $table->string('label');
    $table->string('url')->nullable(); // For custom links
    $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
    $table->integer('sort');
    $table->json('options')->nullable(); // target="_blank", CSS classes, etc.
    $table->timestamps();
});
```

#### 3.2 Content Blocks System
**Goal:** Reusable content snippets

**Use Cases:**
- Footer contact info (appears on multiple pages)
- Registration deadline banner
- Sponsor logos
- Testimonials

**Implementation:**
- New model: `ContentBlock`
- Shortcode syntax: `[block:footer-contact]`
- Or Blade directive: `@block('footer-contact')`
- Edit blocks in admin: `/admin/content-blocks`

#### 3.3 Advanced User Roles
**Goal:** Granular content permissions

**Roles:**
- **Editor:** Can publish/unpublish all content
- **Author:** Can create/edit own content, submit for review
- **Contributor:** Can create drafts, cannot publish
- **Content Viewer:** Read-only access to CMS

**Implementation:**
- Leverage existing `spatie/laravel-permission` package
- Add CMS-specific permissions:
  - `articles.create`, `articles.edit-own`, `articles.edit-all`, `articles.publish`, `articles.delete`
  - `media.upload`, `media.edit`, `media.delete`

---

## Migration Strategy

### For Existing Articles
1. **Backup First:** `mysqldump articles > articles_backup_$(date +%F).sql`
2. **Gradual Rollout:**
   - Phase 1 changes are additive (new editor, enhanced media)
   - Old system remains functional during transition
3. **Training:**
   - Create video tutorials for clients
   - Document new features in user guide
4. **Feedback Loop:**
   - Beta test with 2-3 power users
   - Iterate based on feedback before full rollout

### For Client Migration from WordPress
1. **Content Import Tool:**
   - WordPress XML export parser
   - Map WP custom fields to Article fields
   - Download and import media library
   - Preserve URLs with redirects

2. **Feature Parity Checklist:**
   - Ensure all WordPress features they use have equivalents
   - Provide migration guide: "How to do X in new CMS"

---

## Technical Considerations

### Performance
- **Eager Loading:** Already implemented well
- **Caching:** 
  - Cache rendered articles: `cache()->remember("article.{$slug}", 3600, ...)`
  - Clear cache on save
  - Cache navigation menus
- **Image Optimization:**
  - Use Intervention Image for thumbnails
  - Consider lazy loading with `loading="lazy"`
  - WebP format generation

### Security
- **File Upload Validation:**
  - Mime type checking (not just extension)
  - Max file size limits
  - Scan for malware (ClamAV)
- **XSS Prevention:**
  - Rich text sanitization (HTML Purifier)
  - Escape output: `{!! $article->description !!}` carefully

### Browser Compatibility
- Test in Chrome, Firefox, Safari, Edge
- Mobile-responsive admin interface
- Touch-friendly drag-and-drop

---

## Implementation Timeline

### Sprint 1 (1 week): Package Scaffolding
- Laravel package structure setup
- Service provider configuration
- CMS v2 database migrations
- Basic model relationships
- Route and config publishing

### Sprint 2 (2 weeks): Enhanced Media Library
- Grid view with thumbnails
- Drag-and-drop upload
- Folder organization system
- Image metadata and processing
- Media browser component

### Sprint 3 (2 weeks): Rich Text Editor
- Tiptap integration
- Media browser integration
- Block-based content editing
- Auto-save functionality
- Formatting toolbar

### Sprint 4 (2 weeks): Content Management
- Content item CRUD interface
- Menu system and navigation
- Template selection
- URL structure and routing

### Sprint 5 (2 weeks): Content Migration Tools
- Migration commands development
- Content validation and integrity checking
- Parallel system operation
- URL fallback routing

### Sprint 6 (2 weeks): Advanced Features
- Content revisions system
- SEO meta fields and preview
- Preview before publish
- Sitemap generation

### Sprint 7 (2 weeks): Package Polish
- Configuration abstraction
- Documentation and guides
- Testing across environments
- WLCM integration and validation

**Total Estimated Time:** 13 weeks (3.25 months)
*+3 weeks for package architecture and migration tools, but eliminates future refactoring work*

---

## Success Metrics

### Quantitative
- **Adoption Rate:** 90% of admin users creating content within 1 month
- **Support Tickets:** <5 CMS-related tickets per month after training
- **Content Creation Speed:** 50% faster page creation vs old system
- **Media Library Growth:** 100+ assets uploaded in first 3 months

### Qualitative
- **User Satisfaction:** "This is as easy as WordPress" feedback
- **Client Retention:** WordPress migrants stay on platform
- **Feature Completeness:** Users don't ask "Can it do what WordPress does?"

---

## Dependencies & Requirements

### Technical Dependencies
- **PHP:** 8.1+ (current Laravel 11 requirement)
- **Laravel:** 11.x (current version)
- **JavaScript:** 
  - AlpineJS (already in use)
  - Tiptap (new)
  - SortableJS (for drag-and-drop, optional)
- **Packages:**
  - `intervention/image` (image manipulation)
  - `spatie/laravel-permission` (already installed)
  - `ueberdosis/tiptap` (rich text editor)

### Server Requirements
- **Storage:** Increased for media library (plan for 10-20GB growth per client)
- **Memory:** PHP memory_limit 256M+ for image processing
- **GD/ImageMagick:** For thumbnail generation

### Training Requirements
- Admin user training: 2-hour session
- Video tutorials: 5-10 short videos (2-5 min each)
- User guide update: 10-15 pages new content

---

## Risks & Mitigation

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Users resist change | High | Medium | Comprehensive training, gradual rollout, keep old system available initially |
| Rich text editor bugs | Medium | Low | Thorough testing, fallback to basic textarea |
| Image processing performance issues | Medium | Medium | Queue-based processing, limit file sizes, CDN for delivery |
| WordPress import edge cases | Medium | High | Manual review process, import dry-run reports |
| Scope creep | High | High | Strict phase boundaries, MVP first, iterate later |

---

## Next Steps

1. **Review & Approval:** Share this plan with stakeholders
2. **Proof of Concept:** Build Tiptap editor + enhanced media grid in isolated branch
3. **User Feedback:** Demo to 2-3 clients, gather feedback
4. **Sprint Planning:** Break Phase 1 into detailed tasks
5. **Kickoff:** Begin Sprint 1 development

---

## Questions for Discussion

1. **Rich Text Editor:** Tiptap vs FilamentPHP Editor vs CKEditor 5 - preference?
2. **Media Folders:** Hierarchical (folders in folders) or flat (tags only)?
3. **Content Revisions:** Keep last 25 or configurable limit?
4. **SEO:** Build custom or integrate Spatie Laravel SEO Tools package?
5. **Menu Builder:** Phase 1 priority or defer to Phase 3?
6. **WordPress Import:** Automatic tool or manual migration with consultants?

---

## Appendix: Current File Structure

### Models
- `app/Models/Article.php` - Core content model
- `app/Models/Media.php` - Media assets
- `app/Models/ArticleParent.php` - Pivot model (implicit)

### Controllers
- `app/Http/Controllers/Admin/ArticleController.php` - CRUD operations
- `app/Http/Controllers/Admin/MediaController.php` - Media management
- `app/Http/Controllers/PagesController.php` - Frontend display

### Views
- `resources/views/admin/articles/` - Admin CRUD interface
- `resources/views/admin/media/` - Media library interface
- `resources/views/pages/show.blade.php` - Frontend article display
- `resources/views/components/navigation-menu-*.blade.php` - Navigation components
- `resources/views/components/tinymce.blade.php` - Current rich text editor

### Migrations
- `database/migrations/*article*.php` - Article schema evolution
- `database/migrations/2022_04_22_121727_create_media_table.php` - Media schema
- `database/migrations/2025_11_21_091719_create_article_parent_table.php` - Pivot table

### Routes
- `routes/web.php` - Contains article and page routes
  - `Route::resource('articles', Admin\ArticleController::class);`
  - `Route::get('/{parent_article?}/{article?}', [PagesController::class, 'show']);`

---

**Document Owner:** Development Team  
**Last Updated:** December 3, 2025  
**Version:** 1.0 - Initial Planning Document
