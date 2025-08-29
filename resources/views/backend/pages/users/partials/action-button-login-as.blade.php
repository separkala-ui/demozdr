@if (auth()->user()->can('user.login_as') && $user->id != auth()->user()->id)
    <x-buttons.action-item
        :href="route('admin.users.login-as', $user->id)"
        icon="lucide:log-in"
        :label="__('Login as')"
    />
@endif