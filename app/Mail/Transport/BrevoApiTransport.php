<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
    public function __construct(private string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $from = $email->getFrom()[0];

        $payload = [
            'sender' => array_filter([
                'email' => $from->getAddress(),
                'name' => $from->getName(),
            ]),
            'to' => array_map(
                fn ($to) => array_filter([
                    'email' => $to->getAddress(),
                    'name' => $to->getName(),
                ]),
                $email->getTo()
            ),
            'subject' => $email->getSubject(),
        ];

        if ($email->getHtmlBody()) {
            $payload['htmlContent'] = $email->getHtmlBody();
        }

        if ($email->getTextBody()) {
            $payload['textContent'] = $email->getTextBody();
        }

        Http::withHeaders(['api-key' => $this->apiKey])
            ->acceptJson()
            ->post('https://api.brevo.com/v3/smtp/email', $payload)
            ->throw();
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }
}