<?php

namespace App\Console\Commands;

use App\Models\IncomingEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;
use App\Jobs\ProcessEmailJob;

class FetchEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch incoming emails from the configured IMAP account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Connecting to IMAP server...');

        try {
            $client = Client::account('default');
            $client->connect();
            $this->info('Connection successful.');

            $folder = $client->getFolder('INBOX');
            $messages = $folder->messages()->unseen()->get();

            $this->info("Found {$messages->count()} unseen emails.");

            foreach ($messages as $message) {
                try {
                    $this->info("Processing email: {$message->getSubject()}");

                    $from = $message->getFrom()[0];

                    $newEmail = IncomingEmail::updateOrCreate([
                        'message_id' => $message->getMessageId(),
                    ], [
                        'subject' => $message->getSubject(),
                        'from_address' => is_object($from) ? $from->mail : $from,
                        'to_address' => json_encode(collect($message->getTo())->map(fn ($address) => is_object($address) ? $address->mail : $address)->toArray()),
                        'body_html' => $message->getHTMLBody(true),
                        'body_text' => $message->getTextBody(),
                        'is_read' => false,
                        'received_at' => $message->getDate(),
                    ]);

                    $this->info("Dispatching job for email ID: {$newEmail->id}");
                    ProcessEmailJob::dispatch($newEmail->id);

                } catch (\Exception $e) {
                    $messageId = 'unknown';
                    try {
                        $messageId = $message->getMessageId();
                    } catch (\Exception $me) {
                        // ignore if we can't even get the message ID
                    }
                    Log::error('IMAP Fetch Error processing message ID ' . $messageId . ': ' . $e->getMessage());
                    $this->error("Failed to process email with Message-ID: {$messageId}. Skipping.");
                    continue; // Skip to the next message
                }
            }

            $this->info('Email fetching complete. Jobs dispatched for processing.');

        } catch (\Exception $e) {
            Log::error('IMAP Connection Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            $this->error('Failed to connect to IMAP server. Check credentials and logs.');
        }
    }
}
