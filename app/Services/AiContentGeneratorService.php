<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentGeneratorService
{
    private string $provider;
    private string $apiKey;

    public function __construct()
    {
        $this->provider = config('settings.ai_default_provider', 'openai');
        $this->setApiKey();
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        $this->setApiKey();

        return $this;
    }

    private function setApiKey(): void
    {
        $this->apiKey = match ($this->provider) {
            'openai' => config('settings.ai_openai_api_key'),
            'claude' => config('settings.ai_claude_api_key'),
            default => throw new Exception("Unsupported AI provider: {$this->provider}")
        };

        if (empty($this->apiKey)) {
            throw new Exception("API key not configured for provider: {$this->provider}");
        }
    }

    public function generateContent(string $prompt, string $type = 'general'): array
    {
        try {
            $systemPrompt = $this->getSystemPrompt($type);
            $response = $this->sendRequest($systemPrompt, $prompt);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            Log::error('AI Content Generation Error', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100) . '...',
            ]);

            throw $e;
        }
    }

    private function getSystemPrompt(string $type): string
    {
        return match ($type) {
            'post_content' => 'You are a content creation assistant. Generate well-structured blog post content including title, excerpt, and main content based on the user\'s requirements. Return the response in JSON format with keys: "title", "excerpt", and "content". Make the content engaging, SEO-friendly, and well-formatted with proper paragraphs.',
            'page_content' => 'You are a web page content creation assistant. Generate professional page content including title, excerpt, and main content based on the user\'s requirements. Return the response in JSON format with keys: "title", "excerpt", and "content". Make the content informative, professional, and well-structured.',
            default => 'You are a helpful content creation assistant. Generate content based on the user\'s requirements and return it in JSON format with appropriate keys.'
        };
    }

    private function sendRequest(string $systemPrompt, string $userPrompt): Response
    {
        return match ($this->provider) {
            'openai' => $this->sendOpenAiRequest($systemPrompt, $userPrompt),
            'claude' => $this->sendClaudeRequest($systemPrompt, $userPrompt),
            default => throw new Exception("Unsupported provider: {$this->provider}")
        };
    }

    private function sendOpenAiRequest(string $systemPrompt, string $userPrompt): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ]);
    }

    private function sendClaudeRequest(string $systemPrompt, string $userPrompt): Response
    {
        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 1500,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);
    }

    private function parseResponse(Response $response): array
    {
        if (! $response->successful()) {
            throw new Exception('AI API request failed: ' . $response->body());
        }

        $data = $response->json();

        $content = match ($this->provider) {
            'openai' => $data['choices'][0]['message']['content'] ?? '',
            'claude' => $data['content'][0]['text'] ?? '',
            default => throw new Exception("Unknown provider: {$this->provider}")
        };

        // Try to parse as JSON first
        $parsedContent = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
            return $parsedContent;
        }

        // If not valid JSON, return as plain content
        return [
            'title' => 'Generated Title',
            'excerpt' => 'Generated excerpt from AI',
            'content' => $content,
        ];
    }

    public function getAvailableProviders(): array
    {
        $providers = [];

        if (config('settings.ai_openai_api_key')) {
            $providers['openai'] = 'OpenAI';
        }

        if (config('settings.ai_claude_api_key')) {
            $providers['claude'] = 'Claude (Anthropic)';
        }

        return $providers;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->getAvailableProviders());
    }
}
