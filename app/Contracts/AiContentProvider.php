<?php

namespace App\Contracts;

interface AiContentProvider
{
    /** @return array{content: array, provider: string, model: string, input_tokens: int|null, output_tokens: int|null} */
    public function generate(string $systemPrompt, string $userPrompt): array;
}
