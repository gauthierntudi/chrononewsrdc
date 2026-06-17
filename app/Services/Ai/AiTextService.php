<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiTextService
{
    protected string $provider;

    protected string $apiKey;

    protected string $endpoint;

    protected string $model;

    public function __construct()
    {
        $config = config('chrononews.ai', []);
        $this->provider = (string) ($config['provider'] ?? 'gemini');
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->endpoint = (string) ($config['endpoint'] ?? $this->defaultEndpoint());
        $this->model = (string) ($config['model'] ?? $this->defaultModel());
    }

    public function isEnabled(): bool
    {
        return (bool) config('chrononews.ai.enabled', false)
            && ($this->provider === 'huggingface' || $this->apiKey !== '');
    }

    /** @return array{enabled: bool, provider: string, model: string} */
    public function publicConfig(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'provider' => $this->provider,
            'model' => $this->model,
        ];
    }

    public function process(string $action, string $text): string
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Assistant IA non configuré. Définissez AI_ENABLED=true et AI_API_KEY dans .env');
        }

        $text = trim($text);
        if ($text === '') {
            throw new RuntimeException('Le texte est vide');
        }

        if (! in_array($action, ['correct', 'rewrite', 'improve'], true)) {
            throw new RuntimeException('Action IA invalide');
        }

        $endpoint = $this->buildEndpoint();
        $body = $this->buildRequestBody($action, $text);
        $headers = $this->buildHeaders();

        try {
            $response = Http::timeout(60)
                ->withHeaders($headers)
                ->post($endpoint, $body);
        } catch (RequestException $exception) {
            $message = $exception->response?->json('error.message')
                ?? $exception->response?->json('error')
                ?? $exception->getMessage();

            throw new RuntimeException(is_string($message) ? $message : 'Erreur API IA');
        }

        if (! $response->successful()) {
            $message = $response->json('error.message')
                ?? $response->json('error')
                ?? ('Erreur API IA: '.$response->status());

            throw new RuntimeException(is_string($message) ? $message : 'Erreur API IA');
        }

        $result = $this->extractResponse($response->json());

        if ($result === '') {
            throw new RuntimeException('Réponse IA vide');
        }

        return $result;
    }

    protected function defaultEndpoint(): string
    {
        return match ($this->provider) {
            'huggingface' => 'https://api-inference.huggingface.co/models/',
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'grok' => 'https://api.x.ai/v1/chat/completions',
            'together' => 'https://api.together.xyz/v1/chat/completions',
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'anthropic' => 'https://api.anthropic.com/v1/messages',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/',
            default => 'https://generativelanguage.googleapis.com/v1beta/models/',
        };
    }

    protected function defaultModel(): string
    {
        return match ($this->provider) {
            'huggingface' => 'mistralai/Mixtral-8x7B-Instruct-v0.1',
            'groq' => 'mixtral-8x7b-32768',
            'grok' => 'grok-beta',
            'together' => 'mistralai/Mixtral-8x7B-Instruct-v0.1',
            'openai' => 'gpt-3.5-turbo',
            'anthropic' => 'claude-3-haiku-20240307',
            'gemini' => 'gemini-2.5-flash',
            default => 'gemini-2.5-flash',
        };
    }

    protected function buildEndpoint(): string
    {
        if ($this->provider === 'huggingface') {
            return rtrim($this->endpoint, '/').'/'.$this->model;
        }

        if ($this->provider === 'gemini') {
            return rtrim($this->endpoint, '/').'/'.$this->model.':generateContent?key='.$this->apiKey;
        }

        return $this->endpoint;
    }

    /** @return array<string, mixed> */
    protected function buildRequestBody(string $action, string $text): array
    {
        $systemPrompt = $this->systemPrompt($action);

        if ($this->provider === 'huggingface') {
            return [
                'inputs' => "<s>[INST] {$systemPrompt}\n\nTexte à traiter:\n{$text} [/INST]",
                'parameters' => [
                    'max_new_tokens' => 1000,
                    'temperature' => 0.7,
                    'top_p' => 0.95,
                    'return_full_text' => false,
                ],
                'options' => [
                    'wait_for_model' => true,
                ],
            ];
        }

        if ($this->provider === 'anthropic') {
            return [
                'model' => $this->model,
                'max_tokens' => 2000,
                'messages' => [
                    ['role' => 'user', 'content' => "{$systemPrompt}\n\nTexte à traiter:\n{$text}"],
                ],
            ];
        }

        if ($this->provider === 'gemini') {
            return [
                'contents' => [[
                    'parts' => [[
                        'text' => "{$systemPrompt}\n\nTexte à traiter:\n{$text}",
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2000,
                ],
            ];
        }

        return [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ];
    }

    /** @return array<string, string> */
    protected function buildHeaders(): array
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($this->apiKey === '' || $this->provider === 'gemini') {
            return $headers;
        }

        if ($this->provider === 'anthropic') {
            $headers['x-api-key'] = $this->apiKey;
            $headers['anthropic-version'] = '2023-06-01';

            return $headers;
        }

        $headers['Authorization'] = 'Bearer '.$this->apiKey;

        return $headers;
    }

    protected function systemPrompt(string $action): string
    {
        return match ($action) {
            'rewrite' => 'Tu es un rédacteur expert. Réécris le texte suivant de manière plus fluide et naturelle tout en conservant le sens original. Garde le même ton et la même longueur approximative. Retourne UNIQUEMENT le texte réécrit, sans commentaire ni explication.',
            'improve' => 'Tu es un expert en rédaction. Améliore le texte suivant en le rendant plus clair, plus impactant et plus professionnel. Enrichis le vocabulaire et optimise la structure des phrases. Retourne UNIQUEMENT le texte amélioré, sans commentaire ni explication.',
            default => 'Tu es un correcteur professionnel. Corrige uniquement les fautes d\'orthographe et de grammaire dans le texte suivant. Conserve le style et le ton original. Retourne UNIQUEMENT le texte corrigé, sans commentaire ni explication.',
        };
    }

    /** @param array<string, mixed>|null $data */
    protected function extractResponse(?array $data): string
    {
        if (! is_array($data)) {
            return '';
        }

        if ($this->provider === 'huggingface') {
            if (isset($data[0]['generated_text'])) {
                return trim((string) $data[0]['generated_text']);
            }

            return trim((string) ($data['generated_text'] ?? ''));
        }

        if ($this->provider === 'anthropic') {
            return trim((string) ($data['content'][0]['text'] ?? ''));
        }

        if ($this->provider === 'gemini') {
            return trim((string) ($data['candidates'][0]['content']['parts'][0]['text'] ?? ''));
        }

        return trim((string) ($data['choices'][0]['message']['content'] ?? ''));
    }
}
