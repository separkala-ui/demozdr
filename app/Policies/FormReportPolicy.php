<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DynamicForms\FormReport;
use App\Models\User;

class FormReportPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'form.report.view');
    }

    public function view(User $user, FormReport $formReport): bool
    {
        if ($this->checkPermission($user, 'form.report.view')) {
            return true;
        }

        return $this->checkPermission($user, 'form.submit') && $formReport->reporter_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'form.submit');
    }

    public function update(User $user, FormReport $formReport): bool
    {
        if ($this->checkPermission($user, 'form.report.view')) {
            return true;
        }

        return $this->checkPermission($user, 'form.submit') && $formReport->reporter_id === $user->id;
    }

    public function delete(User $user, FormReport $formReport): bool
    {
        return $this->checkPermission($user, 'form.report.view');
    }
}
