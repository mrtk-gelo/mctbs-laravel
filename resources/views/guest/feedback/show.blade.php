<x-shell title="Provide Feedback | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => ''])</x-slot:nav>

    <main class="container-sm" style="margin-top:32px;margin-bottom:48px">
        <div class="card">
            <div class="card-header">Provide Feedback</div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                <p class="text-muted mb-3">
                    Booking #{{ $booking->booking_id }} for <strong>{{ $booking->displayGuestName() }}</strong> at
                    <strong>{{ $booking->unit->house->name }}</strong>
                    · {{ $booking->unit->unit_type }}
                </p>

                @if ($feedbackMessage)
                    <div class="alert alert-info" role="status">{{ $feedbackMessage }}</div>
                    @if ($booking->review)
                        <p><strong>Your rating:</strong> {{ $booking->review->rating }} / 5</p>
                        @if ($booking->review->comment)
                            <p class="mb-0"><strong>Your review:</strong> {{ $booking->review->comment }}</p>
                        @endif
                    @endif
                @else
                    <form method="POST" action="{{ route('guest.feedback.store', ['booking' => $booking->booking_id, 'token' => $token]) }}">
                        @csrf
                        <fieldset class="mb-3">
                            <legend class="form-label">How would you rate your stay? <span aria-hidden="true">*</span></legend>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                @for ($rating = 1; $rating <= 5; $rating++)
                                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                                        <input
                                            type="radio"
                                            name="rating"
                                            value="{{ $rating }}"
                                            aria-label="{{ $rating }} {{ $rating === 1 ? 'star' : 'stars' }}"
                                            @checked(old('rating') == $rating)
                                            required
                                        >
                                        <i class="fa fa-star" style="color:var(--accent)" aria-hidden="true"></i>
                                        {{ $rating }}
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <p class="text-danger mt-1" role="alert">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <label for="comment">Comment <span class="text-muted">(optional)</span></label>
                        <textarea id="comment" name="comment" rows="5" maxlength="2000">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="text-danger mt-1" role="alert">{{ $message }}</p>
                        @enderror
                        @error('feedback')
                            <p class="text-danger mt-1" role="alert">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="btn btn-primary mt-3">Submit Feedback</button>
                    </form>
                @endif
            </div>
        </div>
    </main>
</x-shell>
