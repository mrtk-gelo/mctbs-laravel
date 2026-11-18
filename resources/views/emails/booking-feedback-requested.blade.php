Hello {{ $guestName }},

Thank you for staying at {{ $booking->unit->house->name }}. We would appreciate your rating and feedback about your stay.

Share your feedback using this secure link:
{!! $feedbackUrl !!}

This link expires in 7 days and can be used to submit one review for booking {{ $booking->booking_reference }}.
