<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vapid:generate {--show : Display the keys instead of saving to .env}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Generate new VAPID key pair for Web Push notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Generating VAPID key pair...');

            // Generate VAPID keys
            $vapidKeys = VAPID::createVapidKeys();

            $publicKey = $vapidKeys['publicKey'];
            $privateKey = $vapidKeys['privateKey'];

            if ($this->option('show')) {
                $this->displayKeys($publicKey, $privateKey);

                return self::SUCCESS;
            }

            // Save to .env file
            $envPath = base_path('.env');
            if (! file_exists($envPath)) {
                $this->error('.env file not found at '.$envPath);
                $this->displayKeys($publicKey, $privateKey);

                return self::FAILURE;
            }

            $envContent = file_get_contents($envPath);

            // Update or add VAPID keys
            if (str_contains($envContent, 'VAPID_PUBLIC_KEY=')) {
                $envContent = preg_replace(
                    '/VAPID_PUBLIC_KEY=.*/i',
                    'VAPID_PUBLIC_KEY='.$publicKey,
                    $envContent
                );
            } else {
                $envContent .= "\nVAPID_PUBLIC_KEY=".$publicKey;
            }

            if (str_contains($envContent, 'VAPID_PRIVATE_KEY=')) {
                $envContent = preg_replace(
                    '/VAPID_PRIVATE_KEY=.*/i',
                    'VAPID_PRIVATE_KEY='.$privateKey,
                    $envContent
                );
            } else {
                $envContent .= "\nVAPID_PRIVATE_KEY=".$privateKey;
            }

            if (str_contains($envContent, 'VAPID_SUBJECT=')) {
                // Keep existing subject or use default
                if (! str_contains($envContent, 'VAPID_SUBJECT=mailto:')) {
                    $envContent = preg_replace(
                        '/VAPID_SUBJECT=.*/i',
                        'VAPID_SUBJECT=mailto:hello@example.com',
                        $envContent
                    );
                }
            } else {
                $envContent .= "\nVAPID_SUBJECT=mailto:hello@example.com";
            }

            file_put_contents($envPath, $envContent);

            $this->info('✓ VAPID keys generated and saved to .env');
            $this->newLine();
            $this->displayKeys($publicKey, $privateKey);
            $this->info('You can also configure these in Admin Settings → Platform Settings.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error generating VAPID keys: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Display the VAPID keys in a formatted way
     */
    private function displayKeys(string $publicKey, string $privateKey): void
    {
        $this->newLine();
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->line('<fg=green>VAPID Keys Generated</>');
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->newLine();

        $this->line('<fg=yellow>Public Key:</> (share with browsers)');
        $this->line('<fg=blue>'.$publicKey.'</>');
        $this->newLine();

        $this->line('<fg=yellow>Private Key:</> (keep secret - add to .env or Admin Settings)');
        $this->line('<fg=red>'.$privateKey.'</>');
        $this->newLine();

        $this->line('<fg=yellow>Subject:</> (add your email or website URL)');
        $this->line('mailto:admin@example.com');
        $this->newLine();

        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
    }
}
