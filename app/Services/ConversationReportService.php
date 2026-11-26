<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ConversationReportService
{
    /**
     * @param  Collection<int, int>  $houseIds
     * @param  array{house_id: ?int, from_date: string, to_date: string}  $filters
     * @return array{
     *     conversationCount: int,
     *     resolvedCount: int,
     *     handoffCount: int,
     *     faqResponseCount: int,
     *     contextResponseCount: int,
     *     respondedConversationCount: int,
     *     averageFirstResponseMinutes: ?float,
     *     resolutionRate: float,
     *     handoffRate: float
     * }
     */
    public function generate(Collection $houseIds, array $filters): array
    {
        $visibleHouseIds = $filters['house_id'] === null
            ? $houseIds
            : $houseIds->filter(fn (int $houseId): bool => $houseId === $filters['house_id'])->values();
        $from = Carbon::parse($filters['from_date'])->startOfDay();
        $to = Carbon::parse($filters['to_date'])->endOfDay();
        $conversations = Conversation::query()
            ->whereIn('house_id', $visibleHouseIds)
            ->whereBetween('created_at', [$from, $to])
            ->with(['messages' => fn ($query) => $query->orderBy('sent_at')])
            ->get();

        $conversationCount = $conversations->count();
        $respondedConversationCount = 0;
        $responseTimes = [];

        foreach ($conversations as $conversation) {
            $guestMessage = $conversation->messages
                ->where('sender_type', 'user')
                ->where('sender_id', $conversation->guest_user_id)
                ->first();
            $staffResponse = $guestMessage
                ? $conversation->messages
                    ->where('sender_type', 'user')
                    ->where('sender_id', '!=', $conversation->guest_user_id)
                    ->first(fn ($message): bool => $message->sent_at->greaterThan($guestMessage->sent_at))
                : null;

            if ($staffResponse) {
                $respondedConversationCount++;
                $responseTimes[] = $guestMessage->sent_at->diffInSeconds($staffResponse->sent_at) / 60;
            }
        }

        $resolvedCount = $conversations->where('status', 'resolved')->count();
        $handoffCount = $conversations
            ->filter(fn (Conversation $conversation): bool => $conversation->messages->contains(
                fn ($message): bool => $message->sender_type === 'system'
            ))
            ->count();
        $faqResponseCount = $conversations->sum(
            fn (Conversation $conversation): int => $conversation->messages->where('message_type', 'assistant')->count()
        );
        $contextResponseCount = $conversations->sum(
            fn (Conversation $conversation): int => $conversation->messages->where('message_type', 'context')->count()
        );

        return [
            'conversationCount' => $conversationCount,
            'resolvedCount' => $resolvedCount,
            'handoffCount' => $handoffCount,
            'faqResponseCount' => $faqResponseCount,
            'contextResponseCount' => $contextResponseCount,
            'respondedConversationCount' => $respondedConversationCount,
            'averageFirstResponseMinutes' => $responseTimes === []
                ? null
                : round(array_sum($responseTimes) / count($responseTimes), 1),
            'resolutionRate' => $conversationCount === 0 ? 0.0 : round($resolvedCount / $conversationCount * 100, 1),
            'handoffRate' => $conversationCount === 0 ? 0.0 : round($handoffCount / $conversationCount * 100, 1),
        ];
    }
}
