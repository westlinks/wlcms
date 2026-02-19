# WLCMS Critical Issue: Bootstrap Legacy Classes Causing Layout Failures

**To:** WLCMS Development Team  
**From:** Host Application Team  
**Date:** February 17, 2026  
**Priority:** CRITICAL - Production Blocker  
**Issue Type:** Template Layout / CSS Framework Mismatch

---

## Problem Statement

WLCMS templates contain **undefined Bootstrap legacy classes** that cause severe layout issues when integrated with modern Tailwind CSS applications. Content extends offscreen, layouts break, and the integration is unusable.

**Root Cause:** Templates use `class="container"` and other Bootstrap classes that don't exist in Tailwind CSS applications.

---

## Manifestation in Host Application

When CMS pages render with app layout:
- ❌ Content extends beyond viewport boundaries
- ❌ No max-width constraints applied
- ❌ Horizontal scrolling appears
- ❌ Responsive breakpoints don't work
- ❌ Padding/spacing inconsistent with host app

**Example:** Homepage template renders but content flows off the right side of the screen because `.container` class is undefined.

---

## Bootstrap Classes Found in WLCMS Templates

### 1. Undefined Container Class (8 instances)
```bash
# Found in:
- full-width.blade.php (line 2)
- sidebar-right.blade.php (line 32)
- contact-form.blade.php (line 43)
- event-landing-page.blade.php (lines 145, 189)
- event-registration.blade.php (line 2)
- time-limited-content.blade.php (line 2)
- archive-timeline.blade.php (line 2)
```

**Current (Broken):**
```blade
<div class="container">
    <!-- Content here -->
</div>
```

**Required (Tailwind):**
```blade
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Content here -->
</div>
```

### 2. Bootstrap Button Classes
**File:** `event-registration.blade.php` (line 25)

**Current (Bootstrap):**
```blade
<button class="btn btn-primary">Register Now</button>
```

**Required (Tailwind):**
```blade
<button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
    Register Now
</button>
```

### 3. Non-Standard Container Classes
Found but not standard Tailwind:
- `signup-container` (should use Tailwind utilities)
- `contact-form-wrapper` (should use Tailwind utilities)
- `feature-card` (should use Tailwind utilities)

---

## Required Changes

### Change 1: Replace All Bootstrap Containers

**Files to Update (8 total):**
1. `resources/views/templates/full-width.blade.php`
2. `resources/views/templates/sidebar-right.blade.php`
3. `resources/views/templates/contact-form.blade.php`
4. `resources/views/templates/event-landing-page.blade.php` (2 instances)
5. `resources/views/templates/event-registration.blade.php`
6. `resources/views/templates/time-limited-content.blade.php`
7. `resources/views/templates/archive-timeline.blade.php`

**Find and Replace:**
```
FROM: class="container"
TO:   class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
```

### Change 2: Replace Bootstrap Buttons

**File:** `event-registration.blade.php`

**FROM:**
```blade
<button class="btn btn-primary"
        style="padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1.125rem; font-weight: 600; cursor: pointer;">
    Register Now
</button>
```

**TO:**
```blade
<button class="px-8 py-4 bg-blue-600 text-white rounded-lg text-lg font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-lg hover:shadow-xl">
    Register Now
</button>
```

### Change 3: Remove Custom Container Classes

Replace with Tailwind utility classes:

**FROM:**
```blade
<div class="signup-container" style="max-width: 600px; width: 100%; background: white; border-radius: 12px; padding: 3rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
```

**TO:**
```blade
<div class="max-w-xl w-full bg-white rounded-xl p-12 shadow-2xl">
```

---

## Why This Matters

### 1. **Framework Consistency**
- Host applications use Tailwind CSS (industry standard since 2019)
- Bootstrap classes don't exist and have no effect
- Creates confusion and maintenance burden

### 2. **Layout Integrity**
- Undefined classes = no styling = broken layouts
- Production sites cannot launch with broken layouts
- User experience is severely degraded

### 3. **Maintainability**
- Mixing frameworks = technical debt
- Inline styles + undefined classes = unmaintainable code
- Future developers will struggle to understand intent

### 4. **Best Practices**
- Modern Laravel ecosystem uses Tailwind
- Jetstream, Breeze, Livewire all use Tailwind
- WLCMS should align with Laravel ecosystem standards

---

## Testing Requirements

### Before Changes
```bash
# Navigate to test page
# Result: Content extends offscreen, horizontal scroll
```

### After Changes
```bash
# Navigate to test page
# Result: 
# ✅ Content contained within 7xl container
# ✅ Proper responsive padding
# ✅ No horizontal scroll
# ✅ Matches host app layout width
# ✅ Mobile responsive
```

### Test All Templates
- [ ] full-width.blade.php
- [ ] sidebar-right.blade.php
- [ ] contact-form.blade.php
- [ ] event-landing-page.blade.php
- [ ] event-registration.blade.php
- [ ] signup-form-page.blade.php
- [ ] time-limited-content.blade.php
- [ ] archive-timeline.blade.php

---

## Additional Considerations

### Global Audit Needed

Please audit **ALL** WLCMS files for:
1. **Bootstrap classes:** `btn`, `btn-*`, `container`, `row`, `col-*`, `navbar`, `card`, `modal`, `alert`
2. **Custom container classes** that should use Tailwind
3. **Inline styles** that duplicate Tailwind utilities
4. **Non-Tailwind spacing** (e.g., `margin: 1rem` vs `mb-4`)

### Style Consolidation

Many templates have inline styles that should be Tailwind classes:

**Example FROM:**
```blade
<div style="padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb;">
```

**Example TO:**
```blade
<div class="p-6 rounded-lg border border-gray-200">
```

---

## Tailwind Container Pattern

For WLCMS templates integrating with host apps, use this pattern:

```blade
<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Template content here -->
    </div>
</x-dynamic-component>
```

**Why this pattern:**
- `max-w-7xl` - Standard Laravel container width (1280px)
- `mx-auto` - Centers container horizontally
- `px-4 sm:px-6 lg:px-8` - Responsive horizontal padding
- `py-8` - Vertical padding for breathing room

**Responsive behavior:**
- Mobile: 1rem (16px) padding
- Tablet: 1.5rem (24px) padding
- Desktop: 2rem (32px) padding
- Max width: 80rem (1280px)

---

## Impact Assessment

### High Priority (Blocks Production)
- ✅ Phase 6.6 template modernization (DONE)
- ❌ Bootstrap class removal (BLOCKING)
- ❌ Container class fixes (BLOCKING)

### Medium Priority (User Experience)
- Button styling consistency
- Inline style consolidation
- Custom class audit

### Low Priority (Technical Debt)
- Full Tailwind migration for all components
- Style guide documentation
- Component library with Tailwind utilities

---

## Timeline Request

**Urgency:** CRITICAL - Cannot deploy to production

**Minimum Viable Fix (deploy blocker):**
- Replace all `class="container"` with Tailwind container pattern
- Test all 8 templates render without layout issues
- **Timeline needed:** 1-2 days

**Complete Bootstrap Removal:**
- Full audit and replacement of all Bootstrap classes
- Inline style consolidation
- **Timeline needed:** 1 week

---

## Files Requiring Changes

### Template Files (8)
```
resources/views/templates/
├── archive-timeline.blade.php      - 1 container class
├── contact-form.blade.php          - 1 container class
├── event-landing-page.blade.php    - 2 container classes
├── event-registration.blade.php    - 1 container + btn classes
├── full-width.blade.php            - 1 container class
├── sidebar-right.blade.php         - 1 container class
├── signup-form-page.blade.php      - 1 custom container
└── time-limited-content.blade.php  - 1 container class
```

### Affected Lines
- 8 template files
- ~10 total instances of Bootstrap/legacy classes
- ~50 lines of code requiring updates

---

## Success Criteria

After fixes are deployed:

### Visual Testing
- [ ] All templates render within viewport bounds
- [ ] No horizontal scrolling on any screen size
- [ ] Content width matches host app standards
- [ ] Responsive padding works correctly
- [ ] Mobile/tablet/desktop layouts correct

### Code Quality
- [ ] No Bootstrap classes remain
- [ ] All styling uses Tailwind utilities
- [ ] Inline styles minimized or eliminated
- [ ] Container pattern consistent across templates

### Integration Testing
- [ ] Templates integrate cleanly with host app layout
- [ ] Navigation visible and functional
- [ ] Page feels cohesive with rest of application
- [ ] No CSS framework conflicts

---

## Questions for WLCMS Team

1. **Timeline:** When can these fixes be implemented?
2. **Scope:** Should we do minimal fix or full Bootstrap removal?
3. **Testing:** Do you have visual regression tests for templates?
4. **Standards:** Should we create a Tailwind style guide for WLCMS?
5. **Migration:** Are there other packages using Bootstrap that need updates?

---

## Workaround We Will NOT Do

❌ **Publishing views to host app** - Creates maintenance nightmare  
❌ **Adding Bootstrap CSS** - Framework conflict, bloats app  
❌ **Inline style overrides** - Band-aid, not a solution  
❌ **Custom CSS classes** - Defeats purpose of utility framework  

✅ **Proper Solution:** Fix the WLCMS package templates with Tailwind classes

---

**Current Status:** Integration blocked by Bootstrap legacy classes  
**Blocker Type:** Template CSS framework mismatch  
**Required Action:** Replace Bootstrap classes with Tailwind utilities  
**Expected Resolution:** 1-2 days for critical fix

---

We need WLCMS to be a **first-class CMS** with modern, maintainable templates. Bootstrap legacy classes prevent this goal.

**Submitted:** February 17, 2026  
**Team:** Host Application Integration  
**Awaiting:** WLCMS Team Response and Timeline
