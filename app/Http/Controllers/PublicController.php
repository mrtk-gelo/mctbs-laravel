<?php

namespace App\Http\Controllers;

use App\Mail\PropertyInquiry;
use App\Models\Booking;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Support\BookingStatus;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $data = [
            'houses' => $this->availableHouses($search)->get(),
            'search' => $search,
        ];

        if ($request->ajax()) {
            return view('public.partials.transient-results', $data);
        }

        return view('public.home', $data);
    }

    public function transients(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $houses = $this->availableHouses($search)->get();

        $data = [
            'houses' => $houses,
            'search' => $search,
        ];

        if ($request->ajax()) {
            return view('public.partials.transient-results', $data);
        }

        return view('public.transients', $data);
    }

    private function availableHouses(string $search): Builder
    {
        return TransientHouse::query()
            ->with('owner')
            ->withCount([
                'units' => fn ($q) => $q->where('status', 'Available'),
            ])
            ->withMin([
                'units as min_price' => fn ($q) => $q->where('status', 'Available'),
            ], 'price')
            ->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('house_id');
    }

    public function house(TransientHouse $house): View
    {
        abort_unless($house->is_active, 404);

        $house->load('owner');

        $units = $house->units()
            ->with('photos')
            ->where('status', 'Available')
            ->orderBy('price')
            ->get();

        return view('public.house', [
            'house' => $house,
            'units' => $units,
            'reviews' => collect(),
            'avgRating' => null,
        ]);
    }

    public function unit(Unit $unit): View
    {
        abort_unless(
            $unit->status === 'Available' &&
            $unit->house &&
            $unit->house->is_active,
            404
        );

        $unit->load(['house', 'photos', 'reviews.guest', 'reviews.booking.guests']);
        $today = Carbon::today('Asia/Manila');
        $reviews = $unit->reviews->sortByDesc('created_at')->values();
        $availability = $unit->availability()
            ->whereDate('date', '>=', $today->toDateString())
            ->get();
        $blocked = $availability->mapWithKeys(
            fn ($entry) => [$entry->date->toDateString() => strtoupper($entry->status)]
        );
        $blockedNotes = $availability->mapWithKeys(
            fn ($entry) => [$entry->date->toDateString() => strtoupper($entry->status) === 'BOOKED'
                ? 'This date is already booked.'
                : $entry->note]
        );
        $reservedBookings = Booking::query()
            ->where('unit_id', $unit->unit_id)
            ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
            ->where('check_out', '>', $today->copy()->startOfDay())
            ->get();
        $checkoutDates = [];
        $checkInDates = [];

        foreach ($reservedBookings as $booking) {
            $checkInDates[$booking->check_in->toDateString()] = true;
            $lastReservedDate = $booking->check_out->copy()->startOfDay()->subDay();
            $checkoutDates[$booking->check_out->toDateString()] = true;
            if ($lastReservedDate->lt($booking->check_in->copy()->startOfDay())) {
                continue;
            }

            $reservedDates = CarbonPeriod::create(
                $booking->check_in->copy()->startOfDay()->max($today->copy()->startOfDay()),
                '1 day',
                $lastReservedDate
            );
            foreach ($reservedDates as $date) {
                $dateKey = $date->toDateString();
                $blocked[$dateKey] = 'BOOKED';
                $blockedNotes[$dateKey] = 'The reservation is confirmed for this date.';
            }
        }

        return view('public.unit', [
            'unit' => $unit,
            'reviews' => $reviews,
            'averageRating' => $reviews->avg('rating'),
            'reviewCount' => $reviews->count(),
            'calendarToday' => $today,
            'checkoutDates' => $checkoutDates,
            'checkInDates' => $checkInDates,
            'unitDetails' => [
                [
                    'icon' => 'fa-users',
                    'label' => 'Capacity',
                    'value' => 'Standard '.$unit->capacity.' guests; max '.($unit->maximum_capacity ?? $unit->capacity).' counted guests',
                ],
                ...((int) ($unit->maximum_capacity ?? $unit->capacity) > (int) $unit->capacity ? [[
                    'icon' => 'fa-user-plus',
                    'label' => 'Extra guest fee',
                    'value' => '₱'.number_format((float) $unit->extra_guest_fee, 2).' per guest per night above standard capacity',
                ]] : []),
                ...($unit->child_free_through_age === null ? [] : [[
                    'icon' => 'fa-child',
                    'label' => 'Free child age',
                    'value' => 'Age '.$unit->child_free_through_age.' and under; not counted toward capacity',
                ]]),
                [
                    'icon' => 'fa-bed',
                    'label' => 'Unit Type',
                    'value' => $unit->unit_type,
                ],
                [
                    'icon' => 'fa-money-bill',
                    'label' => 'Price',
                    'value' => '₱'.number_format($unit->price, 2).' / night',
                ],
                [
                    'icon' => 'fa-circle-check',
                    'label' => 'Status',
                    'value' => $unit->status,
                ],
            ],
            'blocked' => $blocked->all(),
            'blockedNotes' => $blockedNotes->all(),
        ]);
    }

    public function sendInquiry(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        abort_unless(
            $unit->status === 'Available'
                && $unit->house
                && $unit->house->is_active,
            404
        );

        $this->emailInquiryToOwner($data, $unit->house, $unit);

        return back()->with('success', 'Your inquiry has been sent to the property owner.');
    }

    public function sendHouseInquiry(Request $request, TransientHouse $house): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        abort_unless($house->is_active, 404);

        $this->emailInquiryToOwner($data, $house);

        return back()->with('success', 'Your inquiry has been sent to the property owner.');
    }

    /**
     * @param  array{name: string, email: string, message: string}  $data
     */
    private function emailInquiryToOwner(array $data, TransientHouse $house, ?Unit $unit = null): void
    {
        $owner = $house->owner;
        if (! $owner) {
            throw ValidationException::withMessages([
                'inquiry' => 'This property does not currently have a contact email.',
            ]);
        }

        Mail::to($owner->email)->send(new PropertyInquiry(
            propertyName: $house->name,
            unitName: $unit?->unit_type,
            senderName: $data['name'],
            senderEmail: $data['email'],
            inquiryMessage: $data['message'],
        ));
    }
}
