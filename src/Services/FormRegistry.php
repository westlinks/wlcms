<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Support\Collection;

class FormRegistry
{
    /**
     * Registered forms.
     *
     * @var array
     */
    protected array $forms = [];

    /**
     * Register a form.
     *
     * @param string $identifier Unique form identifier
     * @param array $config Form configuration
     * @return void
     */
    public function register(string $identifier, array $config): void
    {
        $this->forms[$identifier] = array_merge([
            'identifier' => $identifier,
            'name' => $config['name'] ?? ucfirst($identifier),
            'type' => $config['type'] ?? 'built-in', // built-in, custom, external
            'fields' => $config['fields'] ?? [],
            'settings' => $config['settings'] ?? [],
            'view' => $config['view'] ?? null,
            'handler' => $config['handler'] ?? null,
            'validation' => $config['validation'] ?? [],
            'redirect_url' => $config['redirect_url'] ?? null,
            'success_message' => $config['success_message'] ?? 'Thank you for your submission!',
            'thank_you_title' => $config['thank_you_title'] ?? 'Thank You!',
            'thank_you_content' => $config['thank_you_content'] ?? '<p>Your submission has been received. We\'ll get back to you soon.</p>',
            'description' => $config['description'] ?? '',
        ], $config);
    }

    /**
     * Get a registered form by identifier.
     *
     * @param string $identifier
     * @return array|null
     */
    public function get(string $identifier): ?array
    {
        $form = $this->forms[$identifier] ?? null;
        
        if (!$form) {
            return null;
        }
        
        // Check for cached overrides from admin panel
        $overrides = cache()->get("wlcms.form.{$identifier}.config", []);
        
        return array_merge($form, $overrides);
    }

    /**
     * Get all registered forms.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return collect($this->forms);
    }

    /**
     * Check if a form is registered.
     *
     * @param string $identifier
     * @return bool
     */
    public function has(string $identifier): bool
    {
        return isset($this->forms[$identifier]);
    }

    /**
     * Get forms by type.
     *
     * @param string $type
     * @return Collection
     */
    public function byType(string $type): Collection
    {
        return $this->all()->filter(function ($form) use ($type) {
            return $form['type'] === $type;
        });
    }

    /**
     * Unregister a form.
     *
     * @param string $identifier
     * @return void
     */
    public function unregister(string $identifier): void
    {
        unset($this->forms[$identifier]);
    }

    /**
     * Get form fields definition.
     *
     * @param string $identifier
     * @return array
     */
    public function getFields(string $identifier): array
    {
        $form = $this->get($identifier);
        return $form['fields'] ?? [];
    }

    /**
     * Get form validation rules.
     *
     * @param string $identifier
     * @return array
     */
    public function getValidation(string $identifier): array
    {
        $form = $this->get($identifier);
        return $form['validation'] ?? [];
    }

    /**
     * Get form settings.
     *
     * @param string $identifier
     * @return array
     */
    public function getSettings(string $identifier): array
    {
        $form = $this->get($identifier);
        return $form['settings'] ?? [];
    }
}
