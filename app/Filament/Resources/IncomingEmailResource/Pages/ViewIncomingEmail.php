<?php

namespace App\Filament\Resources\IncomingEmailResource\Pages;

use App\Filament\Resources\IncomingEmailResource;
use App\Mail\EmailReplyMail;
use App\Models\IncomingEmail;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ViewIncomingEmail extends ViewRecord
{
    protected static string $resource = IncomingEmailResource::class;

    public function mount($record): void
    {
        parent::mount($record);
        
        $email = $this->getRecord();
        if (!$email->is_read) {
            $email->update(['is_read' => true]);
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Dettagli Email')
                    ->schema([
                        Components\TextEntry::make('from_address')->label('Da'),
                        Components\TextEntry::make('subject')->label('Oggetto'),
                        Components\TextEntry::make('received_at')->label('Ricevuto il')->dateTime('d/m/Y H:i'),
                    ])->columns(2),
                
                Components\Section::make('Analisi AI')
                    ->schema([
                        Components\TextEntry::make('analysis')
                            ->label('')
                            ->html()
                            ->columnSpanFull(),
                    ])->collapsible(),

                Components\Section::make('Messaggio Originale')
                    ->schema([
                        ViewEntry::make('body_html')
                            ->label('')
                            ->view('filament.infolists.components.html-viewer')
                            ->columnSpanFull(),
                    ])->collapsible()->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_as_unread')
                ->label('Segna come non letto')
                ->icon('heroicon-o-envelope')
                ->action(function (IncomingEmail $record) {
                    $record->update(['is_read' => false]);
                    $this->refresh();
                }),
            Actions\Action::make('reply')
                ->label('Rispondi')
                ->icon('heroicon-o-arrow-uturn-left')
                ->form([
                    Forms\Components\RichEditor::make('reply_body')
                        ->label('Messaggio di risposta')
                        ->required(),
                ])
                ->action(function (array $data, IncomingEmail $record) {
                    try {
                        // Send the email
                        Mail::to($record->from_address)
                            ->send(new EmailReplyMail($data['reply_body'], $record->subject));

                        // Save the sent email to the database
                        IncomingEmail::create([
                            'parent_id' => $record->id,
                            'from_address' => config('mail.from.address'),
                            'to_address' => [$record->from_address],
                            'subject' => 'Re: ' . $record->subject,
                            'body_html' => $data['reply_body'],
                            'type' => 'sent',
                            'is_read' => true,
                            'received_at' => now(),
                            'message_id' => uniqid(), // A simple unique ID for sent items
                        ]);

                        Notification::make()
                            ->title('Risposta inviata con successo')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Log::error('Failed to send email reply: ' . $e->getMessage());
                        Notification::make()
                            ->title('Errore durante l\'invio della risposta')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
