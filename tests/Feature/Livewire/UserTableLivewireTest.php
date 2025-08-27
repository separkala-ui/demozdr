<?php

declare(strict_types=1);

use App\Livewire\Tables\User;
use Livewire\Livewire;
use App\Models\User as UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders user table successfully', function () {
    Livewire::test(User::class)
        ->assertStatus(200);
});

it('searches users by name and email', function () {
    $user = UserModel::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    Livewire::test(User::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->set('search', 'john@example.com')
        ->assertSee('john@example.com');
});

it('filters users by role', function () {
    $user = UserModel::factory()->create(['name' => 'RoleUser']);
    $role = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    $user->assignRole('admin');
    Livewire::test(User::class)
        ->set('role', 'admin')
        ->assertSee('RoleUser');
});

it('paginates users', function () {
    UserModel::factory()->count(15)->create();
    Livewire::test(User::class)
        ->assertSee('Users')
        ->set('page', 2)
        ->assertStatus(200);
});
