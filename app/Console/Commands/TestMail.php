<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestMail extends Command
{
    protected $signature = 'mctbs:test-mail {to : Email address that receives the test}';

    protected $description = 'Send a test email with the current mail settings and show any delivery error.';

    public function handle(): int
    {
        $this->line('Mailer: '.config('mail.default'));
        $this->line('From:   '.config('mail.from.address'));
        $this->line('API key set: '.(config('services.brevo.key') ? 'yes' : 'NO'));

        try {
            Mail::raw('This is a test email from MCTBS.', function ($message): void {
                $message->to($this->argument('to'))->subject('MCTBS test email');
            });
        } catch (Throwable $exception) {
            $this->error('Failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Sent. Check the inbox (and spam) of '.$this->argument('to').'.');

        return self::SUCCESS;
    }
}
