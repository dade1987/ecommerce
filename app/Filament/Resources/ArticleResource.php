<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\Tag;
use App\Services\OpenAi\OpenAiLanguageDetector;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use function Safe\json_decode;
use function Safe\preg_match;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tags')
                    ->multiple()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Tag::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray())
                    ->getOptionLabelsUsing(fn (array $values): array => Tag::whereIn('id', $values)->pluck('name', 'id')->toArray())
                    ->relationship(name: 'tags', titleAttribute: 'name'),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true) // Met à jour le slug après modification du titre
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true) // Vérifie l'unicité sauf lors de la modification
                    ->dehydrated() // Assure que la valeur est bien envoyée au modèle
                    ->disabled(fn ($livewire) => $livewire instanceof Pages\EditArticle) // Édition désactivée sauf lors de la modification
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? '')))
                    ->reactive(),

                Forms\Components\Textarea::make('summary')
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->hintAction(
                        Action::make('generateWithGpt')
                            ->icon('heroicon-m-sparkles')
                            ->label('Generate with AI')
                            ->modalHeading('Genera contenuto (con lingua suggerita)')
                            ->modalSubmitActionLabel('Genera')
                            ->requiresConfirmation()
                            ->form([
                                Select::make('language')
                                    ->label('Lingua articolo')
                                    ->options(static::languageOptions())
                                    ->required()
                                    ->helperText('Suggerita automaticamente in base al titolo (puoi cambiarla).'),
                                Placeholder::make('language_suggestion')
                                    ->label('Suggerimento')
                                    ->content(function (callable $get): string {
                                        $detected = $get('detected_language');
                                        $name = $get('detected_language_name');
                                        $conf = $get('detected_language_confidence');

                                        $detected = is_string($detected) ? $detected : '';
                                        $name = is_string($name) ? $name : '';
                                        $conf = is_numeric($conf) ? (float) $conf : null;

                                        if ($detected === '') {
                                            return 'Non disponibile (fallback: IT).';
                                        }

                                        $confText = $conf !== null ? (string) round($conf * 100).'%' : '—';

                                        return trim("Rilevata: {$name} ({$detected}) • Confidenza: {$confText}");
                                    }),
                                Hidden::make('detected_language'),
                                Hidden::make('detected_language_name'),
                                Hidden::make('detected_language_confidence'),
                            ])
                            ->fillForm(function (callable $get, OpenAiLanguageDetector $detector): array {
                                $title = $get('title');
                                $title = is_string($title) ? trim($title) : '';

                                $prompt = $get('content');
                                $prompt = is_string($prompt) ? trim($prompt) : '';

                                // Preferiamo il titolo (keyword) come segnale principale.
                                $text = $title !== '' ? $title : $prompt;
                                if ($text === '') {
                                    return [
                                        'language' => 'it',
                                        'detected_language' => 'it',
                                        'detected_language_name' => 'Italian',
                                        'detected_language_confidence' => 0.0,
                                    ];
                                }

                                try {
                                    $detected = $detector->detect($text);
                                } catch (\Throwable $e) {
                                    // fallback silenzioso: non bloccare il modal
                                    $detected = [
                                        'language_code' => 'it',
                                        'language_name' => 'Italian',
                                        'confidence' => 0.0,
                                    ];
                                }

                                $code = (string) ($detected['language_code'] ?? 'it');

                                return [
                                    'language' => $code,
                                    'detected_language' => $code,
                                    'detected_language_name' => (string) ($detected['language_name'] ?? ''),
                                    'detected_language_confidence' => (float) ($detected['confidence'] ?? 0.0),
                                ];
                            })
                            ->action(function (array $data, Set $set, callable $get) {
                                $title = $get('title');
                                $prompt = $get('content');

                                if (! $title || trim($title) === '') {
                                    throw new \Exception('The title field must be filled before generating content.');
                                }

                                if (! $prompt || trim($prompt) === '') {
                                    throw new \Exception('The content field must be filled before generating content.');
                                }

                                $language = $data['language'] ?? 'it';
                                $language = is_string($language) ? trim($language) : 'it';
                                if ($language === '') {
                                    $language = 'it';
                                }

                                $generatedContent = static::generateContent($title, $prompt, $language);
                                $set('content', $generatedContent);
                            })
                    ),

                Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta title')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta description')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('meta_keywords')
                            ->label('Meta keywords (comma-separated)')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('og_title')
                            ->label('OG title')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('og_description')
                            ->label('OG description')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('twitter_title')
                            ->label('Twitter title')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('twitter_description')
                            ->label('Twitter description')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                CuratorPicker::make('featured_image_id')
                    ->relationship('featuredImage', 'id')
                    ->imageResizeTargetWidth('10'),
            ]);
    }

    /**
     * Call OpenAI API to generate article content.
     *
     * @param string $title
     * @return string
     * @throws \Exception
     */
    protected static function generateContent(string $title, string $prompt, string $language): string
    {
        $apiKeyConfig = config('services.openai.key');
        $apiKey = is_string($apiKeyConfig) ? trim($apiKeyConfig) : '';

        if ($apiKey === '') {
            throw new \Exception('OpenAI API key is missing.');
        }

        $client = new \GuzzleHttp\Client();

        try {
            $language = strtolower(trim($language));
            if (! preg_match('/^[a-z]{2}$/', $language)) {
                $language = 'it';
            }

            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Sei uno scrittore professionista di blog.\nSCRIVI SEMPRE E SOLO nella lingua corrispondente a questo codice ISO-639-1: {$language}.\nNon dire mai che sei un'IA.",
                        ],
                        [
                            'role' => 'user',
                            'content' => "Titolo/Keyword: {$title}\n\nScrivi un articolo dettagliato con testo NON FORMATTATO ECCETTO GLI \"A CAPI\".",
                        ],
                        [
                            'role' => 'user',
                            'content' => "Indicazioni aggiuntive: {$prompt}",
                        ],
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.75,
                ],
            ]);

            $decoded = json_decode((string) $response->getBody(), true);
            if (! is_array($decoded)) {
                return 'No content generated.';
            }

            $choices = $decoded['choices'] ?? null;
            if (! is_array($choices) || ! isset($choices[0]) || ! is_array($choices[0])) {
                return 'No content generated.';
            }

            $message = $choices[0]['message'] ?? null;
            if (! is_array($message)) {
                return 'No content generated.';
            }

            $content = $message['content'] ?? null;
            if (! is_string($content) || trim($content) === '') {
                return 'No content generated.';
            }

            return $content;
        } catch (\Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    /**
     * @return array<string, string>
     */
    public static function languageOptions(): array
    {
        return [
            'it' => 'Italiano (it)',
            'en' => 'English (en)',
            'fr' => 'Français (fr)',
            'es' => 'Español (es)',
            'de' => 'Deutsch (de)',
            'pt' => 'Português (pt)',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable() // Aggiunge un pulsante per copiare il valore
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(false), // La colonna non è disattivabile

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc') // Ordine discendente per ID
            ->filters([
                SelectFilter::make('tags')
                ->relationship('tags', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
