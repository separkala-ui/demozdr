<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Get translated permission name
     */
    public static function getTranslatedPermission($permissionName)
    {
        // ابتدا بررسی کنید آیا translation موجود است
        $key = "permissions.{$permissionName}";
        
        // استفاده از trans() function به جای __()
        $translated = trans($key);
        
        // اگر translation key خود را برگرداند (ترجمه نشده)، فقط permission name را برگردان
        if ($translated === $key) {
            return $permissionName;
        }
        
        return $translated;
    }

    /**
     * Get all permission translations
     */
    public static function getAllPermissionTranslations()
    {
        return trans('permissions');
    }
}
