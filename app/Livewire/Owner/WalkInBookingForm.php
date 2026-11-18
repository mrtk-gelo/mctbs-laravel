<?php

namespace App\Livewire\Owner;

use App\Models\Invitation;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BookingWorkflowService;
use App\Support\BookingStatus;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class WalkInBookingForm extends Component
{
    public string $dashboardPrefix = 'owner';

    public string $unitId = '';

    public string $walkInName = '';

    public string $walkInPhone = '';

    public string $walkInEmail = '';

    public string $checkIn = '';

    public string $checkOut = '';

    public string $adultCount = '1';

    public string $freeChildCount = '0';

    public int $seniorCount = 0;

    public int $pwdCount = 0;

    public array $seniorIds = [];

    public array $pwdIds = [];

    public string $paymentMethod = 'Cash';

    public string $paymentAmount = '';

    public string $paymentReference = '';

    public bool $checkInNow = false;

    public function mount(): void
    {
        $this->dashboardPrefix = auth()->user()->role?->name === 'Staff' ? 'staff' : 'owner';
    }

    public function updatedUnitId(): void
    {
        if (! $this->selectedUnit()) {
            $this->addError('unitId', 'Choose an available unit.');

            return;
        }

        $this->resetErrorBag('unitId');
        $this->validateDateAvailability('checkIn');
        $this->validateGuestCapacity();
    }

    public function updatedCheckIn(mixed $value): void
    {
        if (! $this->isValidDate($value)) {
            $this->validateDateAvailability('checkIn');

            return;
        }

        if (! $this->isValidDate($this->checkOut)
            || Carbon::parse($this->checkOut)->startOfDay()->lessThanOrEqualTo(Carbon::parse($value)->startOfDay())) {
            $this->checkOut = Carbon::parse($value)->addDay()->toDateString();
        }

        if ($this->checkInNow && $value !== Carbon::today('Asia/Manila')->toDateString()) {
            $this->checkInNow = false;
        }

        $this->validateDateAvailability('checkIn');
    }

    public function updatedCheckOut(): void
    {
        $this->validateDateAvailability('checkOut');
    }

    public function updatedAdultCount(mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1) {
            $this->addError('adultCount', 'Add at least 1 paying guest.');

            return;
        }

        $this->resetErrorBag('adultCount');
        $this->validateGuestCapacity();
        $this->validateDiscountCounts();
    }

    public function updatedFreeChildCount(mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 0) {
            $this->addError('freeChildCount', 'Enter a whole number of children.');

            return;
        }

        $this->resetErrorBag('freeChildCount');
        $this->validateGuestCapacity();
    }

    public function updatedPaymentReference(mixed $value, BookingWorkflowService $workflow): void
    {
        $this->paymentReference = trim((string) $value);

        if (! $workflow->isReferenceNumberAvailable($this->paymentReference)) {
            $this->addError('paymentReference', 'Reference number already used.');

            return;
        }

        $this->resetErrorBag('paymentReference');
    }

    public function addSenior(): void
    {
        if ($this->seniorCount + $this->pwdCount >= (int) $this->adultCount) {
            $this->addError('seniorIds', 'Discounted guests must be included in the paying guest count.');

            return;
        }

        $this->seniorIds[] = '';
        $this->seniorCount++;
        $this->resetErrorBag('seniorIds');
    }

    public function removeSenior(int $index): void
    {
        unset($this->seniorIds[$index]);
        $this->seniorIds = array_values($this->seniorIds);
        $this->seniorCount = count($this->seniorIds);
        $this->resetErrorBag('seniorIds');
    }

    public function addPwd(): void
    {
        if ($this->seniorCount + $this->pwdCount >= (int) $this->adultCount) {
            $this->addError('pwdIds', 'Discounted guests must be included in the paying guest count.');

            return;
        }

        $this->pwdIds[] = '';
        $this->pwdCount++;
        $this->resetErrorBag('pwdIds');
    }

    public function removePwd(int $index): void
    {
        unset($this->pwdIds[$index]);
        $this->pwdIds = array_values($this->pwdIds);
        $this->pwdCount = count($this->pwdIds);
        $this->resetErrorBag('pwdIds');
    }

    protected function rules(): array
    {
        $unit = $this->selectedUnit();
        $maximumCapacity = $unit ? (int) ($unit->maximum_capacity ?? $unit->capacity) : 1;
        $freeChildMaximum = $unit?->child_free_through_age === null ? 0 : 100;

        return [
            'unitId' => ['required', 'integer', 'min:1'],
            'walkInName' => ['required', 'string', 'max:200'],
            'walkInPhone' => ['required', 'string', 'max:30'],
            'walkInEmail' => ['nullable', 'email', 'max:191'],
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
            'adultCount' => ['required', 'integer', 'min:1', 'max:'.$maximumCapacity],
            'freeChildCount' => ['required', 'integer', 'min:0', 'max:'.$freeChildMaximum],
            'seniorCount' => ['required', 'integer', 'min:0', 'max:'.max(0, (int) $this->adultCount)],
            'pwdCount' => ['required', 'integer', 'min:0', 'max:'.max(0, (int) $this->adultCount)],
            'seniorIds' => ['array', 'size:'.$this->seniorCount],
            'seniorIds.*' => ['required', 'string', 'max:100'],
            'pwdIds' => ['array', 'size:'.$this->pwdCount],
            'pwdIds.*' => ['required', 'string', 'max:100'],
            'paymentMethod' => ['required', 'in:Cash,GCash,Bank Transfer'],
            'paymentAmount' => ['required', 'numeric', 'gt:0'],
            'paymentReference' => ['nullable', 'string', 'max:100'],
            'checkInNow' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'checkOut.after' => 'Check-out must be after check-in.',
            'adultCount.min' => 'Add at least 1 paying guest.',
            'adultCount.max' => 'Maximum is '.($this->selectedUnit()
                ? ($this->selectedUnit()->maximum_capacity ?? $this->selectedUnit()->capacity)
                : 1).' guests.',
            'seniorIds.*.required' => 'Enter an ID reference for each Senior Citizen.',
            'pwdIds.*.required' => 'Enter an ID reference for each PWD guest.',
        ];
    }

    public function submit(
        BookingService $bookingService,
        BookingWorkflowService $workflow
    ): RedirectResponse|Redirector|null {
        $this->validate();

        $actor = auth()->user();
        if (! $actor instanceof User) {
            abort(403);
        }

        $unit = $this->selectedUnit();
        if (! $unit) {
            $this->addError('unitId', 'Choose an available unit.');

            return null;
        }

        if (! $this->validateGuestCapacity() || ! $this->validateDiscountCounts()) {
            return null;
        }

        if (! $workflow->isReferenceNumberAvailable($this->paymentReference)) {
            $this->addError('paymentReference', 'Reference number already used.');

            return null;
        }

        try {
            $booking = $bookingService->createWalkInBooking($actor, $unit, [
                'check_in' => $this->checkIn,
                'check_out' => $this->checkOut,
                'adult_count' => (int) $this->adultCount,
                'child_count' => (int) $this->freeChildCount,
                'free_child_count' => (int) $this->freeChildCount,
                'senior_count' => $this->seniorCount,
                'pwd_count' => $this->pwdCount,
                'senior_ids' => $this->seniorIds,
                'pwd_ids' => $this->pwdIds,
                'walk_in_name' => trim($this->walkInName),
                'walk_in_phone' => trim($this->walkInPhone),
                'walk_in_email' => trim($this->walkInEmail) !== '' ? trim($this->walkInEmail) : null,
                'payment_method' => $this->paymentMethod,
                'payment_amount' => (float) $this->paymentAmount,
                'payment_reference' => trim($this->paymentReference) !== '' ? trim($this->paymentReference) : null,
                'check_in_now' => $this->checkInNow,
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $componentField = match ($field) {
                    'check_in' => 'checkIn',
                    'check_out' => 'checkOut',
                    'guests', 'free_child_count', 'adult_count' => 'freeChildCount',
                    'senior_count' => 'seniorIds',
                    'pwd_count' => 'pwdIds',
                    'check_in_now' => 'checkInNow',
                    'amount' => 'paymentAmount',
                    'reference_number' => 'paymentReference',
                    default => $field,
                };
                $this->addError($componentField, $messages[0]);
            }

            return null;
        }

        session()->flash('success', 'Walk-in booking saved. Payment recorded.');
        $destination = $booking->status === BookingStatus::CHECKED_IN
            ? route($this->dashboardPrefix.'.guests.check-in', $booking->booking_id)
            : route($this->dashboardPrefix.'.bookings.show', $booking->booking_id);

        return redirect()->to($destination);
    }

    private function availableUnits(): Collection
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return new Collection;
        }

        $query = Unit::query()
            ->with('house')
            ->where('status', 'Available');

        if ($user->role?->name === 'Owner') {
            return $query
                ->whereHas('house', fn ($houseQuery) => $houseQuery->where('owner_id', $user->user_id))
                ->orderBy('unit_type')
                ->get();
        }

        $staffRoleId = Role::query()->where('name', 'Staff')->value('role_id');
        $houseIds = Invitation::query()
            ->where('email', $user->email)
            ->where('role_id', $staffRoleId)
            ->whereNotNull('used_at')
            ->whereNotNull('house_id')
            ->pluck('house_id');

        return $query->whereIn('house_id', $houseIds)->orderBy('unit_type')->get();
    }

    private function selectedUnit(): ?Unit
    {
        if ($this->unitId === '') {
            return null;
        }

        return $this->availableUnits()->firstWhere('unit_id', (int) $this->unitId);
    }

    private function validateGuestCapacity(): bool
    {
        $adultCount = filter_var($this->adultCount, FILTER_VALIDATE_INT);
        $freeChildCount = filter_var($this->freeChildCount, FILTER_VALIDATE_INT);

        if ($adultCount === false || $adultCount < 1) {
            $this->addError('adultCount', 'Add at least 1 paying guest.');

            return false;
        }

        if ($freeChildCount === false || $freeChildCount < 0) {
            $this->addError('freeChildCount', 'Enter a whole number of children.');

            return false;
        }

        $unit = $this->selectedUnit();
        if (! $unit) {
            return false;
        }

        $maximumCapacity = (int) ($unit->maximum_capacity ?? $unit->capacity);
        if ($adultCount + $freeChildCount > $maximumCapacity) {
            $this->addError('freeChildCount', 'Maximum is '.$maximumCapacity.' guests.');

            return false;
        }

        $this->resetErrorBag(['adultCount', 'freeChildCount']);

        return true;
    }

    private function validateDiscountCounts(): bool
    {
        if ($this->seniorCount + $this->pwdCount > (int) $this->adultCount) {
            $this->addError('seniorIds', 'Discounted guests must be included in the paying guest count.');

            return false;
        }

        return true;
    }

    private function validateDateAvailability(string $changedDateField): void
    {
        $this->resetErrorBag(['checkIn', 'checkOut']);
        $unit = $this->selectedUnit();

        if (! $unit || ! $this->isValidDate($this->checkIn)) {
            return;
        }

        $bookingService = app(BookingService::class);
        $minimumCheckout = Carbon::parse($this->checkIn)->addDay()->toDateString();
        if ($bookingService->dateRangeConflict($unit, $this->checkIn, $minimumCheckout, false) !== null) {
            $this->addError('checkIn', 'This check-in date is unavailable or booked.');

            return;
        }

        if (! $this->isValidDate($this->checkOut)) {
            return;
        }

        if (! $this->hasValidStayDateOrder()) {
            $this->addError('checkOut', 'Check-out must be after check-in.');

            return;
        }

        $conflict = $bookingService->dateRangeConflict($unit, $this->checkIn, $this->checkOut);
        if ($conflict === 'blocked') {
            $this->addError($changedDateField, 'Your stay includes an unavailable date.');
        } elseif ($conflict === 'booking') {
            $this->addError($changedDateField, 'Your stay overlaps a confirmed booking.');
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

    /**
     * @return array<string, int|float|string>|null
     */
    private function priceSummary(?Unit $unit): ?array
    {
        if (! $unit || ! $this->hasValidStayDateOrder()) {
            return null;
        }

        $adultCount = filter_var($this->adultCount, FILTER_VALIDATE_INT);
        if ($adultCount === false || $adultCount < 1) {
            return null;
        }

        $summary = app(BookingService::class)->calculate($unit, $this->checkIn, $this->checkOut, $adultCount);
        $discountGuestCount = $this->seniorCount + $this->pwdCount;
        $guestShares = app(BookingService::class)->allocateGuestShares($summary, $adultCount);
        $discountShares = array_slice($guestShares, 0, $discountGuestCount);
        $estimatedDiscount = array_sum(array_map(
            fn (string $share): float => round((float) $share * 0.2, 2),
            $discountShares
        ));

        return [
            ...$summary,
            'estimated_discount' => round($estimatedDiscount, 2),
        ];
    }

    public function render(): View
    {
        $units = $this->availableUnits();
        $selectedUnit = $units->firstWhere('unit_id', (int) $this->unitId);

        return view('livewire.owner.walk-in-booking-form', [
            'units' => $units,
            'selectedUnit' => $selectedUnit,
            'summary' => $this->priceSummary($selectedUnit),
        ]);
    }
}
