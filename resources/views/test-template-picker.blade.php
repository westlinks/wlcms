<!DOCTYPE html>
<html>
<head>
    <title>WLCMS Template Picker Test</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: system-ui, sans-serif; padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .status { padding: 1rem; margin: 1rem 0; border-radius: 0.5rem; }
        .success { background: #d1fae5; color: #065f46; }
        .info { background: #dbeafe; color: #1e40af; }
        pre { background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>WLCMS Template Picker Diagnostic</h1>
    
    <div class="status success">
        <strong>✓ Alpine.js Status:</strong>
        <div x-data="{ test: 'Alpine.js is working!' }">
            <p x-text="test"></p>
        </div>
    </div>

    <div class="status info">
        <strong>Templates in Database:</strong>
        <pre><?php
        try {
            $templates = \Westlinks\Wlcms\Models\Template::all();
            echo "Count: " . $templates->count() . "\n\n";
            foreach ($templates as $t) {
                echo "- {$t->identifier}: {$t->name}\n";
                echo "  Features: " . implode(', ', $t->features ?? []) . "\n";
                echo "  Zones: " . count($t->zones ?? []) . "\n\n";
            }
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage();
        }
        ?></pre>
    </div>

    <div class="status info">
        <strong>Files Check:</strong>
        <pre><?php
        $files = [
            'Template Picker Component' => resource_path('../vendor/westlinks/wlcms/resources/views/admin/components/template-picker.blade.php'),
            'Create Form' => resource_path('../vendor/westlinks/wlcms/resources/views/admin/content/create.blade.php'),
            'Edit Form' => resource_path('../vendor/westlinks/wlcms/resources/views/admin/content/edit.blade.php'),
        ];
        
        foreach ($files as $name => $path) {
            $exists = file_exists($path);
            $size = $exists ? filesize($path) : 0;
            echo "{$name}: " . ($exists ? "✓ EXISTS ({$size} bytes)" : "✗ MISSING") . "\n";
        }
        ?></pre>
    </div>

    <div class="status info">
        <strong>Symlink Status:</strong>
        <pre><?php
        $vendorPath = base_path('vendor/westlinks/wlcms');
        $isSymlink = is_link($vendorPath);
        $target = $isSymlink ? readlink($vendorPath) : 'N/A';
        
        echo "Path: {$vendorPath}\n";
        echo "Is Symlink: " . ($isSymlink ? "YES" : "NO") . "\n";
        if ($isSymlink) {
            echo "Target: {$target}\n";
            echo "Resolved: " . realpath($vendorPath) . "\n";
        }
        ?></pre>
    </div>

    <h2>Test Template Picker Component</h2>
    <div style="border: 2px solid #e5e7eb; padding: 2rem; border-radius: 0.5rem; background: white;">
        @include('wlcms::admin.components.template-picker', [
            'name' => 'test_template',
            'selected' => null,
            'label' => 'Test Template Selection'
        ])
    </div>

    <div style="margin-top: 2rem; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
        <strong>Next Steps:</strong>
        <ol>
            <li>Check if Alpine.js message shows above (should say "Alpine.js is working!")</li>
            <li>Verify 4 templates are listed</li>
            <li>Check all files exist</li>
            <li>Click "Select Template" button to test the modal</li>
            <li>If everything works here, check browser cache on actual admin pages</li>
        </ol>
    </div>

    <div style="margin-top: 1rem; padding: 1rem; background: #e0e7ff; border-radius: 0.5rem;">
        <strong>Admin URLs:</strong>
        <ul>
            <li><a href="/admin/cms/content/create" target="_blank">/admin/cms/content/create</a> (WLCMS - NEW)</li>
            <li><a href="/admin/cms/content" target="_blank">/admin/cms/content</a> (Content List)</li>
        </ul>
    </div>
</body>
</html>
