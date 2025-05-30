<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email verification functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $verificationCode = '123456';
        
        // Tạo fake user để test 
        $user = new class {
            public $id = 'test-123';
            public $firstname = 'Test';
            public $lastname = 'User';
            public $email;
            
            public function __construct() {
                // Constructor
            }
        };
        $user->email = $email;
        
        try {
            $this->info("Testing email verification for: {$email}");
            
            // Test notification trực tiếp
            $notification = new EmailVerificationNotification($verificationCode);
            $mailMessage = $notification->toMail($user);
            
            $this->info("✅ Email notification created successfully!");
            $this->info("Subject: " . $mailMessage->subject);
            $this->info("View used: " . ($mailMessage->view ?? 'default'));
            $this->info("Check storage/logs/laravel.log for detailed email content");
            
            // Log thông tin để debug
            Log::info('Test email verification', [
                'email' => $email,
                'verification_code' => $verificationCode,
                'subject' => $mailMessage->subject,
                'view' => $mailMessage->view ?? 'none'
            ]);
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to test email: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
