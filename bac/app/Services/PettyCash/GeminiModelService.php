<?php

namespace App\Services\PettyCash;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiModelService
{
    private const CACHE_KEY = 'gemini_models_list';
    private const CACHE_TTL = 86400; // 24 hours

    public function getRecommendedModels(): array
    {
        // Try to get from cache first
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        // If API key is set, try to fetch from API
        $apiKey = config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'));
        if ($apiKey) {
            try {
                $models = $this->fetchModelsFromApi($apiKey);
                if (!empty($models)) {
                    // Filter duplicates and cache
                    $filteredModels = $this->filterDuplicateModels($models);
                    Cache::put(self::CACHE_KEY, $filteredModels, self::CACHE_TTL);
                    return $filteredModels;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch Gemini models from API', ['error' => $e->getMessage()]);
            }
        }

        // Return default models as fallback
        $defaultModels = $this->getDefaultModels();
        Cache::put(self::CACHE_KEY, $defaultModels, self::CACHE_TTL);
        return $defaultModels;
    }

    private function fetchModelsFromApi(string $apiKey): array
    {
        try {
            $response = Http::timeout(10)
                ->get("https://generativelanguage.googleapis.com/v1beta/models", [
                    'key' => $apiKey
                ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $models = [];

            foreach ($data['models'] ?? [] as $model) {
                // Filter only Gemini models with vision capability
                if (str_starts_with($model['name'], 'models/gemini-') && 
                    in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
                    
                    $modelName = str_replace('models/', '', $model['name']);
                    $models[] = [
                        'name' => $modelName,
                        'display_name' => $this->getDisplayName($modelName),
                        'description' => $this->getModelDescription($modelName),
                        'capabilities' => $this->extractCapabilities($model),
                        'recommended' => $this->isRecommended($modelName),
                    ];
                }
            }

            // Sort by recommendation
            usort($models, fn($a, $b) => ($b['recommended'] ?? false) <=> ($a['recommended'] ?? false));

            return $models;

        } catch (\Exception $e) {
            Log::error('Error fetching Gemini models', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function filterDuplicateModels(array $models): array
    {
        $seenDisplayNames = [];
        $filtered = [];
        
        foreach ($models as $model) {
            $displayName = $this->getDisplayName($model['name'] ?? '');
            
            // Skip if we've already seen this display name
            if (in_array($displayName, $seenDisplayNames)) {
                continue;
            }
            
            // Add to filtered list and mark as seen
            $filtered[] = [
                'name' => $model['name'] ?? '',
                'display_name' => $displayName,
                'description' => $this->getModelDescription($model['name'] ?? ''),
                'capabilities' => $this->getModelCapabilities($model['name'] ?? ''),
                'recommended' => $this->isRecommended($model['name'] ?? ''),
            ];
            
            $seenDisplayNames[] = $displayName;
        }
        
        // Sort by priority
        usort($filtered, function($a, $b) {
            $priorityA = $this->getModelPriority($a['name']);
            $priorityB = $this->getModelPriority($b['name']);
            return $priorityB <=> $priorityA;
        });
        
        return $filtered;
    }

    private function getDefaultModels(): array
    {
        return [
            [
                'name' => 'gemini-2.5-flash',
                'display_name' => 'Gemini 2.5 Flash',
                'description' => 'بهترین گزینه برای استخراج فاکتور - سریع و دقیق',
                'capabilities' => ['Vision', 'Text', 'Fast', 'Multimodal'],
                'recommended' => true,
            ],
            [
                'name' => 'gemini-2.0-flash-exp',
                'display_name' => 'Gemini 2.0 Flash (Experimental)',
                'description' => 'نسخه آزمایشی با قابلیت‌های پیشرفته',
                'capabilities' => ['Vision', 'Text', 'Fast', 'Experimental'],
                'recommended' => true,
            ],
            [
                'name' => 'gemini-1.5-flash',
                'display_name' => 'Gemini 1.5 Flash',
                'description' => 'گزینه پایدار و سریع',
                'capabilities' => ['Vision', 'Text', 'Stable'],
                'recommended' => false,
            ],
            [
                'name' => 'gemini-1.5-pro',
                'display_name' => 'Gemini 1.5 Pro',
                'description' => 'دقت بالا برای فاکتورهای پیچیده',
                'capabilities' => ['Vision', 'Text', 'High Accuracy'],
                'recommended' => false,
            ],
        ];
    }

    private function getDisplayName(string $modelName): string
    {
        return match (true) {
            str_contains($modelName, 'gemini-2.5-flash') && str_contains($modelName, 'preview') => 'Gemini 2.5 Flash (Preview)',
            str_contains($modelName, 'gemini-2.5-flash') && str_contains($modelName, 'lite') => 'Gemini 2.5 Flash Lite',
            str_contains($modelName, 'gemini-2.5-flash') => 'Gemini 2.5 Flash',
            str_contains($modelName, 'gemini-2.5-pro') && str_contains($modelName, 'preview') => 'Gemini 2.5 Pro (Preview)',
            str_contains($modelName, 'gemini-2.5-pro') => 'Gemini 2.5 Pro',
            str_contains($modelName, 'gemini-2.0-flash-exp') => 'Gemini 2.0 Flash (Experimental)',
            str_contains($modelName, 'gemini-2.0-flash') && str_contains($modelName, 'lite') => 'Gemini 2.0 Flash Lite',
            str_contains($modelName, 'gemini-2.0-flash') => 'Gemini 2.0 Flash',
            str_contains($modelName, 'gemini-2.0-pro') => 'Gemini 2.0 Pro',
            str_contains($modelName, 'gemini-1.5-pro') => 'Gemini 1.5 Pro',
            str_contains($modelName, 'gemini-1.5-flash') => 'Gemini 1.5 Flash',
            str_contains($modelName, 'gemini-1.0-pro') => 'Gemini 1.0 Pro',
            default => ucwords(str_replace(['-', '_'], ' ', $modelName))
        };
    }

    private function getModelDescription(string $modelName): string
    {
        return match (true) {
            str_contains($modelName, 'gemini-2.5-flash') => 'نسخه جدید و سریع با قابلیت‌های پیشرفته multimodal',
            str_contains($modelName, 'gemini-2.5-pro') => 'نسخه حرفه‌ای با دقت بالا برای کارهای پیچیده',
            str_contains($modelName, 'gemini-2.0-flash-exp') => 'نسخه آزمایشی با قابلیت‌های جدید',
            str_contains($modelName, 'gemini-2.0-flash') => 'نسخه سریع و کارآمد',
            str_contains($modelName, 'gemini-2.0-pro') => 'نسخه حرفه‌ای با دقت بالا',
            str_contains($modelName, 'gemini-1.5-pro') => 'نسخه حرفه‌ای با دقت بالا',
            str_contains($modelName, 'gemini-1.5-flash') => 'نسخه سریع و پایدار',
            str_contains($modelName, 'gemini-1.0-pro') => 'نسخه پایه حرفه‌ای',
            default => 'مدل Gemini برای استخراج اطلاعات'
        };
    }

    private function extractCapabilities(array $model): array
    {
        $capabilities = [];
        
        if (in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
            $capabilities[] = 'Text';
        }
        
        if (str_contains($model['description'] ?? '', 'vision') || 
            str_contains($model['description'] ?? '', 'image') ||
            str_contains($model['name'], 'vision')) {
            $capabilities[] = 'Vision';
        }
        
        if (str_contains(strtolower($model['name']), 'flash')) {
            $capabilities[] = 'Fast';
        }
        
        if (str_contains(strtolower($model['name']), 'pro')) {
            $capabilities[] = 'High Accuracy';
        }

        if (str_contains(strtolower($model['name']), '2.5') || str_contains(strtolower($model['name']), '2.0')) {
            $capabilities[] = 'Multimodal';
        }

        return $capabilities;
    }

    private function isRecommended(string $modelName): bool
    {
        $recommended = [
            'gemini-2.5-flash',
            'gemini-2.0-flash-exp',
            'gemini-1.5-flash',
        ];

        return in_array($modelName, $recommended);
    }

    public function refreshModels(): array
    {
        Cache::forget(self::CACHE_KEY);
        return $this->getRecommendedModels();
    }

    public function getAvailableModels(): array
    {
        return $this->getRecommendedModels();
    }

    private function getModelCapabilities(string $modelName): array
    {
        $capabilities = ['Text'];
        
        // Vision capabilities
        if (str_contains($modelName, 'gemini-2.5') || 
            str_contains($modelName, 'gemini-2.0') || 
            str_contains($modelName, 'gemini-1.5')) {
            $capabilities[] = 'Vision';
        }
        
        // Speed indicators
        if (str_contains($modelName, 'flash')) {
            $capabilities[] = 'Fast';
        }
        
        // Multimodal capabilities
        if (str_contains($modelName, 'gemini-2.5') || str_contains($modelName, 'gemini-2.0')) {
            $capabilities[] = 'Multimodal';
        }
        
        // Experimental features
        if (str_contains($modelName, 'exp') || str_contains($modelName, 'experimental')) {
            $capabilities[] = 'Experimental';
        }
        
        // High accuracy
        if (str_contains($modelName, 'pro')) {
            $capabilities[] = 'High Accuracy';
        }
        
        // Stability
        if (str_contains($modelName, 'gemini-1.5')) {
            $capabilities[] = 'Stable';
        }
        
        return array_unique($capabilities);
    }

    private function getModelPriority(string $modelName): int
    {
        return match (true) {
            str_contains($modelName, 'gemini-2.5-flash') => 100,
            str_contains($modelName, 'gemini-2.5-pro') => 90,
            str_contains($modelName, 'gemini-2.0-flash-exp') => 80,
            str_contains($modelName, 'gemini-2.0-flash') => 70,
            str_contains($modelName, 'gemini-2.0-pro') => 60,
            str_contains($modelName, 'gemini-1.5-pro') => 50,
            str_contains($modelName, 'gemini-1.5-flash') => 40,
            str_contains($modelName, 'gemini-1.0-pro') => 30,
            default => 10
        };
    }
}
