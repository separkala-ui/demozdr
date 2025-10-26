<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Enums\Hooks\PostFilterHook;
use App\Http\Requests\FormRequest;
use App\Support\Facades\Hook;
use Illuminate\Support\Str;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the controller using policies.
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize meta keys by slugifying them
        if ($this->has('meta_keys')) {
            $metaKeys = $this->input('meta_keys', []);
            // Ensure $metaKeys is always an array
            $metaKeys = is_array($metaKeys) ? $metaKeys : [];
            $sanitizedKeys = array_map(function ($key) {
                return ! empty($key) ? Str::slug($key, '_') : $key;
            }, $metaKeys);

            $this->merge([
                'meta_keys' => $sanitizedKeys,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $postStatuses = implode(',', array_map(fn ($status) => $status->value, \App\Enums\PostStatus::cases()));

        return Hook::applyFilters(PostFilterHook::POST_STORE_VALIDATION_RULES, [
            /** @example "How to Build a Laravel Application" */
            'title' => 'required|string|max:255',

            /** @example "how-to-build-a-laravel-application" */
            'slug' => 'nullable|string|max:255|unique:posts',

            /** @example "<p>This is a comprehensive guide to building Laravel applications...</p>" */
            'content' => 'nullable|string',

            /** @example "Learn the fundamentals of building Laravel applications from scratch." */
            'excerpt' => 'nullable|string',

            /** @example "publish" */
            'status' => 'required|in:' . $postStatuses,

            /** @example null */
            'parent_id' => 'nullable|exists:posts,id',

            /** @example null */
            'published_at' => 'nullable|date',

            /** Featured image - accepts both file uploads and media library IDs */
            'featured_image' => 'nullable',

            /** @example "seo_keywords" */
            'meta_keys.*' => 'nullable|string|max:255|regex:/^[a-z0-9_]+$/',

            /** @example "laravel, php, web development" */
            'meta_values.*' => 'nullable|string',

            /** @example "textarea" */
            'meta_types.*' => 'nullable|string|in:input,textarea,number,email,url,text,date,checkbox,select',

            /** @example "laravel" */
            'meta_default_values.*' => 'nullable|string',
        ]);
    }
}
