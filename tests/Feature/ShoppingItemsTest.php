<?php

use App\Models\ShoppingItem;
use App\Models\User;

function shoppingItem(User $owner, array $attributes = []): ShoppingItem
{
    $item = new ShoppingItem(array_merge(['name' => 'Milch', 'quantity' => '2 Liter', 'category' => 'Kühlregal'], $attributes));
    $item->added_by = $owner->id;
    $item->save();

    return $item;
}

test('giriş yapmayan listeyi ve işlemleri göremez', function () {
    $owner = User::factory()->create();
    $item = shoppingItem($owner);
    $this->get('/shopping-items')->assertRedirect('/login');
    $this->get('/shopping-items/create')->assertRedirect('/login');
    $this->post('/shopping-items', ['name' => 'Brot'])->assertRedirect('/login');
    $this->get("/shopping-items/{$item->id}/edit")->assertRedirect('/login');
    $this->put("/shopping-items/{$item->id}", ['name' => 'Brot'])->assertRedirect('/login');
    $this->delete("/shopping-items/{$item->id}")->assertRedirect('/login');
    $this->patch("/shopping-items/{$item->id}/buy")->assertRedirect('/login');
});

test('üye ürün ekler düzenler ve siler', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/shopping-items/create')->assertOk();
    $this->post('/shopping-items', ['name' => 'Brot', 'quantity' => '2', 'category' => 'Backwaren', 'added_by' => 999])
        ->assertRedirect('/shopping-items');
    $item = ShoppingItem::firstOrFail();
    expect($item->added_by)->toBe($user->id);
    $this->get("/shopping-items/{$item->id}/edit")->assertOk()->assertSee('Brot');
    $this->put("/shopping-items/{$item->id}", ['name' => 'Brötchen', 'quantity' => '4', 'category' => 'Backwaren'])
        ->assertRedirect('/shopping-items');
    expect($item->refresh()->name)->toBe('Brötchen')->and($item->quantity)->toBe('4');
    $this->delete("/shopping-items/{$item->id}")->assertRedirect('/shopping-items');
    $this->assertDatabaseMissing('shopping_items', ['id' => $item->id]);
});

test('boş isim Almanca hatayla reddedilir', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post('/shopping-items', ['name' => ''])
        ->assertSessionHasErrors(['name' => 'Name muss ausgefüllt werden.']);
    $this->assertDatabaseCount('shopping_items', 0);
});

test('ürün tek istekle satın alınır ve ilk satın alma korunur', function () {
    $owner = User::factory()->create();
    $buyer = User::factory()->create();
    $item = shoppingItem($owner);
    $this->actingAs($buyer)->patch("/shopping-items/{$item->id}/buy")->assertRedirect('/shopping-items');
    expect($item->refresh()->bought_by)->toBe($buyer->id)->and($item->bought_at)->not->toBeNull();
    $time = $item->bought_at->copy();
    $this->travel(1)->days();
    $this->actingAs($owner)->patch("/shopping-items/{$item->id}/buy");
    $item->refresh();
    expect($item->bought_by)->toBe($buyer->id)->and($item->bought_at->equalTo($time))->toBeTrue();
});

test('alınanlar altta görünür ve yedi gün sonra gizlenir', function () {
    $user = User::factory()->create();
    $old = shoppingItem($user, ['name' => 'Alter Käse']);
    $bought = shoppingItem($user, ['name' => 'Gekaufte Milch']);
    $bought->bought_at = now()->subDays(6);
    $bought->save();
    $old->bought_at = now()->subDays(7);
    $old->save();
    shoppingItem($user, ['name' => 'Frisches Brot']);
    $this->actingAs($user)->get('/shopping-items')->assertOk()
        ->assertSeeInOrder(['Frisches Brot', 'Gekaufte Milch'])->assertDontSee('Alter Käse');
    $this->assertDatabaseHas('shopping_items', ['id' => $old->id]);
});

test('gast listeyi görür ama hiçbir değişiklik yapamaz', function () {
    $owner = User::factory()->create();
    $gast = User::factory()->gast()->create();
    $item = shoppingItem($owner);
    $this->actingAs($gast)->get('/shopping-items')->assertOk()->assertSee('Milch')->assertDontSee('Artikel hinzufügen')->assertDontSee('Gekauft');
    $this->get('/shopping-items/create')->assertForbidden();
    $this->post('/shopping-items', ['name' => 'Brot'])->assertForbidden();
    $this->get("/shopping-items/{$item->id}/edit")->assertForbidden();
    $this->put("/shopping-items/{$item->id}", ['name' => 'Brot'])->assertForbidden();
    $this->delete("/shopping-items/{$item->id}")->assertForbidden();
    $this->patch("/shopping-items/{$item->id}/buy")->assertForbidden();
    expect($item->refresh()->name)->toBe('Milch')->and($item->bought_at)->toBeNull();
});

test('başkasının ürününü yalnız ebeveyn düzenler veya siler', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $parent = User::factory()->eltern()->create();
    $item = shoppingItem($owner);
    $this->actingAs($other)->get("/shopping-items/{$item->id}/edit")->assertForbidden();
    $this->put("/shopping-items/{$item->id}", ['name' => 'Brot'])->assertForbidden();
    $this->delete("/shopping-items/{$item->id}")->assertForbidden();
    $this->actingAs($parent)->put("/shopping-items/{$item->id}", ['name' => 'Brot'])->assertRedirect('/shopping-items');
    $this->delete("/shopping-items/{$item->id}")->assertRedirect('/shopping-items');
});


test('üye silindiğinde ortak ürün ve satın alma kaydı korunur', function () {
    $owner = User::factory()->create();
    $buyer = User::factory()->create();
    $item = shoppingItem($owner);
    $item->bought_by = $buyer->id;
    $item->bought_at = now();
    $item->save();
    $owner->delete();
    $buyer->delete();
    $item->refresh();
    expect($item->added_by)->toBeNull()->and($item->bought_by)->toBeNull()->and($item->bought_at)->not->toBeNull();
});
