<?php

namespace App\Filament\Widgets;

use App\Models\Quoter;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ChatLogsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('thread_id')
                    ->label('Thread ID')
                    ->searchable()
                    ->limit(30)
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Primo messaggio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messaggi')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 20 => 'danger',
                        $state >= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('preview')
                    ->label('Anteprima')
                    ->getStateUsing(function (Quoter $record): string {
                        return mb_strimwidth((string) $record->content, 0, 80, '…');
                    })
                    ->limit(80)
                    ->wrap(),
            ])
            ->actions([
                Action::make('view_conversation')
                    ->label('Visualizza conversazione')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Quoter $record): string => 'Conversazione - Thread: '.$record->thread_id)
                    ->modalContent(function (Quoter $record): Htmlable {
                        $messages = Quoter::where('thread_id', $record->thread_id)
                            ->orderBy('created_at')
                            ->get();

                        return new HtmlString(
                            view('filament.widgets.conversation-modal', [
                                'messages' => $messages,
                                'threadId' => $record->thread_id,
                            ])->render()
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Chiudi'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->heading('Log chat raggruppati per thread');
    }

    /**
     * Restituisce una query che mostra un solo record per thread_id,
     * usando il primo messaggio del thread e un conteggio dei messaggi.
     */
    protected function getBaseQuery(): Builder
    {
        // Sottoquery: id del primo messaggio per ogni thread
        $firstPerThread = Quoter::query()
            ->selectRaw('MIN(id) as id')
            ->groupBy('thread_id');

        return Quoter::query()
            ->select('quoters.*')
            ->selectRaw('(select COUNT(*) from quoters q2 where q2.thread_id = quoters.thread_id) as messages_count')
            ->joinSub($firstPerThread, 'first_per_thread', 'first_per_thread.id', '=', 'quoters.id');
    }

    /**
     * Costruisce una stringa testuale con tutta la conversazione per un thread.
     */
    private function formatConversation(string $threadId): string
    {
        $messages = Quoter::where('thread_id', $threadId)
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty()) {
            return 'Nessun messaggio trovato per questo thread.';
        }

        $lines = [];

        foreach ($messages as $message) {
            $role = $message->role === 'user' ? 'Utente' : 'Chatbot';
            $timestamp = $message->created_at?->format('d/m/Y H:i:s') ?? '';
            $content = (string) $message->content;

            $lines[] = "[{$timestamp}] {$role}:\n{$content}";
        }

        $text = implode("\n\n".str_repeat('-', 80)."\n\n", $lines);

        $text .= "\n\nThread ID: {$threadId}\n";
        $text .= 'Totale messaggi: '.$messages->count();

        return $text;
    }
}
