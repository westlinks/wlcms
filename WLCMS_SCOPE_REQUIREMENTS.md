# WLCMS Package Enhancement Requirements
## Add Query Scopes to ContentItem Model

### Overview
Add two additional query scopes to the `ContentItem` model to improve content retrieval flexibility for consuming applications.

### Current State
The `ContentItem` model already has:
- ✅ `scopePublished()` - Filters published content
- ✅ `scopeOrdered()` - Orders by sort_order and title
- ✅ `scopeTopLevel()` - Filters top-level items (no parent)

### Required Additions

#### 1. Add `scopeBySlug()` Method
**Purpose:** Easily retrieve content by its URL slug

**Location:** `/src/Models/ContentItem.php`

**Code to add:**
```php
/**
 * Scope to find content by slug.
 */
public function scopeBySlug(Builder $query, string $slug): Builder
{
    return $query->where('slug', $slug);
}
```

**Usage example:**
```php
$content = ContentItem::bySlug('credit-card-orders-guide')->published()->first();
```

---

#### 2. Add `scopeOfType()` Method
**Purpose:** Filter content by type (page, post, article, etc.)

**Location:** `/src/Models/ContentItem.php`

**Code to add:**
```php
/**
 * Scope to filter content by type.
 */
public function scopeOfType(Builder $query, string $type): Builder
{
    return $query->where('type', $type);
}
```

**Usage example:**
```php
$pages = ContentItem::ofType('page')->published()->get();
$helpDocs = ContentItem::ofType('help')->published()->ordered()->get();
```

---

### Placement in File
Add these methods after the existing `scopeTopLevel()` method (around line 103), keeping all scope methods together.

### Benefits
1. **Cleaner queries** - `ContentItem::bySlug('my-page')` instead of `ContentItem::where('slug', 'my-page')`
2. **Chainable** - Works with other scopes: `bySlug('x')->published()->first()`
3. **Consistent API** - Follows Laravel conventions and existing scope patterns
4. **Type safety** - Type hints ensure correct usage
5. **Flexibility** - Apps can combine scopes as needed without package modifications

### Testing Recommendations
```php
// Test bySlug
$item = ContentItem::bySlug('test-slug')->first();

// Test ofType
$pages = ContentItem::ofType('page')->get();

// Test chaining
$publishedPage = ContentItem::bySlug('about')->ofType('page')->published()->first();
```

### Migration Impact
✅ No database changes required
✅ No breaking changes
✅ Backward compatible with existing code
