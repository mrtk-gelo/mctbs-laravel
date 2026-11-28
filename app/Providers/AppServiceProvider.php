<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use App\Models\TransientHouse;
use App\Models\User;
use App\Services\AssistanceService;
use App\Services\ConversationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Mail::extend('brevo', fn () => new BrevoApiTransport((string) config('services.brevo.key')));

        View::composer('partials.nav', function (ViewInstance $view): void {
            $user = auth()->user();
            $unreadConversationCount = $user && in_array($user->role?->name, ['Owner', 'Staff'], true)
                ? app(ConversationService::class)->unreadConversationCountFor($user)
                : 0;

            $view->with('unreadConversationCount', $unreadConversationCount);
        });

        View::composer('partials.guest-chat-launcher', function (ViewInstance $view): void {
            $user = auth()->user();
            $conversationService = app(ConversationService::class);
            $assistance = app(AssistanceService::class);
            $isGuest = $user instanceof User && $user->role?->name === 'Guest';
            $unit = $view->getData()['unit'] ?? null;
            $house = $view->getData()['house'] ?? $unit?->house;
            $recentConversations = $isGuest
                ? $conversationService->conversationQueryFor($user)
                    ->with(['house', 'latestMessage'])
                    ->latest('last_message_at')
                    ->latest('created_at')
                    ->limit(3)
                    ->get()
                : collect();
            $visitorConversations = $user === null
                ? $conversationService->visitorConversationsFor(request())->take(3)
                : collect();
            $chatHouses = $house
                ? collect()
                : TransientHouse::query()
                    ->where('is_active', true)
                    ->whereNull('archived_at')
                    ->whereHas('owner', fn ($query) => $query->where('is_active', true))
                    ->orderBy('name')
                    ->get(['house_id', 'name']);
            $chatFaqs = $house
                ? $assistance->activeFaqsForGuest($house->house_id, $unit?->unit_id)
                : collect();

            $view->with([
                'recentConversations' => $recentConversations,
                'visitorConversations' => $visitorConversations,
                'chatHouses' => $chatHouses,
                'chatFaqs' => $chatFaqs,
                'chatQuickQuestions' => $unit
                    ? $assistance->quickQuestionsForUnit()
                    : $assistance->quickQuestionsForHouse(),
                'chatHouseId' => $house?->house_id,
                'chatUnitId' => $unit?->unit_id,
                'unreadChatCount' => $isGuest ? $conversationService->unreadConversationCountFor($user) : 0,
            ]);
        });
    }
}
