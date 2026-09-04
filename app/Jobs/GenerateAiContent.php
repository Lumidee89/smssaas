<?php

namespace App\Jobs;

use App\Contracts\AiContentProvider;
use App\Models\AiGenerationRequest;
use App\Services\Ai\CopilotPromptFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateAiContent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public readonly int $requestId)
    {
        $this->onQueue('ai')->afterCommit();
    }

    public function handle(AiContentProvider $provider, CopilotPromptFactory $prompts): void
    {
        $request = AiGenerationRequest::findOrFail($this->requestId);
        if ($request->status === 'completed') {
            return;
        }
        $request->update(['status' => 'processing', 'error' => null]);
        [$system,$user] = $prompts->make($request->type, $request->input);
        $result = $provider->generate($system, $user);
        $request->update(['status' => 'completed', 'output' => $result['content'], 'provider' => $result['provider'], 'model' => $result['model'], 'input_tokens' => $result['input_tokens'], 'output_tokens' => $result['output_tokens'], 'completed_at' => now()]);
    }

    public function failed(?Throwable $exception): void
    {
        AiGenerationRequest::whereKey($this->requestId)->update(['status' => 'failed', 'error' => str($exception?->getMessage() ?? 'Generation failed.')->limit(1000)]);
    }
}
