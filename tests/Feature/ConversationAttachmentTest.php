<?php

use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('conversation replies reject file uploads and remain text only', function () {
    Mail::fake();
    $owner = textOnlyConversationUser('Owner', 'text-owner@example.com');
    $guest = textOnlyConversationUser('Guest', 'text-guest@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Text Only House',
        'address' => '1 Text Road',
        'city' => 'Baguio',
        'contact_number' => '5556000',
        'is_active' => true,
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($guest)
        ->post(route('guest.conversations.reply', $conversation->conversation_id), [
            'message' => 'Here is a file.',
            'attachments' => [UploadedFile::fake()->create('guest-document.pdf', 200, 'application/pdf')],
        ])
        ->assertSessionHasErrors('attachments');

    $this->actingAs($owner)
        ->post(route('owner.conversations.store', $conversation->conversation_id), [
            'message' => 'Here is another file.',
            'attachments' => [UploadedFile::fake()->create('owner-document.pdf', 200, 'application/pdf')],
        ])
        ->assertSessionHasErrors('attachments');

    $this->actingAs($guest)
        ->post(route('guest.conversations.store'), [
            'house_id' => $house->house_id,
            'message' => 'This message includes a file.',
            'attachments' => [UploadedFile::fake()->create('new-conversation.pdf', 200, 'application/pdf')],
        ])
        ->assertSessionHasErrors('attachments');

    expect($conversation->messages()->count())->toBe(0);

    $this->actingAs($guest)
        ->get('/guest/conversations/'.$conversation->conversation_id.'/attachments/1')
        ->assertNotFound();
});

function textOnlyConversationUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => ucfirst($role),
        'last_name' => 'TextOnly',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}
