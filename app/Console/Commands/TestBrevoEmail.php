<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\BrevoMailer;

class TestBrevoEmail extends Command
{
    protected $signature = 'test:brevo';
    protected $description = 'Send test email using Brevo API';

    public function handle(BrevoMailer $mailer)
    {
        try {
            $mailer->send('fredrickjaradal25@gmail.com', 'Fredrick', 'Test from Laravel', '<h1>Hello Fred!</h1><p>This is a Brevo test email.</p>');
            $this->info('✅ Email sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}

