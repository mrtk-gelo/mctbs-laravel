<?php

namespace App\Livewire\Guest;

use App\Models\Booking;
use App\Models\User;
use App\Support\BookingFinancialContract;
use App\Support\PaymentStatus;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingStatus extends Component
{
    public Booking $booking;

    #[Locked]
    public float $verifiedPaymentTotal = 0;

    #[Locked]
    public bool $hasUnderReviewPayment = false;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking;
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $this->booking->user_id === $user->user_id, 404);

        $this->booking = Booking::query()
            ->with(['payments', 'receipt'])
            ->findOrFail($this->booking->booking_id);
        $this->verifiedPaymentTotal = BookingFinancialContract::verifiedPayments($this->booking);
        $this->hasUnderReviewPayment = $this->booking->payments
            ->contains('payment_status', PaymentStatus::UNDER_REVIEW);
    }

    public function render(): View
    {
        return view('livewire.guest.booking-status', [
            'checkoutTotal' => $this->booking->amountDueAtCheckout(),
            'downpaymentAmount' => $this->booking->downpaymentAmount(),
        ]);
    }
}
