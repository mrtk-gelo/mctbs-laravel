<?php

use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use App\Services\ConversationService;
use App\Support\R2AclCompatibility;
use Aws\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('R2 upload configuration omits unsupported ACL headers', function () {
    $command = new Command('PutObject', ['ACL' => 'private']);
    $beforeUpload = config('filesystems.disks.r2.options.before_upload');

    $beforeUpload($command);

    expect(isset($command['ACL']))->toBeFalse()
        ->and($beforeUpload)->toBe([R2AclCompatibility::class, 'removeAcl']);
});

test('conversation participants can upload and download private attachments', function () {
    config(['filesystems.conversation_attachments' => 'r2']);
    Storage::fake('r2');
    Mail::fake();
    $owner = attachmentUser('Owner', 'attachment-owner@example.com');
    $guest = attachmentUser('Guest', 'attachment-guest@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Attachment House',
        'address' => '1 Attachment Road',
        'city' => 'Baguio',
        'contact_number' => '5556000',
        'is_active' => true,
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($guest)
        ->post(route('guest.conversations.reply', $conversation->conversation_id), [
            'message' => 'Here is the requested document.',
            'attachments' => [UploadedFile::fake()->create('guest-document.pdf', 200, 'application/pdf')],
        ])
        ->assertRedirect();

    $message = $conversation->messages()->where('message', 'Here is the requested document.')->firstOrFail();
    $attachment = $message->attachments()->firstOrFail();
    expect($attachment->disk)->toBe('r2');
    Storage::disk('r2')->assertExists($attachment->path);

    $this->actingAs($guest)
        ->get(route('guest.conversations.attachments.download', [
            $conversation->conversation_id,
            $attachment->attachment_id,
        ]))
        ->assertOk();

    $this->actingAs($owner)
        ->get(route('owner.conversations.attachments.download', [
            $conversation->conversation_id,
            $attachment->attachment_id,
        ]))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('invalid and cross-property attachment requests are rejected', function () {
    Storage::fake('local');
    Mail::fake();
    $owner = attachmentUser('Owner', 'attachment-scope-owner@example.com');
    $otherOwner = attachmentUser('Owner', 'attachment-scope-other@example.com');
    $guest = attachmentUser('Guest', 'attachment-scope-guest@example.com');
    $house = attachmentHouse($owner, 'Attachment Scope House');
    $otherHouse = attachmentHouse($otherOwner, 'Other Attachment House');
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($guest)
        ->post(route('guest.conversations.reply', $conversation->conversation_id), [
            'message' => 'This file type is not accepted.',
            'attachments' => [UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload')],
        ])
        ->assertSessionHasErrors('attachments.0');

    expect($conversation->messages()->count())->toBe(0);

    $otherConversation = app(ConversationService::class)->createForGuest($guest, $otherHouse->house_id);
    $attachmentMessage = $conversation->messages()->create([
        'sender_id' => $guest->user_id,
        'message' => 'Private attachment',
        'message_type' => 'user',
        'sent_at' => now(),
    ]);
    $attachment = $attachmentMessage->attachments()->create([
        'path' => 'conversation-attachments/'.$conversation->conversation_id.'/private.pdf',
        'original_name' => 'private.pdf',
        'mime_type' => 'application/pdf',
        'size' => 100,
    ]);
    Storage::disk('local')->put($attachment->path, 'private file');

    $this->actingAs($owner)
        ->get(route('owner.conversations.attachments.download', [
            $conversation->conversation_id,
            $attachment->attachment_id,
        ]))
        ->assertOk();

    $this->actingAs($otherOwner)
        ->get(route('owner.conversations.attachments.download', [
            $conversation->conversation_id,
            $attachment->attachment_id,
        ]))
        ->assertNotFound();

    expect($otherConversation->house_id)->not->toBe($conversation->house_id);
});

test('a storage failure does not persist a message without its attachment', function () {
    config(['filesystems.conversation_attachments' => 'r2']);
    Mail::fake();
    $owner = attachmentUser('Owner', 'attachment-failure-owner@example.com');
    $guest = attachmentUser('Guest', 'attachment-failure-guest@example.com');
    $house = attachmentHouse($owner, 'Attachment Failure House');
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('putFileAs')->once()->andReturn(false);
    Storage::shouldReceive('disk')->once()->with('r2')->andReturn($disk);

    expect(fn () => app(ConversationService::class)->sendMessage(
        $guest,
        $conversation,
        'This message must not be sent without its file.',
        attachments: [UploadedFile::fake()->create('failed-upload.pdf', 100, 'application/pdf')]
    ))->toThrow(RuntimeException::class, 'The conversation attachment could not be stored.');

    expect($conversation->messages()->count())->toBe(0);
    Mail::assertNothingSent();
});

function attachmentUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => ucfirst($role),
        'last_name' => 'Attachment',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

function attachmentHouse(User $owner, string $name): TransientHouse
{
    return TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => $name,
        'address' => '2 Attachment Road',
        'city' => 'Baguio',
        'contact_number' => '5556001',
        'is_active' => true,
    ]);
}
