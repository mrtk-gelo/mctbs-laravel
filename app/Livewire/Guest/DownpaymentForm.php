<?php

namespace App\Livewire\Guest;

use App\Models\Booking;
use App\Models\User;
use App\Services\BookingWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class DownpaymentForm extends Component
{
    use WithFileUploads;

    public Booking $booking;

    public float $downpaymentAmount;

    public float $remainingBalance;

    public string $paymentMethod = '';

    public string $amount = '';

    public ?string $referenceNumber = null;

    public ?TemporaryUploadedFile $paymentReceipt = null;

    public ?string $notes = null;

    public function mount(Booking $booking, float $downpaymentAmount, float $remainingBalance): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $user->user_id === $booking->user_id, 404);

        $this->booking = $booking;
        $this->downpaymentAmount = $downpaymentAmount;
        $this->remainingBalance = $remainingBalance;
        $this->amount = number_format($downpaymentAmount, 2, '.', '');
    }

    public function updatedReferenceNumber(mixed $value): void
    {
        $referenceNumber = trim((string) $value);
        $this->resetErrorBag('referenceNumber');

        if ($referenceNumber !== ''
            && ! app(BookingWorkflowService::class)->isReferenceNumberAvailable($referenceNumber)) {
            $this->addError('referenceNumber', 'Reference number already used.');
        }
    }

    public function updatedPaymentReceipt(): void
    {
        $this->validateOnly('paymentReceipt');
    }

    protected function rules(): array
    {
        return [
            'paymentMethod' => ['required', 'in:GCash,Bank Transfer'],
            'amount' => [
                'required',
                'numeric',
                'min:'.$this->downpaymentAmount,
                'max:'.$this->remainingBalance,
            ],
            'referenceNumber' => ['nullable', 'string', 'max:100'],
            'paymentReceipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'paymentMethod.required' => 'Choose a payment method.',
            'amount.min' => 'Enter at least the required downpayment.',
            'amount.max' => 'Amount cannot exceed the remaining balance.',
            'paymentReceipt.required' => 'Upload your payment proof.',
            'paymentReceipt.mimes' => 'Use a JPG, PNG, or PDF file.',
            'paymentReceipt.max' => 'File size must be 5 MB or less.',
        ];
    }

    public function submit(BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $this->validate();

        if (! $workflow->isReferenceNumberAvailable($validated['referenceNumber'] ?? null)) {
            throw ValidationException::withMessages([
                'referenceNumber' => 'Reference number already used.',
            ]);
        }

        $user = auth()->user();
        abort_unless($user instanceof User && $user->user_id === $this->booking->user_id, 404);
        $receiptFile = $this->paymentReceipt;
        if (! $receiptFile) {
            throw ValidationException::withMessages(['paymentReceipt' => 'Upload your payment proof.']);
        }

        $receiptPath = $receiptFile->store('payment-proofs');
        if (! $receiptPath) {
            throw new \RuntimeException('The payment proof could not be saved.');
        }

        $paymentSaved = false;

        try {
            $workflow->submitPayment($user, $this->booking->booking_id, [
                'payment_method' => $validated['paymentMethod'],
                'amount' => (float) $validated['amount'],
                'reference_number' => $validated['referenceNumber'] ?? null,
                'payment_receipt' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
            ]);
            $paymentSaved = true;
        } finally {
            if (! $paymentSaved) {
                Storage::disk('local')->delete($receiptPath);
            }
        }

        return redirect()
            ->route('guest.bookings.show', $this->booking->booking_id)
            ->with('success', 'Your payment was submitted and is waiting for review.');
    }

    public function render(): View
    {
        return view('livewire.guest.downpayment-form');
    }
}
