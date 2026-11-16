<?php

// Modül 1 — Ev üyeleri ve giriş (Mitglieder · Anmeldung)

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

function newMember(array $override = []): array
{
    return array_merge([
        'name' => 'Oma Ilse',
        'email' => 'ilse@example.com',
        'password' => 'geheim1234',
        'password_confirmation' => 'geheim1234',
        'role' => 'gast',
        'color' => '#ff8800',
    ], $override);
}

// --- Giriş yapmayan hiçbir sayfayı göremez ---

test('giriş yapmayan üye listesini göremez', function () {
    $this->get('/members')->assertRedirect('/login');
});

test('giriş yapmayan üye ekleyemez', function () {
    $this->post('/members', newMember())->assertRedirect('/login');
    expect(User::count())->toBe(0);
});

// --- Gast sadece okuyabilir ---

test('gast listeyi görebilir ama ekle düğmesini görmez', function () {
    $gast = User::factory()->gast()->create(['name' => 'Besuch Bernd']);

    $this->actingAs($gast)->get('/members')
        ->assertOk()
        ->assertSee('Besuch Bernd')
        ->assertDontSee('Mitglied hinzufügen');
});

test('gast üye ekleyemez', function () {
    $gast = User::factory()->gast()->create();

    $this->actingAs($gast)->get('/members/create')->assertForbidden();
    $this->actingAs($gast)->post('/members', newMember())->assertForbidden();
});

test('gast yazma iznine sahip değil', function () {
    expect(User::factory()->gast()->make()->canWrite())->toBeFalse()
        ->and(User::factory()->make()->canWrite())->toBeTrue();
});

// --- Yalnız eltern yeni üye ekleyebilir ---

test('kind üye ekleyemez', function () {
    $kind = User::factory()->create();

    $this->actingAs($kind)->get('/members/create')->assertForbidden();
    $this->actingAs($kind)->post('/members', newMember())->assertForbidden();
});

test('eltern üye ekleyebilir', function () {
    $eltern = User::factory()->eltern()->create();

    $this->actingAs($eltern)->post('/members', newMember())
        ->assertRedirect('/members');

    $ilse = User::where('email', 'ilse@example.com')->first();
    expect($ilse->role)->toBe(Role::Gast)
        ->and($ilse->color)->toBe('#ff8800')
        ->and(Hash::check('geheim1234', $ilse->password))->toBeTrue();
});

test('eltern ekle ve düzenle formlarını açabilir', function () {
    $eltern = User::factory()->eltern()->create();
    $kind = User::factory()->create();

    $this->actingAs($eltern)->get('/members')->assertOk()->assertSee('Mitglied hinzufügen');
    $this->actingAs($eltern)->get('/members/create')->assertOk()->assertSee('Farbe');
    $this->actingAs($eltern)->get("/members/{$kind->id}/edit")->assertOk()->assertSee($kind->email);
});

test('boş isim Almanca hatayla reddedilir', function () {
    $eltern = User::factory()->eltern()->create();

    $this->actingAs($eltern)->post('/members', newMember(['name' => '']))
        ->assertSessionHasErrors(['name' => 'Name muss ausgefüllt werden.']);
});

test('geçersiz rol ve renk reddedilir', function () {
    $eltern = User::factory()->eltern()->create();

    $this->actingAs($eltern)->post('/members', newMember(['role' => 'chef', 'color' => 'rot']))
        ->assertSessionHasErrors(['role', 'color']);
});

// --- Düzenleme ve silme ---

test('eltern üyeyi düzenleyebilir, boş şifre eskisini korur', function () {
    $eltern = User::factory()->eltern()->create();
    $kind = User::factory()->create(['password' => 'altes-pass']);

    $this->actingAs($eltern)->put("/members/{$kind->id}", newMember([
        'name' => 'Yağız', 'email' => $kind->email, 'role' => 'kind',
        'password' => '', 'password_confirmation' => '',
    ]))->assertRedirect('/members');

    $kind->refresh();
    expect($kind->name)->toBe('Yağız')
        ->and(Hash::check('altes-pass', $kind->password))->toBeTrue();
});

test('eltern kendi rolünü düşüremez', function () {
    $eltern = User::factory()->eltern()->create();

    $this->actingAs($eltern)->put("/members/{$eltern->id}", newMember([
        'email' => $eltern->email, 'role' => 'kind', 'password' => '', 'password_confirmation' => '',
    ]))->assertSessionHasErrors('role');

    expect($eltern->refresh()->role)->toBe(Role::Eltern);
});

test('kind başkasını düzenleyemez', function () {
    $kind = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($kind)->get("/members/{$other->id}/edit")->assertForbidden();
});

test('eltern başkasını silebilir ama kendini silemez', function () {
    $eltern = User::factory()->eltern()->create();
    $kind = User::factory()->create();

    $this->actingAs($eltern)->delete("/members/{$eltern->id}")->assertForbidden();
    $this->actingAs($eltern)->delete("/members/{$kind->id}")->assertRedirect('/members');

    expect(User::count())->toBe(1);
});

// --- Açık kayıt yalnız ilk kişi için ---

test('ilk kayıt olan kişi eltern olur', function () {
    Volt::test('auth.register')
        ->set('name', 'Erste Person')
        ->set('email', 'erste@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors();

    expect(User::first()->role)->toBe(Role::Eltern);
});

test('biri kayıtlıysa kayıt kapalıdır', function () {
    User::factory()->eltern()->create();

    $this->get('/register')->assertRedirect('/login');

    Volt::test('auth.register')->assertRedirect(route('login'));

    // Giriş sayfasında "Registrieren" bağlantısı da görünmez.
    $this->get('/login')->assertDontSee('Registrieren');
});
