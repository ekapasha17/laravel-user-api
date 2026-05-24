<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUsersTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $role, ?User $user = null): User
    {
        $user ??= User::factory()->create([
            'role' => $role,
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_it_requires_authentication(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_it_returns_a_paginated_list_of_users_with_the_expected_fields(): void
    {
        $this->actingAsRole('administrator');

        User::factory()->count(3)->create(['active' => true]);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure([
                'page',
                'users' => [
                    '*' => ['id', 'email', 'name', 'role', 'created_at', 'orders_count', 'can_edit'],
                ],
            ]);
    }

    public function test_it_only_returns_active_users(): void
    {
        $this->actingAsRole('administrator');

        User::factory()->create(['active' => true, 'email' => 'active@example.com']);
        User::factory()->create(['active' => false, 'email' => 'inactive@example.com']);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['email' => 'active@example.com'])
            ->assertJsonMissing(['email' => 'inactive@example.com']);
    }

    public function test_it_filters_users_by_name_when_search_is_provided(): void
    {
        $this->actingAsRole('administrator');

        User::factory()->create(['name' => 'John Doe', 'active' => true]);
        User::factory()->create(['name' => 'Jane Smith', 'active' => true]);

        $this->getJson('/api/users?search=John')
            ->assertOk()
            ->assertJsonFragment(['name' => 'John Doe'])
            ->assertJsonMissing(['name' => 'Jane Smith']);
    }

    public function test_it_filters_users_by_email_when_search_is_provided(): void
    {
        $this->actingAsRole('administrator');

        User::factory()->create(['email' => 'john@example.com', 'name' => 'John', 'active' => true]);
        User::factory()->create(['email' => 'other@example.com', 'name' => 'Other', 'active' => true]);

        $this->getJson('/api/users?search=john')
            ->assertOk()
            ->assertJsonFragment(['email' => 'john@example.com'])
            ->assertJsonMissing(['email' => 'other@example.com']);
    }

    public function test_it_includes_each_users_order_count(): void
    {
        $this->actingAsRole('administrator');

        $user = User::factory()->create(['active' => true]);
        Order::factory()->count(4)->create(['user_id' => $user->id]);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $user->id,
                'orders_count' => 4,
            ]);
    }

    public function test_it_marks_every_user_as_editable_for_an_administrator(): void
    {
        $this->actingAsRole('administrator');

        $manager = User::factory()->create(['role' => 'manager', 'active' => true]);
        $regularUser = User::factory()->create(['role' => 'user', 'active' => true]);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['id' => $manager->id, 'can_edit' => true])
            ->assertJsonFragment(['id' => $regularUser->id, 'can_edit' => true]);
    }

    public function test_it_marks_users_but_not_other_managers_as_editable_for_a_manager(): void
    {
        $this->actingAsRole('manager');

        $regularUser = User::factory()->create(['role' => 'user', 'active' => true]);
        $otherManager = User::factory()->create(['role' => 'manager', 'active' => true]);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['id' => $regularUser->id, 'can_edit' => true])
            ->assertJsonFragment(['id' => $otherManager->id, 'can_edit' => false]);
    }

    public function test_it_only_marks_the_authenticated_user_as_editable_for_a_regular_user(): void
    {
        $self = User::factory()->create(['role' => 'user', 'active' => true]);
        $otherUser = User::factory()->create(['role' => 'user', 'active' => true]);

        $this->actingAsRole('user', $self);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['id' => $self->id, 'can_edit' => true])
            ->assertJsonFragment(['id' => $otherUser->id, 'can_edit' => false]);
    }
}
