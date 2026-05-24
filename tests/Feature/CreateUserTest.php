<?php

namespace Tests\Feature;

use App\Mail\AdminNewUserNotificationMail;
use App\Mail\UserWelcomeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload = [
        'email' => 'john@example.com',
        'password' => 'secret123',
        'name' => 'John Doe',
    ];

    public function test_it_creates_a_user_and_returns_the_created_user_without_the_password(): void
    {
        Mail::fake();

        $this->postJson('/api/users', $this->validPayload)
            ->assertCreated()
            ->assertJsonStructure(['id', 'email', 'name', 'created_at'])
            ->assertJsonMissing(['password']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    public function test_it_sends_a_welcome_email_to_the_new_user(): void
    {
        Mail::fake();

        $this->postJson('/api/users', $this->validPayload)->assertCreated();

        Mail::assertSent(
            UserWelcomeMail::class,
            fn (UserWelcomeMail $mail) => $mail->hasTo('john@example.com')
        );
    }

    public function test_it_sends_a_new_user_notification_email_to_the_admin(): void
    {
        Mail::fake();

        config(['mail.admin_email' => 'admin@example.com']);

        $this->postJson('/api/users', $this->validPayload)->assertCreated();

        Mail::assertSent(
            AdminNewUserNotificationMail::class,
            fn (AdminNewUserNotificationMail $mail) => $mail->hasTo('admin@example.com')
        );
    }

    public function test_it_rejects_a_missing_email(): void
    {
        $this->postJson('/api/users', [...$this->validPayload, 'email' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_an_invalid_email_address(): void
    {
        $this->postJson('/api/users', [...$this->validPayload, 'email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_an_email_address_that_is_already_registered(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $this->postJson('/api/users', $this->validPayload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_a_missing_password(): void
    {
        $this->postJson('/api/users', [...$this->validPayload, 'password' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_rejects_a_password_shorter_than_eight_characters(): void
    {
        $this->postJson('/api/users', [...$this->validPayload, 'password' => 'short'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_rejects_a_missing_name(): void
    {
        $this->postJson('/api/users', [...$this->validPayload, 'name' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_rejects_a_name_shorter_than_three_characters(): void
    {
        $this->postJson('/api/users', [...$this->validPayload, 'name' => 'Jo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_rejects_a_name_longer_than_fifty_characters(): void
    {
        $this->postJson('/api/users', [...$this->validPayload, 'name' => str_repeat('A', 51)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
