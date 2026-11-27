<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use App\Models\User;
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
            $isGuest = $user instanceof User && $user->role?->name === 'Guest';
            $recentConversations = $isGuest
                ? $conversationService->conversationQueryFor($user)
                    ->with(['house', 'latestMessage'])
                    ->latest('last_message_at')
                    ->latest('created_at')
                    ->limit(5)
                    ->get()
                : collect();

            $view->with([
                'recentConversations' => $recentConversations,
                'unreadChatCount' => $isGuest ? $conversationService->unreadConversationCountFor($user) : 0,
            ]);
        });
    }
}
