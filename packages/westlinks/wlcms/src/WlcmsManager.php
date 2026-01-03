<?php

namespace Westlinks\Wlcms;

class WlcmsManager
{
    /**
     * Get the package version.
     */
    public function version(): string
    {
        return '1.0.0';
    }

    /**
     * Check if the package is properly installed.
     */
    public function isInstalled(): bool
    {
        return file_exists(config_path('wlcms.php'));
    }

    /**
     * Get the package configuration.
     */
    public function config(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('wlcms');
        }

        return config("wlcms.{$key}", $default);
    }

    /**
     * Get the admin route prefix.
     */
    public function adminPrefix(): string
    {
        return $this->config('admin.prefix', 'admin/cms');
    }

    /**
     * Get the admin middleware.
     */
    public function adminMiddleware(): array
    {
        return $this->config('admin.middleware', ['web', 'auth']);
    }
}