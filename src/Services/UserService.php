<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Database\Eloquent\Model;

class UserService
{
    /**
     * Get the display name for a user based on configuration.
     */
    public static function getDisplayName(?Model $user): string
    {
        if (!$user) {
            return 'Unknown User';
        }

        $config = config('wlcms.user.display_name');
        
        if (!$config) {
            return 'User #' . $user->getKey();
        }

        return match ($config['type']) {
            'field' => $user->{$config['field']} ?? 'Unknown',
            
            'fields' => collect($config['fields'])
                ->map(fn($field) => $user->{$field} ?? '')
                ->filter()
                ->join($config['separator'] ?? ' '),
            
            'method' => method_exists($user, $config['method']) 
                ? $user->{$config['method']}() 
                : 'Unknown',
            
            'format' => self::formatUserName($user, $config['format']),
            
            default => 'User #' . $user->getKey(),
        };
    }

    /**
     * Format user name using a template string like "{firstName} {lastName}".
     */
    protected static function formatUserName(Model $user, string $format): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($user) {
            $field = $matches[1];
            return $user->{$field} ?? '';
        }, $format);
    }

    /**
     * Get the user's avatar URL.
     */
    public static function getAvatarUrl(?Model $user): ?string
    {
        if (!$user) {
            return null;
        }

        $avatarField = config('wlcms.user.avatar_field');
        
        if (!$avatarField || !isset($user->{$avatarField})) {
            return null;
        }

        $avatar = $user->{$avatarField};
        
        // If it's already a full URL, return it
        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }
        
        // If it's a storage path, convert to URL
        if ($avatar && \Storage::exists($avatar)) {
            return \Storage::url($avatar);
        }
        
        return null;
    }

    /**
     * Get the user's email address.
     */
    public static function getEmail(?Model $user): ?string
    {
        if (!$user) {
            return null;
        }

        $emailField = config('wlcms.user.email_field', 'email');
        
        return $user->{$emailField} ?? null;
    }

    /**
     * Get the configured User model class.
     */
    public static function getUserModelClass(): ?string
    {
        return config('wlcms.user.model');
    }

    /**
     * Check if user integration is enabled.
     */
    public static function isUserIntegrationEnabled(): bool
    {
        return self::getUserModelClass() !== null;
    }

    /**
     * Get a user instance by ID.
     */
    public static function findUser($id): ?Model
    {
        $userModel = self::getUserModelClass();
        
        if (!$userModel || !$id) {
            return null;
        }

        try {
            return $userModel::find($id);
        } catch (\Exception $e) {
            return null;
        }
    }
}