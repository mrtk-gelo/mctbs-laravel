<x-shell title="Share Your Feedback | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => ''])</x-slot:nav>

    <main class="container-sm feedback-page">
        <section class="feedback-card">
            <header class="feedback-heading">
                <span class="feedback-eyebrow">MCTBS · Guest experience</span>
                <h1>How was your stay?</h1>
                <p>Your feedback helps us make every visit more comfortable.</p>
            </header>

            <div class="feedback-booking">
                <span class="feedback-booking-icon"><i class="fa fa-house" aria-hidden="true"></i></span>
                <div>
                    <strong>{{ $booking->unit->house->name }}</strong>
                    <span>{{ $booking->unit->unit_type }} · Booking {{ $booking->booking_reference ?: '#'.$booking->booking_id }}</span>
                </div>
            </div>

            @if (session('success'))
                <div class="feedback-alert feedback-alert--success" role="status">
                    <i class="fa fa-circle-check" aria-hidden="true"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($feedbackMessage)
                @unless (session('success'))
                    <div class="feedback-alert feedback-alert--info" role="status">
                        <i class="fa fa-circle-info" aria-hidden="true"></i>
                        <span>{{ $feedbackMessage }}</span>
                    </div>
                @endunless
                @if ($booking->review)
                    <div class="feedback-review">
                        <strong>Your rating</strong>
                        <span class="feedback-review-rating" aria-label="{{ $booking->review->rating }} out of 5 stars">
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <i class="fa fa-star{{ $rating <= $booking->review->rating ? '' : '-o' }}" aria-hidden="true"></i>
                            @endfor
                            {{ $booking->review->rating }} / 5
                        </span>
                        @if ($booking->review->comment)
                            <p>{{ $booking->review->comment }}</p>
                        @endif
                    </div>
                @endif
            @else
                <form method="POST" action="{{ route('guest.feedback.store', ['booking' => $booking->booking_id, 'token' => $token]) }}" class="feedback-form" data-feedback-form>
                    @csrf
                    <fieldset class="feedback-rating-fieldset">
                        <legend>Rate your stay <span aria-hidden="true">*</span></legend>
                        <p>Select the rating that best reflects your experience.</p>
                        <div class="feedback-rating-options">
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <div class="feedback-rating-option">
                                    <input
                                        id="rating-{{ $rating }}"
                                        type="radio"
                                        name="rating"
                                        value="{{ $rating }}"
                                        aria-label="{{ $rating }} {{ $rating === 1 ? 'star' : 'stars' }}"
                                        @checked(old('rating') == $rating)
                                        required
                                    >
                                    <label for="rating-{{ $rating }}">
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <span>{{ $rating }}</span>
                                    </label>
                                </div>
                            @endfor
                        </div>
                        @error('rating')
                            <p class="feedback-error" role="alert">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div class="feedback-comment-field">
                        <label for="comment">Anything else you would like us to know? <span>(optional)</span></label>
                        <textarea id="comment" name="comment" rows="5" maxlength="2000" placeholder="Share what went well or how we could make your next stay even better.">{{ old('comment') }}</textarea>
                        <div class="feedback-field-footer"><span>Up to 2,000 characters</span></div>
                        @error('comment')
                            <p class="feedback-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    @error('feedback')
                        <div class="feedback-alert feedback-alert--info" role="alert">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="feedback-submit">
                        <span data-feedback-submit-label>Submit feedback</span>
                        <i class="fa fa-arrow-right" aria-hidden="true"></i>
                    </button>
                    <p class="feedback-privacy-note">Your review can be submitted once for this booking.</p>
                </form>
            @endif
        </section>
    </main>

    <style>
        .feedback-page { max-width: 720px; margin: 36px auto 56px; padding: 0 16px; }
        .feedback-card { padding: clamp(22px, 5vw, 42px); border: 1px solid #eadfd3; border-radius: 18px; background: #fff; box-shadow: 0 18px 48px rgba(83, 54, 31, .09); }
        .feedback-heading { margin-bottom: 25px; }
        .feedback-eyebrow { color: #a45b28; font-size: 11px; font-weight: 800; letter-spacing: 1.3px; text-transform: uppercase; }
        .feedback-heading h1 { margin: 9px 0 7px; color: #3d2c20; font-size: clamp(27px, 5vw, 34px); line-height: 1.2; }
        .feedback-heading p { margin: 0; color: #76695f; font-size: 15px; line-height: 1.6; }
        .feedback-booking { display: flex; align-items: center; gap: 13px; margin-bottom: 27px; padding: 15px; border: 1px solid #eee3d7; border-radius: 12px; background: #fffaf5; }
        .feedback-booking-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border-radius: 12px; background: #f7e5d4; color: #9a4f20; }
        .feedback-booking strong, .feedback-booking span:not(.feedback-booking-icon) { display: block; }
        .feedback-booking strong { color: #49372b; font-size: 14px; }
        .feedback-booking div > span { margin-top: 3px; color: #78685e; font-size: 12px; }
        .feedback-rating-fieldset { margin: 0 0 25px; padding: 0; border: 0; }
        .feedback-rating-fieldset legend { color: #49372b; font-size: 15px; font-weight: 700; }
        .feedback-rating-fieldset legend span { color: #b65f25; }
        .feedback-rating-fieldset > p { margin: 5px 0 15px; color: #78685e; font-size: 13px; }
        .feedback-rating-options { display: flex; flex-wrap: wrap; gap: 10px; }
        .feedback-rating-option { position: relative; }
        .feedback-rating-option input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .feedback-rating-option label { display: flex; min-width: 64px; min-height: 54px; align-items: center; justify-content: center; gap: 7px; padding: 9px 13px; border: 1px solid #e6d9cd; border-radius: 11px; background: #fff; color: #7a6b60; cursor: pointer; font-weight: 700; transition: background-color .15s, border-color .15s, color .15s; }
        .feedback-rating-option label i { color: #c9b8a7; }
        .feedback-rating-option input:checked + label { border-color: #bf682e; background: #fff3e8; color: #8c4318; }
        .feedback-rating-option input:checked + label i, .feedback-rating-option label:hover i { color: #d88230; }
        .feedback-rating-option input:focus-visible + label { outline: 3px solid rgba(191, 104, 46, .28); outline-offset: 2px; }
        .feedback-comment-field label { display: block; margin-bottom: 8px; color: #49372b; font-size: 14px; font-weight: 700; }
        .feedback-comment-field label span { color: #8b7c70; font-weight: 400; }
        .feedback-comment-field textarea { display: block; width: 100%; min-height: 128px; padding: 13px 14px; border: 1px solid #e6d9cd; border-radius: 11px; background: #fffdfa; color: #38291f; font: inherit; font-size: 14px; line-height: 1.6; resize: vertical; }
        .feedback-comment-field textarea:focus { outline: 3px solid rgba(191, 104, 46, .17); border-color: #bf682e; }
        .feedback-comment-field textarea::placeholder { color: #a99a8f; }
        .feedback-field-footer { margin-top: 6px; color: #8b7c70; font-size: 11px; text-align: right; }
        .feedback-submit { display: flex; width: 100%; min-height: 48px; align-items: center; justify-content: center; gap: 10px; margin-top: 18px; border: 0; border-radius: 10px; background: #a95320; color: #fff; cursor: pointer; font: inherit; font-size: 15px; font-weight: 700; }
        .feedback-submit:hover { background: #8f4318; }
        .feedback-submit:focus-visible { outline: 3px solid rgba(169, 83, 32, .3); outline-offset: 3px; }
        .feedback-submit:disabled { cursor: wait; opacity: .7; }
        .feedback-privacy-note { margin: 11px 0 0; color: #8b7c70; font-size: 12px; text-align: center; }
        .feedback-alert { display: flex; gap: 10px; margin: 0 0 20px; padding: 13px 15px; border-radius: 10px; font-size: 14px; line-height: 1.55; }
        .feedback-alert--success { border: 1px solid #b8dfc7; background: #effaf2; color: #28623c; }
        .feedback-alert--info { border: 1px solid #ead8c7; background: #fff8f1; color: #704a2e; }
        .feedback-review { padding-top: 19px; border-top: 1px solid #eee3d7; }
        .feedback-review > strong { display: block; margin-bottom: 8px; color: #49372b; }
        .feedback-review-rating { color: #9a4f20; font-weight: 700; }
        .feedback-review-rating i { margin-right: 2px; color: #d88230; }
        .feedback-review p { margin: 12px 0 0; color: #5f5148; line-height: 1.7; white-space: pre-line; }
        .feedback-error { margin: 8px 0 0; color: #a33324; font-size: 13px; }
        @media (max-width: 480px) { .feedback-page { margin-top: 20px; } .feedback-rating-options { gap: 7px; } .feedback-rating-option label { min-width: 55px; padding: 8px; } }
    </style>
    <script>
        const feedbackForm = document.querySelector('[data-feedback-form]');
        if (feedbackForm) {
            feedbackForm.addEventListener('submit', () => {
                const button = feedbackForm.querySelector('button[type="submit"]');
                button.disabled = true;
                button.querySelector('[data-feedback-submit-label]').textContent = 'Submitting…';
            });
        }
    </script>
</x-shell>
