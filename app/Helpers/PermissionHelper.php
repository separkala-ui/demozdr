<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Get translated permission name
     */
    public static function getTranslatedPermission($permissionName)
    {
        $key = "permissions.{$permissionName}";
        $translated = __($key);
        
        // اگر translation key خود را برگرداند (ترجمه نشده)، فقط permission name را برگردان
        if ($translated === $key) {
            return $permissionName;
        }
        
        return $translated;
    }
}
