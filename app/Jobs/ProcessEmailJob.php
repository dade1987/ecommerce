<?php

namespace App\Jobs;

use App\Models\IncomingEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI;

class ProcessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $emailId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $emailId)
    {
        $this->emailId = $emailId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = IncomingEmail::find($this->emailId);
        if (!$email) {
            Log::error("ProcessEmailJob: Could not find email with ID {$this->emailId}");
            return;
        }

        try {
            $openAiClient = OpenAI::client(config('services.openai.key'));
            $prompt = $this->buildPrompt($email);

            $response = $openAiClient->chat()->create([
                'model' => 'gpt-4o', // Using a newer model that is better with JSON
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an intelligent assistant that analyzes emails. Provide a concise analysis in Italian, which includes a summary and key points. Also, assign a priority level from 1 to 10. ALWAYS respond with a valid JSON object in the format: {"analysis": "...", "priority": X}. Do not include any other text or markdown.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'], // Force JSON output
            ]);

            $content = $response->choices[0]->message->content;
            $data = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($data['analysis'], $data['priority'])) {
                $email->update([
                    'analysis' => $data['analysis'],
                    'priority' => (int) $data['priority'],
                ]);
            } else {
                Log::error('Failed to decode or validate JSON response from OpenAI for email ID: ' . $email->id, [
                    'response' => $content,
                    'error' => json_last_error_msg(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing email with OpenAI for email ID: ' . $email->id . ' - ' . $e->getMessage());
        }
    }

    /**
     * Build the prompt for the OpenAI API.
     */
    private function buildPrompt(IncomingEmail $email): string
    {
        $body = strip_tags($email->body_html ?? $email->body_text ?? '');
        return "Subject: {$email->subject}\nFrom: {$email->from_address}\n\nBody:\n" . substr($body, 0, 8000);
    }
}
