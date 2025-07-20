<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Browsershot\Browsershot;
use App\Models\User;

class GenerateScreenshots extends Command
{
    protected $signature = 'app:generate-screenshots {--debug}';
    protected $description = 'Generate screenshots of application pages';

    public function handle()
    {
        $defaultWidth = 1024;
        $defaultHeight = 600;

        // Generate guest pages
        $this->info('Generating guest pages screenshots...');
        foreach ($this->guestModePages() as $route) {
            $this->generateScreenshot($route, $defaultWidth, $defaultHeight);
        }

        // Generate authenticated pages
        $this->info('Generating authenticated pages screenshots...');
        foreach ($this->authenticatedPages() as $route) {
            $this->generateAuthenticatedScreenshot($route, $defaultWidth, $defaultHeight);
        }

        $this->info('Screenshots updated!');
    }

    private function generateScreenshot($route, $defaultWidth, $defaultHeight)
    {
        $browsershot = Browsershot::url(config('app.url') . $route['url'])
            ->waitUntilNetworkIdle()
            ->windowSize($route['width'] ?? $defaultWidth, $route['height'] ?? $defaultHeight);

        if (isset($route['mode']) && $route['mode'] === 'dark') {
            $browsershot->click('.dark-mode-toggle')->waitUntilNetworkIdle();
        }

        $browsershot->save("demo-screenshots-2/{$route['name']}.png");
        $this->info("Generated: {$route['name']}.png");
    }

    private function generateAuthenticatedScreenshot($route, $defaultWidth = 1200, $defaultHeight = 800)
    {
        try {
            $this->info("Generating: {$route['name']}");

            // Create a session for the user
            $user = User::where('email', 'superadmin@example.com')->first();
            $sessionId = $this->createUserSession($user);

            // Get the application URL and domain
            $appUrl = config('app.url');
            $domain = parse_url($appUrl, PHP_URL_HOST);

            // Create the browser instance with the authenticated session
            $browser = Browsershot::url($appUrl . $route['url'])
                ->useCookies([
                    'laravel_session' => $sessionId
                ], $domain)
                ->setDelay(500)
                ->setOption('args', [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-web-security',
                    '--disable-features=VizDisplayCompositor',
                ])
                ->waitUntilNetworkIdle();

            // Apply dark mode if needed.
            if (isset($route['mode']) && $route['mode'] === 'dark') {
                $browser->click('.dark-mode-toggle')
                    ->wait(2000)
                    ->waitUntilNetworkIdle(true);
            }

            // Take final screenshot.
            $browser->windowSize($route['width'] ?? $defaultWidth, $route['height'] ?? $defaultHeight)
                ->save("demo-screenshots-2/{$route['name']}.png");

            $this->info("✓ Generated: {$route['name']}.png");
        } catch (\Exception $e) {
            $this->error("Failed to generate {$route['name']}: " . $e->getMessage());
        }
    }

    /**
     * Create a session for the user and return the session ID
     */
    private function createUserSession($user)
    {
        // Make sure we're not in production
        if (app()->environment('production')) {
            throw new \Exception('This functionality is not available in production environment');
        }

        // Create a new session
        $session = app('session');
        $session->start();

        // Log the user in
        auth()->login($user);

        // Get the session ID
        $sessionId = $session->getId();

        return $sessionId;
    }

    public function guestModePages()
    {
        return [
            [
                'name' => '00-Login-Page-Lite-Mode',
                'url' => '/admin/login',
                'mode' => 'lite',
            ],
            [
                'name' => '01-Forget-password',
                'url' => '/admin/password/reset',
                'mode' => 'dark',
            ],
        ];
    }

    public function authenticatedPages()
    {
        return [
            [
                'name' => '03-Dashboard-Page-lite-Mode',
                'url' => '/admin',
                'mode' => 'lite',
            ],
            [
                'name' => '05-Role-List-Lite',
                'url' => '/admin/roles',
                'mode' => 'lite',
            ],
            [
                'name' => '06-Role-List-Dark',
                'url' => '/admin/roles',
                'mode' => 'dark',
            ],
            [
                'name' => '11-User-List-Dark-Mode',
                'url' => '/admin/users',
                'mode' => 'dark',
            ],
        ];
    }
}
