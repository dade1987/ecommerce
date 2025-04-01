<?php

namespace App\Filament\Resources;

use App\Models\Tag;
use Filament\Forms;
use Filament\Tables;
use App\Models\Article;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use function Safe\json_decode;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\ArticleResource\Pages;
use Awcodes\Curator\Components\Forms\CuratorPicker;

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
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true) // Vérifie l'unicité sauf lors de la modification
                    ->dehydrated() // Assure que la valeur est bien envoyée au modèle
                    ->disabled(fn ($livewire) => $livewire instanceof Pages\EditArticle) // Édition désactivée sauf lors de la modification
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->reactive(),

                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->hintAction(
                        Action::make('generateWithGpt')
                            ->icon('heroicon-m-sparkles')
                            ->label('Generate with AI')
                            ->requiresConfirmation()
                            ->action(function (Set $set, callable $get) {
                                $title = $get('title');
                                $prompt = $get('content');

                                if (! $title || trim($title) === '') {
                                    throw new \Exception('The title field must be filled before generating content.');
                                }

                                if (! $prompt || trim($prompt) === '') {
                                    throw new \Exception('The content field must be filled before generating content.');
                                }

                                $generatedContent = static::generateContent($title, $prompt);
                                $set('content', $generatedContent);
                            })
                    ),

                CuratorPicker::make('featured_image_id')
                    ->relationship('featuredImage', 'id')
                    ->imageResizeTargetWidth(10),
            ]);
    }

    /**
     * Call OpenAI API to generate article content.
     *
     * @param string $title
     * @return string
     * @throws \Exception
     */
    protected static function generateContent(string $title, string $prompt): string
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            throw new \Exception('OpenAI API key is missing.');
        }

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Sei uno scrittore professionista di blog.'],
                        ['role' => 'user', 'content' => "Scrivi un articolo dettagliato con testo NON FORMATTATO ECCETTO GLI \"A CAPI\", sul seguente argomento: {$title}"],
                        ['role' => 'user', 'content' => "In più ti fornisco le seguenti indicazioni: {$prompt}"],
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.75,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['choices'][0]['message']['content'] ?? 'No content generated.';
        } catch (\Exception $e) {
            return 'Error: '.$e->getMessage();
        }
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
                ->relationship('tags', 'name')
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

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
}
