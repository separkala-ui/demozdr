<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up mock configuration
        Config::set('settings.recaptcha_site_key', 'test-site-key');
        Config::set('settings.recaptcha_secret_key', 'test-secret-key');
        Config::set('settings.recaptcha_enabled_pages', json_encode(['login', 'registration']));
        Config::set('settings.recaptcha_score_threshold', 0.5);
    }

    public function test_is_enabled_for_page_returns_true_when_configured()
    {
        $service = new RecaptchaService();

        $this->assertTrue($service->isEnabledForPage('login'));
        $this->assertTrue($service->isEnabledForPage('registration'));
        $this->assertFalse($service->isEnabledForPage('forgot_password'));
    }

    public function test_is_enabled_for_page_returns_false_when_no_keys()
    {
        Config::set('settings.recaptcha_site_key', '');
        Config::set('settings.recaptcha_secret_key', '');

        $service = new RecaptchaService();

        $this->assertFalse($service->isEnabledForPage('login'));
    }

    public function test_get_site_key_returns_configured_key()
    {
        $service = new RecaptchaService();

        $this->assertEquals('test-site-key', $service->getSiteKey());
    }

    public function test_verify_returns_false_when_no_response()
    {
        $service = new RecaptchaService();
        $request = Request::create('/', 'POST');

        $this->assertFalse($service->verify($request, 'login'));
    }

    public function test_verify_makes_http_request_when_response_present()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
                'action' => 'login',
            ]),
        ]);

        $service = new RecaptchaService();
        $request = Request::create('/', 'POST', ['g-recaptcha-response' => 'test-response']);

        $result = $service->verify($request, 'login');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.google.com/recaptcha/api/siteverify'
                && $request['secret'] === 'test-secret-key'
                && $request['response'] === 'test-response';
        });
    }

    public function test_get_available_pages_returns_expected_pages()
    {
        $pages = RecaptchaService::getAvailablePages();

        $this->assertArrayHasKey('login', $pages);
        $this->assertArrayHasKey('forgot_password', $pages);
    }

    public function test_verify_fails_when_score_below_threshold()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.3,
                'action' => 'login',
            ]),
        ]);

        $service = new RecaptchaService();
        $request = Request::create('/', 'POST', ['g-recaptcha-response' => 'test-response']);

        $result = $service->verify($request, 'login');

        $this->assertFalse($result);
    }

    public function test_verify_fails_when_action_mismatch()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
                'action' => 'registration',
            ]),
        ]);

        $service = new RecaptchaService();
        $request = Request::create('/', 'POST', ['g-recaptcha-response' => 'test-response']);

        $result = $service->verify($request, 'login');

        $this->assertFalse($result);
    }

    public function test_get_score_threshold_returns_configured_value()
    {
        Config::set('settings.recaptcha_score_threshold', 0.7);
        $service = new RecaptchaService();

        $this->assertEquals(0.7, $service->getScoreThreshold());
    }

    public function test_get_script_tag_returns_v3_script()
    {
        $service = new RecaptchaService();
        $scriptTag = $service->getScriptTag();

        $this->assertStringContainsString('https://www.google.com/recaptcha/api.js?render=test-site-key', $scriptTag);
    }

    public function test_get_html_returns_empty_for_v3()
    {
        $service = new RecaptchaService();
        $html = $service->getHtml();

        $this->assertEquals('', $html);
    }
}
