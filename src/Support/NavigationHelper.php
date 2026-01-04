<?php

namespace Westlinks\Wlcms\Support;

class NavigationHelper
{
    /**
     * Get navigation items for integration with host applications
     */
    public static function getNavigationItems(): array
    {
        return config('wlcms.navigation.items', []);
    }

    /**
     * Get navigation items formatted for Laravel Nova
     */
    public static function getNovaNavigationItems(): array
    {
        $items = static::getNavigationItems();
        $nova = [];

        foreach ($items as $item) {
            $nova[] = [
                'name' => $item['label'],
                'path' => route($item['route']),
                'icon' => static::mapIcon($item['icon'], 'nova'),
            ];
        }

        return $nova;
    }

    /**
     * Get navigation items formatted for Filament
     */
    public static function getFilamentNavigationItems(): array
    {
        $items = static::getNavigationItems();
        $filament = [];

        foreach ($items as $item) {
            $filament[] = [
                'label' => $item['label'],
                'url' => route($item['route']),
                'icon' => static::mapIcon($item['icon'], 'filament'),
                'group' => config('wlcms.navigation.integrations.filament.group', 'Content'),
                'sort' => config('wlcms.navigation.integrations.filament.sort', 100) + count($filament),
            ];
        }

        return $filament;
    }

    /**
     * Get navigation items formatted for custom admin interfaces
     */
    public static function getCustomNavigationItems(): array
    {
        $items = static::getNavigationItems();
        $custom = [];

        foreach ($items as $item) {
            $custom[] = [
                'label' => $item['label'],
                'route' => $item['route'],
                'url' => route($item['route']),
                'icon' => static::mapIcon($item['icon']),
                'permission' => $item['permission'] ?? null,
                'description' => $item['description'] ?? null,
                'children' => $item['children'] ?? [],
                'wrapper_class' => config('wlcms.navigation.integrations.custom.wrapper_class', 'nav-item'),
            ];
        }

        return $custom;
    }

    /**
     * Map icon names to different icon libraries
     */
    public static function mapIcon(string $iconName, string $library = null): string
    {
        $library = $library ?: config('wlcms.navigation.icons.type', 'heroicons');
        $mappings = config('wlcms.navigation.icons.mappings', []);

        if (isset($mappings[$library][$iconName])) {
            $mapped = $mappings[$library][$iconName];
            $prefix = config('wlcms.navigation.icons.class_prefix', '');
            
            return $prefix . $mapped;
        }

        return $iconName;
    }

    /**
     * Generate navigation HTML for custom admin interfaces
     */
    public static function renderCustomNavigation(array $options = []): string
    {
        $items = static::getCustomNavigationItems();
        $html = '';

        $wrapperClass = $options['wrapper_class'] ?? 'nav-group';
        $itemClass = $options['item_class'] ?? 'nav-item';
        $linkClass = $options['link_class'] ?? 'nav-link';

        $html .= "<div class=\"{$wrapperClass}\">\n";

        foreach ($items as $item) {
            $isActive = request()->routeIs($item['route']) ? 'active' : '';
            $icon = $item['icon'];

            $html .= "  <div class=\"{$item['wrapper_class']} {$isActive}\">\n";
            $html .= "    <a href=\"{$item['url']}\" class=\"{$linkClass}\">\n";
            
            if ($icon) {
                $html .= "      <i class=\"{$icon}\"></i>\n";
            }
            
            $html .= "      <span>{$item['label']}</span>\n";
            $html .= "    </a>\n";

            // Handle sub-navigation
            if (!empty($item['children'])) {
                $html .= "    <div class=\"nav-children\">\n";
                foreach ($item['children'] as $child) {
                    $childActive = request()->routeIs($child['route']) ? 'active' : '';
                    $html .= "      <a href=\"" . route($child['route']) . "\" class=\"nav-child-link {$childActive}\">\n";
                    $html .= "        <span>{$child['label']}</span>\n";
                    $html .= "      </a>\n";
                }
                $html .= "    </div>\n";
            }

            $html .= "  </div>\n";
        }

        $html .= "</div>\n";

        return $html;
    }

    /**
     * Get permissions list for integration with permission systems
     */
    public static function getPermissions(): array
    {
        return config('wlcms.navigation.permissions', []);
    }

    /**
     * Check if navigation integration is enabled
     */
    public static function isNavigationIntegrationEnabled(): bool
    {
        return config('wlcms.layout.navigation_integration', false);
    }

    /**
     * Check if we're in embedded layout mode
     */
    public static function isEmbeddedMode(): bool
    {
        return config('wlcms.layout.mode') === 'embedded';
    }
}