<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

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

        if ($replyTo = $email->getReplyTo()[0] ?? null) {
            $payload['replyTo'] = array_filter([
                'email' => $replyTo->getAddress(),
                'name' => $replyTo->getName(),
            ]);
        }

        if ($email->getHtmlBody()) {
            $payload['htmlContent'] = $email->getHtmlBody();
        }

        if ($email->getTextBody()) {
            $payload['textContent'] = $email->getTextBody();
        }

        if ($attachments = $email->getAttachments()) {
            $payload['attachment'] = array_map(
                fn ($attachment) => [
                    'name' => $attachment->getFilename() ?? 'attachment',
                    'content' => base64_encode($this->getAttachmentContents($attachment)),
                ],
                $attachments
            );
        }

        Http::connectTimeout(20)
            ->timeout(45)
            ->withOptions(['force_ip_resolve' => 'v4'])
            ->withHeaders(['api-key' => $this->apiKey])
            ->acceptJson()
            ->post('https://api.brevo.com/v3/smtp/email', $payload)
            ->throw();
    }

    private function getAttachmentContents(DataPart $attachment): string
    {
        $body = $attachment->getBody();

        if (is_string($body)) {
            return $body;
        }

        if ($body instanceof File) {
            return $body->getContent();
        }

        if (is_resource($body)) {
            $contents = stream_get_contents($body);

            if ($contents !== false) {
                return $contents;
            }
        }

        throw new \RuntimeException('Unable to read email attachment contents.');
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }
}
