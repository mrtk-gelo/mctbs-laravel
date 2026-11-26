<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostmarkInboundController extends Controller
{
    public function __invoke(Request $request, ConversationService $conversations): JsonResponse
    {
        $configuredUsername = (string) config('services.postmark.inbound_username');
        $configuredPassword = (string) config('services.postmark.inbound_password');
        abort_if($configuredUsername === '' || $configuredPassword === '', 503, 'Inbound email is not configured.');
        abort_unless(
            hash_equals($configuredUsername, (string) $request->getUser())
                && hash_equals($configuredPassword, (string) $request->getPassword()),
            401
        );

        $data = Validator::make($request->all(), [
            'From' => ['required', 'string', 'max:255'],
            'To' => ['required', 'string', 'max:2000'],
            'TextBody' => ['required', 'string', 'max:5000'],
            'MessageID' => ['required', 'string', 'max:255'],
        ])->validate();
        preg_match('/reply\+([A-Za-z0-9]+)@/i', $data['To'], $tokenMatch);
        $token = $tokenMatch[1] ?? null;
        abort_unless($token !== null, 404);

        $expectedDomain = strtolower(trim((string) config('services.postmark.inbound_domain')));
        if ($expectedDomain !== '') {
            preg_match('/@([^>\s,]+)/', $data['To'], $domainMatch);
            abort_unless(isset($domainMatch[1]) && strtolower($domainMatch[1]) === $expectedDomain, 404);
        }

        preg_match('/<([^>]+)>/', $data['From'], $emailMatch);
        $fromEmail = $emailMatch[1] ?? trim($data['From']);
        abort_unless(filter_var($fromEmail, FILTER_VALIDATE_EMAIL), 422);

        $conversation = Conversation::query()
            ->where('external_channel', 'postmark')
            ->where('external_token', $token)
            ->firstOrFail();
        $conversations->receiveGuestEmail(
            $conversation,
            $fromEmail,
            $data['TextBody'],
            $data['MessageID']
        );

        return response()->json(['accepted' => true]);
    }
}
