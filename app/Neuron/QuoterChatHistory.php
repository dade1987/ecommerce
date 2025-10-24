<?php

declare(strict_types=1);

namespace App\Neuron;

use App\Models\Quoter;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * QuoterChatHistory
 *
 * Memory provider persistente che salva la chat history in Quoter.
 * Consente di mantenere il contesto tra le richieste HTTP usando il thread_id.
 */
class QuoterChatHistory implements ChatHistoryInterface
{
    private array $messages = [];

    public function __construct(private string $threadId)
    {
        $this->loadFromDatabase();
    }

    /**
     * Carica i messaggi dal database
     */
    private function loadFromDatabase(): void
    {
        $records = Quoter::where('thread_id', $this->threadId)
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content']);

        foreach ($records as $record) {
            if ($record->role === 'user') {
                $this->messages[] = new UserMessage($record->content);
            } elseif ($record->role === 'chatbot') {
                $this->messages[] = new AssistantMessage($record->content);
            }
        }
    }

    /**
     * Aggiungi un messaggio alla history e salva nel database
     */
    public function addMessage(Message $message): ChatHistoryInterface
    {
        $this->messages[] = $message;

        // Estrai il contenuto e convertilo a string
        $content = $message->getContent();
        if (is_array($content)) {
            $content = json_encode($content);
        } elseif ($content === null) {
            $content = '';
        } else {
            $content = (string) $content;
        }

        // Salva nel database solo se il contenuto non è vuoto
        if (!empty(trim($content))) {
            $role = $message instanceof UserMessage ? 'user' : 'chatbot';
            Quoter::create([
                'thread_id' => $this->threadId,
                'role' => $role,
                'content' => $content,
            ]);
        }

        return $this;
    }

    /**
     * Ritorna tutti i messaggi della history
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Ritorna l'ultimo messaggio o false
     */
    public function getLastMessage(): Message|false
    {
        return end($this->messages) ?: false;
    }

    /**
     * Imposta i messaggi della history
     */
    public function setMessages(array $messages): ChatHistoryInterface
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Pulisci tutti i messaggi
     */
    public function flushAll(): ChatHistoryInterface
    {
        Quoter::where('thread_id', $this->threadId)->delete();
        $this->messages = [];
        return $this;
    }

    /**
     * Calcola l'uso totale dei token (stub - non implementato)
     */
    public function calculateTotalUsage(): int
    {
        return 0;
    }

    /**
     * Serializza per JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'threadId' => $this->threadId,
            'messageCount' => count($this->messages),
            'messages' => $this->messages,
        ];
    }
}
