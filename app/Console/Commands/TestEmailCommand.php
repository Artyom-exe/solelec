<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        try {
            Mail::raw('Test email from SOLELEC', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - SOLELEC');
            });

            $this->info("Email sent successfully to: {$email}");
        } catch (\Exception $e) {
            $this->error("Error sending email: " . $e->getMessage());
        }
    }
}
