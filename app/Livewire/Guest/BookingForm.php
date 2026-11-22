<?php

namespace App\Livewire\Guest;

use App\Models\Unit;
use App\Services\BookingService;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BookingForm extends Component
{
    public Unit $unit;

    public string $checkIn = '';

    public string $checkOut = '';

    public string $paxCount = '1';

    public string $freeChildCount = '0';

    public int $seniorCount = 0;

    public int $pwdCount = 0;

    public array $seniorIds = [];

    public array $pwdIds = [];

    public bool $ackPolicy = false;

    public function mount(Unit $unit): void
    {
        $unit->loadMissing('house');
        abort_unless($unit->archived_at === null && $unit->house?->archived_at === null, 404);
        abort_unless(
            $unit->house?->is_active &&
            $unit->house->owner()->where('is_active', true)->exists(),
            404
        );

        $this->unit = $unit;
        $this->syncDiscountIds();
    }

    public function updatedPaxCount(mixed $value): void
    {
        $paxCount = filter_var($value, FILTER_VALIDATE_INT);

        if ($paxCount === false || $paxCount < 1) {
            $this->addError('paxCount', 'Add at least 1 paying guest.');

            return;
        }

        $this->resetErrorBag('paxCount');
        $this->validateGuestCapacity();

        if ($this->seniorCount + $this->pwdCount > $paxCount) {
            $this->addError('paxCount', 'Discounted guests must be included in the paying guest count.');
        }
    }

    public function updatedCheckIn(mixed $value): void
    {
        if (! is_string($value) || ! $this->isValidDate($value)) {
            $this->validateDateAvailability('checkIn');

            return;
        }

        $minimumCheckout = Carbon::parse($value)->addDay()->toDateString();

        if (! $this->isValidDate($this->checkOut)
            || Carbon::parse($this->checkOut)->startOfDay()->lessThanOrEqualTo(Carbon::parse($value)->startOfDay())) {
            $this->checkOut = $minimumCheckout;
        }

        $this->validateDateAvailability('checkIn');
    }

    public function updatedCheckOut(mixed $value): void
    {
        $this->validateDateAvailability('checkOut');
    }

    public function updatedFreeChildCount(mixed $value): void
    {
        $this->validateGuestCapacity();
    }

    public function updatedSeniorCount(mixed $value): void
    {
        $this->seniorCount = is_numeric($value) ? (int) $value : 0;
        $this->syncDiscountIds();
    }

    public function updatedPwdCount(mixed $value): void
    {
        $this->pwdCount = is_numeric($value) ? (int) $value : 0;
        $this->syncDiscountIds();
    }

    protected function rules(): array
    {
        $rules = [
            'checkIn' => ['required', 'date', 'after_or_equal:tomorrow'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
            'paxCount' => ['required', 'integer', 'min:1', 'max:'.($this->unit->maximum_capacity ?? $this->unit->capacity)],
            'freeChildCount' => [
                'required',
                'integer',
                'min:0',
                'max:'.($this->unit->child_free_through_age === null ? 0 : 100),
            ],
            'seniorCount' => ['required', 'integer', 'min:0', 'max:'.$this->paxCount],
            'pwdCount' => ['required', 'integer', 'min:0', 'max:'.$this->paxCount],
            'seniorIds' => ['array', 'size:'.$this->seniorCount],
            'seniorIds.*' => ['required', 'string', 'max:100'],
            'pwdIds' => ['array', 'size:'.$this->pwdCount],
            'pwdIds.*' => ['required', 'string', 'max:100'],
            'ackPolicy' => ['accepted'],
        ];

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'checkOut.after' => 'Check-out must be after check-in.',
            'paxCount.min' => 'Add at least 1 paying guest.',
            'paxCount.max' => 'Maximum is '.($this->unit->maximum_capacity ?? $this->unit->capacity).' guests.',
            'seniorIds.*.required' => 'Enter an ID reference for each Senior Citizen.',
            'pwdIds.*.required' => 'Enter an ID reference for each PWD guest.',
            'ackPolicy.accepted' => 'You must acknowledge the cancellation policy.',
        ];
    }

    /**
     * Live price summary, recalculated automatically whenever
     * checkIn / checkOut changes — this replaces the old
     * updateCalc() JS function with a reactive server-side computed property.
     */
    #[Computed]
    public function summary(): ?array
    {
        $paxCount = filter_var($this->paxCount, FILTER_VALIDATE_INT);

        if ($paxCount === false || $paxCount < 1 || ! $this->hasValidStayDateOrder()) {
            return null;
        }

        $summary = app(BookingService::class)->calculate(
            $this->unit,
            $this->checkIn,
            $this->checkOut,
            $paxCount
        );

        return $summary;
    }

    public function submit(BookingService $bookingService): RedirectResponse|Redirector|null
    {
        $this->validate();

        if (! $this->validateGuestCapacity()) {
            return null;
        }

        $paxCount = (int) $this->paxCount;
        $freeChildCount = (int) $this->freeChildCount;

        if ($this->seniorCount + $this->pwdCount > $paxCount) {
            $this->addError('pwdCount', 'Each Senior Citizen or PWD discount must belong to a different person included in the pax count.');

            return null;
        }

        try {
            $booking = $bookingService->createBooking(auth()->user(), $this->unit, [
                'check_in' => $this->checkIn,
                'check_out' => $this->checkOut,
                'adult_count' => $paxCount,
                'child_count' => $freeChildCount,
                'free_child_count' => $freeChildCount,
                'senior_count' => $this->seniorCount,
                'pwd_count' => $this->pwdCount,
                'senior_ids' => $this->seniorIds,
                'pwd_ids' => $this->pwdIds,
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $componentField = $field === 'guests' ? 'freeChildCount' : ($field === 'check_in' ? 'checkIn' : $field);
                $this->addError($componentField, $messages[0]);
            }

            return null;
        }

        session()->flash('modal', [
            'title' => 'Booking request sent',
            'message' => 'Your request has been sent to the property owner and is waiting for their response. It is not confirmed yet. You will be notified when the owner accepts or declines it.',
        ]);

        return redirect()->route('guest.bookings.show', $booking->booking_id);
    }

    public function addSenior(): void
    {
        $paxCount = filter_var($this->paxCount, FILTER_VALIDATE_INT);

        if ($paxCount === false || $this->seniorCount + $this->pwdCount >= $paxCount) {
            $this->addError('seniorCount', 'Discounted guests must be included in the pax count, with only one discount per person.');

            return;
        }

        $this->seniorCount++;
        $this->syncDiscountIds();
        $this->resetErrorBag('seniorCount');
    }

    public function removeSenior(int $index): void
    {
        $this->seniorCount = max(0, $this->seniorCount - 1);
        unset($this->seniorIds[$index]);
        $this->seniorIds = array_values($this->seniorIds);
        $this->syncDiscountIds();
        $this->resetErrorBag('seniorCount');
    }

    public function addPwd(): void
    {
        $paxCount = filter_var($this->paxCount, FILTER_VALIDATE_INT);

        if ($paxCount === false || $this->seniorCount + $this->pwdCount >= $paxCount) {
            $this->addError('pwdCount', 'Discounted guests must be included in the pax count, with only one discount per person.');

            return;
        }

        $this->pwdCount++;
        $this->syncDiscountIds();
        $this->resetErrorBag('pwdCount');
    }

    public function removePwd(int $index): void
    {
        $this->pwdCount = max(0, $this->pwdCount - 1);
        unset($this->pwdIds[$index]);
        $this->pwdIds = array_values($this->pwdIds);
        $this->syncDiscountIds();
        $this->resetErrorBag('pwdCount');
    }

    private function syncDiscountIds(): void
    {
        $paxCount = filter_var($this->paxCount, FILTER_VALIDATE_INT);

        if ($paxCount === false || $paxCount < 0) {
            return;
        }

        $this->seniorCount = min($paxCount, max(0, $this->seniorCount));
        $this->pwdCount = min(max(0, $paxCount - $this->seniorCount), max(0, $this->pwdCount));
        $this->seniorIds = array_slice($this->seniorIds, 0, $this->seniorCount);
        $this->pwdIds = array_slice($this->pwdIds, 0, $this->pwdCount);
        $this->seniorIds = array_pad($this->seniorIds, $this->seniorCount, '');
        $this->pwdIds = array_pad($this->pwdIds, $this->pwdCount, '');
    }

    private function validateGuestCapacity(): bool
    {
        $paxCount = filter_var($this->paxCount, FILTER_VALIDATE_INT);
        $freeChildCount = filter_var($this->freeChildCount, FILTER_VALIDATE_INT);

        if ($paxCount === false || $paxCount < 1) {
            $this->addError('paxCount', 'Add at least 1 paying guest.');

            return false;
        }

        if ($freeChildCount === false || $freeChildCount < 0) {
            $this->addError('freeChildCount', 'Enter a whole number of free children.');

            return false;
        }

        if ($paxCount + $freeChildCount > (int) ($this->unit->maximum_capacity ?? $this->unit->capacity)) {
            $this->addError(
                'freeChildCount',
                'Maximum is '.($this->unit->maximum_capacity ?? $this->unit->capacity).' guests.'
            );

            return false;
        }

        $this->resetErrorBag('freeChildCount');

        return true;
    }

    private function validateDateAvailability(string $changedDateField): void
    {
        $this->resetErrorBag(['checkIn', 'checkOut']);

        if (! $this->isValidDate($this->checkIn)) {
            return;
        }

        $bookingService = app(BookingService::class);
        $minimumCheckout = Carbon::parse($this->checkIn)->addDay()->toDateString();
        $checkInConflict = $bookingService->dateRangeConflict($this->unit, $this->checkIn, $minimumCheckout, false);

        if ($checkInConflict !== null) {
            $this->addError('checkIn', 'This check-in date is unavailable or already booked. Choose another date.');

            return;
        }

        if (! $this->isValidDate($this->checkOut)) {
            return;
        }

        if (! $this->hasValidStayDateOrder()) {
            $this->addError('checkOut', 'Check-out must be after check-in.');

            return;
        }

        $rangeConflict = $bookingService->dateRangeConflict($this->unit, $this->checkIn, $this->checkOut);

        if ($rangeConflict === 'blocked') {
            $this->addError($changedDateField, 'Your selected stay includes an unavailable date. Choose different dates.');
        } elseif ($rangeConflict === 'booking') {
            $this->addError($changedDateField, 'Your selected stay overlaps an active booking. Choose different dates.');
        }
    }

    private function isValidDate(mixed $value): bool
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) !== 1) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
            && $date->format('Y-m-d') === $value
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
    }

    private function hasValidStayDateOrder(): bool
    {
        return $this->isValidDate($this->checkIn)
            && $this->isValidDate($this->checkOut)
            && Carbon::parse($this->checkOut)->startOfDay()->greaterThan(Carbon::parse($this->checkIn)->startOfDay());
    }

    public function render()
    {
        return view('livewire.guest.booking-form');
    }
}
