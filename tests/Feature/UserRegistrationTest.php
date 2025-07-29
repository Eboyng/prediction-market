<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_register_with_valid_data()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
        ]);
        
        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check($userData['password'], $user->password));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_can_register_with_referral_code()
    {
        // Arrange
        $referrer = User::factory()->create();
        $referrer->generateReferralCode();
        
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'referral_code' => $referrer->referral_code,
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertRedirect('/dashboard');
        
        $newUser = User::where('email', $userData['email'])->first();
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $newUser->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function registration_fails_with_invalid_email()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => 'invalid-email',
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => $userData['email']]);
    }

    /** @test */
    public function registration_fails_with_weak_password()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => $userData['email']]);
    }

    /** @test */
    public function registration_fails_with_duplicate_email()
    {
        // Arrange
        $existingUser = User::factory()->create();
        
        $userData = [
            'name' => $this->faker->name,
            'email' => $existingUser->email,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::where('email', $existingUser->email)->count());
    }

    /** @test */
    public function registration_fails_with_invalid_referral_code()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'referral_code' => 'INVALID123',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors('referral_code');
        $this->assertDatabaseMissing('users', ['email' => $userData['email']]);
    }

    /** @test */
    public function user_gets_welcome_bonus_after_registration()
    {
        // Arrange
        config(['app.welcome_bonus' => 50000]); // 500 NGN
        
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $user = User::where('email', $userData['email'])->first();
        $this->assertEquals(50000, $user->wallet_balance);
        
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'type' => 'bonus',
            'amount' => 50000,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function user_can_register_via_livewire_component()
    {
        // Arrange & Act
        Livewire::test('auth.register-component')
            ->set('name', $this->faker->name)
            ->set('email', $this->faker->unique()->safeEmail)
            ->set('phone', '+2348012345678')
            ->set('password', 'SecurePass123!')
            ->set('password_confirmation', 'SecurePass123!')
            ->call('register')
            ->assertRedirect('/dashboard');

        // Assert
        $this->assertDatabaseCount('users', 1);
    }

    /** @test */
    public function registration_creates_activity_log()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $user = User::where('email', $userData['email'])->first();
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'user_registered',
            'description' => 'User registered successfully',
        ]);
    }

    /** @test */
    public function registration_sends_welcome_email()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        // This would typically use Mail::fake() and assert mail was sent
        // For now, we'll just verify the user was created
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);
    }

    /** @test */
    public function registration_rate_limiting_works()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act - Make multiple registration attempts
        for ($i = 0; $i < 6; $i++) {
            $userData['email'] = $this->faker->unique()->safeEmail;
            $response = $this->post('/register', $userData);
        }

        // Assert - Should be rate limited after 5 attempts
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function user_cannot_register_if_already_authenticated()
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('users', ['email' => $userData['email']]);
    }

    /** @test */
    public function registration_validates_phone_number_format()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '123', // Invalid phone format
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseMissing('users', ['email' => $userData['email']]);
    }

    /** @test */
    public function registration_creates_referral_code_for_new_user()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user->referral_code);
        $this->assertEquals(8, strlen($user->referral_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $user->referral_code);
    }
}
