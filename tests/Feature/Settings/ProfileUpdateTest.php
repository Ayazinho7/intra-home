<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/settings/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.profile')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    // Üyeleri eltern yönetir: kimse (eltern dahil) kendi hesabını silemez.
    public function test_user_cannot_delete_their_own_account(): void
    {
        foreach ([User::factory()->eltern()->create(), User::factory()->create(), User::factory()->gast()->create()] as $user) {
            $this->actingAs($user);

            Volt::test('settings.delete-user-form')
                ->set('password', 'password')
                ->call('deleteUser')
                ->assertForbidden();

            $this->assertNotNull($user->fresh());
        }
    }

    public function test_delete_account_form_is_hidden(): void
    {
        $this->actingAs(User::factory()->eltern()->create());

        $this->get('/settings/profile')->assertOk()->assertDontSee(__('Delete Account'));
    }
}
