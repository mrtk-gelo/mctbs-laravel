<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReceipt;
use App\Services\BookingTicketDeliveryService;
use App\Services\BookingTicketPdfGenerator;
use App\Services\BookingWorkflowService;
use App\Support\BookingFinancialContract;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function show(Request $request, int $booking_id): View
    {
        return view('guest.bookings.show', $this->bookingPageData($request, $booking_id));
    }

    public function ticket(Request $request, int $booking_id, BookingTicketPdfGenerator $pdfGenerator): Response
    {
        $booking = Booking::query()
            ->where('user_id', $request->user()->user_id)
            ->whereIn('status', [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
            ->with(['unit.house', 'guest'])
            ->findOrFail($booking_id);

        return response($pdfGenerator->generate($booking))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="booking-ticket-'.$booking->booking_reference.'.pdf"');
    }

    public function resendTicket(
        Request $request,
        int $booking_id,
        BookingTicketDeliveryService $ticketDelivery
    ): RedirectResponse {
        Booking::query()
            ->where('user_id', $request->user()->user_id)
            ->findOrFail($booking_id);

        $status = $ticketDelivery->send($booking_id);

        return match ($status) {
            'sent' => redirect()->route('guest.bookings.show', $booking_id)
                ->with('success', 'Your booking ticket email was sent successfully.'),
            'already_sent' => redirect()->route('guest.bookings.show', $booking_id)
                ->with('info', 'Your booking ticket has already been emailed.'),
            'in_progress' => redirect()->route('guest.bookings.show', $booking_id)
                ->with('info', 'Your booking ticket email is already being processed.'),
            'failed', 'missing_recipient' => redirect()->route('guest.bookings.show', $booking_id)
                ->with('error', 'We could not email your ticket. You can download it here and try sending it again later.'),
            default => redirect()->route('guest.bookings.show', $booking_id)
                ->with('error', 'A ticket can only be sent for a confirmed booking.'),
        };
    }

    public function payment(Request $request, int $booking_id): View|RedirectResponse
    {
        $booking = Booking::query()
            ->where('user_id', $request->user()->user_id)
            ->with(['unit.house', 'guests', 'payments'])
            ->findOrFail($booking_id);

        if ($booking->status !== BookingStatus::ACCEPTED) {
            return redirect()
                ->route('guest.bookings.show', $booking_id)
                ->with('info', 'A downpayment can only be submitted while an accepted booking is awaiting payment.');
        }

        return view('guest.bookings.payment', [
            'booking' => $booking,
            'remainingBalance' => $booking->outstandingBalance(),
            'downpaymentAmount' => max(
                0,
                round($booking->downpaymentAmount() - BookingFinancialContract::verifiedPayments($booking), 2)
            ),
            'hasUnderReviewPayment' => $booking->payments->contains('payment_status', PaymentStatus::UNDER_REVIEW),
        ]);
    }

    public function receipt(Request $request, int $booking_id): View
    {
        return view('guest.bookings.receipt', $this->receiptData($request, $booking_id));
    }

    public function receiptPdf(Request $request, int $booking_id): Response
    {
        $receiptData = $this->receiptData($request, $booking_id);

        return Pdf::loadView('guest.bookings.receipt-pdf', $receiptData)
            ->setPaper('a4', 'portrait')
            ->download('Booking-Breakdown-'.$receiptData['receipt']->receipt_number.'.pdf');
    }

    public function submitPayment(
        Request $request,
        int $booking_id,
        BookingWorkflowService $workflow
    ): RedirectResponse {
        $referenceNumber = trim((string) $request->input('reference_number', ''));
        $request->merge(['reference_number' => $referenceNumber !== '' ? $referenceNumber : null]);

        $validated = $request->validate([
            'payment_method' => ['required', 'in:GCash,Bank Transfer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reference_number' => ['nullable', 'string', 'max:100', Rule::unique('payments', 'reference_number')],
            'payment_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'reference_number.unique' => 'Reference number already used.',
        ]);
        $receiptPath = $request->file('payment_receipt')?->store('payment-proofs');
        $paymentSaved = false;

        try {
            $workflow->submitPayment($request->user(), $booking_id, [
                'payment_method' => $validated['payment_method'],
                'amount' => (float) $validated['amount'],
                'reference_number' => $validated['reference_number'] ?? null,
                'payment_receipt' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
            ]);
            $paymentSaved = true;
        } finally {
            if (! $paymentSaved && $receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }
        }

        return redirect()
            ->route('guest.bookings.show', $booking_id)
            ->with('success', 'Your payment was submitted and is waiting for review.');
    }

    public function cancel(Request $request, int $booking_id, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Please explain why you are cancelling this booking.',
            'reason.string' => 'Please enter a reason in words.',
            'reason.max' => 'Please keep the reason to 500 characters or fewer.',
        ]);

        $workflow->cancelByGuest($request->user(), $booking_id, $validated['reason']);

        return redirect()
            ->route('guest.bookings.show', $booking_id)
            ->with('success', 'Your booking was cancelled.');
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPageData(Request $request, int $bookingId): array
    {
        $booking = Booking::query()
            ->where('user_id', $request->user()->user_id)
            ->with(['unit.house.owner', 'guests', 'addons', 'damages', 'additionalCharges', 'damageReports', 'discounts.guest', 'payments', 'refunds.payment', 'edits.editor', 'receipt'])
            ->findOrFail($bookingId);
        $verifiedPaymentTotal = BookingFinancialContract::verifiedPayments($booking);
        $hasUnderReviewPayment = $booking->payments->contains('payment_status', PaymentStatus::UNDER_REVIEW);
        $checkoutTotal = $booking->amountDueAtCheckout();

        return [
            'booking' => $booking,
            'nights' => max(1, $booking->check_in->diffInDays($booking->check_out)),
            'checkoutTotal' => $checkoutTotal,
            'verifiedPaymentTotal' => $verifiedPaymentTotal,
            'hasUnderReviewPayment' => $hasUnderReviewPayment,
        ];
    }

    /**
     * @return array{
     *     booking: Booking,
     *     receipt: BookingReceipt,
     *     financialSummary: array{
     *         roomCharges: float,
     *         extraGuestCharges: float,
     *         accommodationBeforeDiscounts: float,
     *         approvedDiscounts: float,
     *         accommodationAfterDiscounts: float,
     *         additionalCharges: float,
     *         amenities: float,
     *         approvedDamageCharges: float,
     *         finalAmountDue: float,
     *         verifiedPayments: float,
     *         completedRefunds: float,
     *         outstandingBalance: float,
     *         refundableOverpayment: float
     *     },
     *     nights: int
     * }
     */
    private function receiptData(Request $request, int $bookingId): array
    {
        $booking = Booking::query()
            ->where('user_id', $request->user()->user_id)
            ->whereIn('status', BookingStatus::completedStatuses())
            ->with([
                'unit.house',
                'guest',
                'guests',
                'discounts.guest',
                'payments' => fn ($query) => $query->orderBy('payment_date')->orderBy('payment_id'),
                'addons',
                'damages',
                'additionalCharges',
                'damageReports',
                'refunds' => fn ($query) => $query
                    ->with('payment')
                    ->orderBy('created_at')
                    ->orderBy('refund_id'),
                'receipt',
            ])
            ->findOrFail($bookingId);
        abort_unless($booking->receipt, 404);

        return [
            'booking' => $booking,
            'receipt' => $booking->receipt,
            'financialSummary' => BookingFinancialContract::receiptSummary($booking),
            'nights' => max(1, $booking->check_in->diffInDays($booking->check_out)),
        ];
    }
}
