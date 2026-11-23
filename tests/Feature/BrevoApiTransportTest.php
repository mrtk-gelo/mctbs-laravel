<?php

use App\Mail\BookingTicket;
use App\Models\Booking;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

function makeTransportTicket(string $pdfContents): BookingTicket
{
    $house = new TransientHouse(['name' => 'Email Test House']);
    $unit = new Unit([
        'unit_type' => 'Family Room',
        'check_in_time' => '15:00:00',
        'check_out_time' => '11:00:00',
    ]);
    $unit->setRelation('house', $house);

    $guest = new User(['first_name' => 'Mail', 'last_name' => 'Guest']);
    $booking = new Booking([
        'booking_reference' => 'MCTBS-000777',
        'check_in' => now()->addDay(),
        'check_out' => now()->addDays(2),
    ]);
    $booking->setRelation('unit', $unit);
    $booking->setRelation('guest', $guest);

    return new BookingTicket($booking, $pdfContents);
}

it('sends HTML, text, reply-to, and PDF attachment fields through Brevo', function () {
    config([
        'services.brevo.key' => 'test-only-api-key',
        'mail.from.address' => 'mail@example.com',
        'mail.from.name' => 'MCTBS',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => 'test-id'], 201),
    ]);
    $pdfContents = "%PDF-1.7\nTicket test";

    Mail::mailer('brevo')->to('guest@example.com')->send(
        makeTransportTicket($pdfContents)->replyTo('support@example.com', 'MCTBS Support')
    );

    Http::assertSent(function (Request $request) use ($pdfContents): bool {
        $payload = $request->data();

        return $request->url() === 'https://api.brevo.com/v3/smtp/email'
            && $request->hasHeader('api-key', 'test-only-api-key')
            && isset($payload['htmlContent'], $payload['textContent'])
            && str_contains($payload['htmlContent'], 'Your booking is confirmed')
            && str_contains($payload['textContent'], 'Your booking is confirmed')
            && $payload['replyTo']['email'] === 'support@example.com'
            && $payload['attachment'][0]['name'] === 'booking-ticket-MCTBS-000777.pdf'
            && $payload['attachment'][0]['content'] === base64_encode($pdfContents)
            && ! array_key_exists('api-key', $payload);
    });
});

it('surfaces Brevo request failures instead of reporting successful delivery', function () {
    config(['services.brevo.key' => 'test-only-api-key']);
    Http::preventStrayRequests();
    Http::fake([
        'https://api.brevo.com/v3/smtp/email' => Http::response(['message' => 'rejected'], 500),
    ]);

    expect(fn () => Mail::mailer('brevo')->to('guest@example.com')->send(makeTransportTicket('%PDF')))
        ->toThrow(RequestException::class);
});
