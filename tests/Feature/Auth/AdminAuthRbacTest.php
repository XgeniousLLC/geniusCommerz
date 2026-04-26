<?php

use App\Models\Admin;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'is_active' => true,
        'must_reset_password' => false,
    ]);
    $this->admin->assignRole('super-admin');
});

describe('Admin Login', function () {
    test('admin can login with valid credentials', function () {
        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    });

    test('inactive admin cannot login', function () {
        $this->admin->update(['is_active' => false]);

        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    });

    test('wrong credentials are rejected', function () {
        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');
    });

    test('login records last_login_at', function () {
        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        expect($this->admin->fresh()->last_login_at)->not->toBeNull();
    });

    test('must_reset_password redirects to change-password', function () {
        $this->admin->update(['must_reset_password' => true]);

        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.password.change'));
    });

    test('guest is redirected from protected routes', function () {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    });

    test('admin can logout', function () {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    });
});

describe('Spatie RBAC', function () {
    test('admin has super-admin role', function () {
        expect($this->admin->hasRole('super-admin'))->toBeTrue();
    });

    test('admin uuid is auto-generated', function () {
        expect($this->admin->uuid)->not->toBeEmpty();
        expect(strlen($this->admin->uuid))->toBe(36);
    });
});

describe('Invite flow', function () {
    test('super admin can send invite', function () {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.admins.invite'), [
                'name' => 'New Manager',
                'email' => 'manager@test.com',
                'role' => 'store-manager',
            ])->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseHas('admins', [
            'email' => 'manager@test.com',
            'is_active' => false,
        ]);
    });

    test('invited admin can accept invite and activate account', function () {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.admins.invite'), [
                'name' => 'New Manager',
                'email' => 'manager2@test.com',
                'role' => 'store-manager',
            ]);

        $invited = Admin::where('email', 'manager2@test.com')->first();
        expect($invited->invite_token)->not->toBeNull();
        expect($invited->is_active)->toBeFalse();

        $this->post(route('admin.invite.accept', $invited->invite_token), [
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ])->assertRedirect(route('admin.dashboard'));

        $invited->refresh();
        expect($invited->is_active)->toBeTrue();
        expect($invited->invite_token)->toBeNull();
        expect($invited->must_reset_password)->toBeFalse();
    });

    test('invalid invite token returns 404', function () {
        $this->get(route('admin.invite.show', 'invalid-token'))->assertStatus(404);
    });
});
