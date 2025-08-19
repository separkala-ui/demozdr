<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LanguageService;
use App\Services\TimezoneService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly TimezoneService $timezoneService,
    ) {
    }

    public function edit(): Renderable
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        // Load user metadata
        $userMeta = $user->userMeta()->pluck('meta_value', 'meta_key')->toArray();

        // Load localization data
        $locales = $this->languageService->getLanguages();
        $timezones = $this->timezoneService->getTimezones();

        return view('backend.pages.profile.edit', compact('user', 'userMeta', 'locales', 'timezones'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('Edit Profile'),
                ],
            ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Prevent modification of super admin in demo mode.
        $this->preventSuperAdminModification(Auth::user(), ['profile.edit']);

        /**
         * @var User $user
         */
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'avatar_id' => 'nullable|exists:media,id',
        ]);

        $requestInputs = ld_apply_filters('user_profile_update_data_before', [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'avatar_id' => $request->avatar_id,
        ], $user);

        $user->update($requestInputs);

        ld_do_action('user_profile_update_after', $user);

        session()->flash('success', 'Profile updated successfully.');

        $this->storeActionLog(ActionType::UPDATED, ['profile' => $user]);

        return redirect()->route('profile.edit');
    }

    public function updateAdditional(Request $request): RedirectResponse
    {
        // Prevent modification of super admin in demo mode.
        $this->preventSuperAdminModification(Auth::user(), ['profile.edit']);

        /**
         * @var User $user
         */
        $user = Auth::user();

        $request->validate([
            'display_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:100',
            'locale' => 'nullable|string|max:10',
        ]);

        // Update user metadata only.
        $metaFields = ['display_name', 'bio', 'timezone', 'locale'];
        foreach ($metaFields as $field) {
            if ($request->has($field)) {
                $user->userMeta()->updateOrCreate(
                    ['meta_key' => $field],
                    [
                        'meta_value' => $request->input($field) ?? '',
                        'type' => 'string',
                    ]
                );
            }
        }

        // Update social links metadata.
        $socialFields = ['social_facebook', 'social_x', 'social_youtube', 'social_linkedin', 'social_website'];
        foreach ($socialFields as $field) {
            if ($request->has($field)) {
                $user->userMeta()->updateOrCreate(
                    ['meta_key' => $field],
                    [
                        'meta_value' => $request->input($field) ?? '',
                        'type' => 'string',
                    ]
                );
            }
        }

        ld_do_action('user_profile_additional_update_after', $user);

        session()->flash('success', 'Additional information updated successfully.');

        $this->storeActionLog(ActionType::UPDATED, ['profile_additional' => $user]);

        return redirect()->route('profile.edit');
    }
}
