<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:push {email? : The email of the user to send the push to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test push notification to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $user = \App\Models\User::where('email', $email)->first();
            if (!$user) {
                $this->error("User with email {$email} not found.");
                return;
            }
        } else {
            $user = \App\Models\User::first();
            if (!$user) {
                $this->error("No users found in the database.");
                return;
            }
        }

        $this->info("Sending test push notification to {$user->name} ({$user->email})...");

        $service = new \App\Services\PushNotificationService();
        $service->sendToUser(
            $user, 
            'Vyora Test Push', 
            'This is a test notification from the Vyora system! 🚀', 
            ['type' => 'test', 'id' => '123']
        );

        $this->info("Push request sent to SaaSance relay successfully.");
    }
}
