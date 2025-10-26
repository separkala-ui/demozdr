<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertSetting extends Model
{
    protected $fillable = [
        'key',
        'category',
        'type',
        'value',
        'title_fa',
        'description_fa',
        'title_en',
        'description_en',
        'is_active',
        'is_editable',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_editable' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * دریافت مقدار تنظیمات با پارس JSON
     */
    public function getValueAttribute($value)
    {
        $decoded = json_decode($value, true);
        return $decoded ?? $value;
    }

    /**
     * ذخیره مقدار با تبدیل به JSON
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * دریافت مقدار تنظیمات با کلید
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * تنظیم مقدار با کلید
     */
    public static function setValue(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();
        
        if ($setting) {
            $setting->value = $value;
            return $setting->save();
        }
        
        return false;
    }

    /**
     * دریافت تنظیمات بر اساس دسته‌بندی
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Scope برای تنظیمات فعال
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope برای تنظیمات قابل ویرایش
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }
}
