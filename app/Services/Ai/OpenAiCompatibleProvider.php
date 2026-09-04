<?php

namespace App\Services\Ai;

use App\Contracts\AiContentProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiCompatibleProvider implements AiContentProvider
{
    public function generate(string $systemPrompt, string $userPrompt): array
    {
        $key = config('services.ai.api_key');
        $model = config('services.ai.model');
        if (! $key || ! $model) {
            throw new RuntimeException('AI provider credentials are not configured.');
        }
        $response = Http::withToken($key)->timeout(90)->retry(2, 500)->post(rtrim(config('services.ai.base_url'), '/').'/chat/completions', [
            'model' => $model,
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object'],
            'messages' => [['role' => 'system', 'content' => $systemPrompt], ['role' => 'user', 'content' => $userPrompt]],
        ])->throw()->json();
        $raw = data_get($response, 'choices.0.message.content');
        $content = is_string($raw) ? json_decode($raw, true, flags: JSON_THROW_ON_ERROR) : null;
        if (! is_array($content)) {
            throw new RuntimeException('AI provider returned an invalid structured response.');
        }

        return ['content' => $content, 'provider' => 'compatible-chat-completions', 'model' => $model, 'input_tokens' => data_get($response, 'usage.prompt_tokens'), 'output_tokens' => data_get($response, 'usage.completion_tokens')];
    }
}
