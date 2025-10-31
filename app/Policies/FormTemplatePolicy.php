<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DynamicForms\FormTemplate;
use App\Models\User;

class FormTemplatePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'form.view');
    }

    public function view(User $user, FormTemplate $formTemplate): bool
    {
        return $this->checkPermission($user, 'form.view');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'form.create');
    }

    public function update(User $user, FormTemplate $formTemplate): bool
    {
        return $this->checkPermission($user, 'form.create');
    }

    public function delete(User $user, FormTemplate $formTemplate): bool
    {
        return $this->checkPermission($user, 'form.create');
    }

    public function restore(User $user, FormTemplate $formTemplate): bool
    {
        return $this->checkPermission($user, 'form.create');
    }

    public function forceDelete(User $user, FormTemplate $formTemplate): bool
    {
        return $this->checkPermission($user, 'form.create');
    }

    public function bulkDelete(User $user): bool
    {
        return $this->checkPermission($user, 'form.create');
    }
}
