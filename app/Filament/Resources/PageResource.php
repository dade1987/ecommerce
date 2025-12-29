<?php

namespace App\Filament\Resources;

use App\Models\Article;
use App\Services\Seo\LlmsTxtGenerator;
use Closure;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Forms\Components\PageBuilder;
use Z3d0X\FilamentFabricator\Models\Contracts\Page as PageContract;
use Z3d0X\FilamentFabricator\Models\Page as PageModel;
use Z3d0X\FilamentFabricator\Resources\PageResource as ResourcesPageResource;
use Z3d0X\FilamentFabricator\Resources\PageResource\Pages;

class PageResource extends ResourcesPageResource
{
    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Group::make()
                    ->schema([
                        Group::make()->schema(FilamentFabricator::getSchemaSlot('blocks.before')),

                        PageBuilder::make('blocks')
                            ->label(__('filament-fabricator::page-resource.labels.blocks')),

                        Group::make()->schema(FilamentFabricator::getSchemaSlot('blocks.after')),
                    ])
                    ->columnSpan(2),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Group::make()->schema(FilamentFabricator::getSchemaSlot('sidebar.before')),

                        Section::make()
                            ->schema([
                                Placeholder::make('page_url')
                                    ->visible(fn (?PageContract $record) => config('filament-fabricator.routing.enabled') && filled($record))
                                    ->content(fn (?PageContract $record) => FilamentFabricator::getPageUrlFromId($record?->id)),

                                TextInput::make('title')
                                    ->label(__('filament-fabricator::page-resource.labels.title'))
                                    /*
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?PageContract $record) {
                                        if (!$get('is_slug_changed_manually') && filled($state) && blank($record)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->debounce('500ms')
                                    */
                                    ->required(),

                                Hidden::make('is_slug_changed_manually')
                                    ->default(false)
                                    ->dehydrated(false),

                                TextInput::make('slug')
                                    ->label(__('filament-fabricator::page-resource.labels.slug'))
                                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('parent_id', $get('parent_id')))
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('is_slug_changed_manually', true);
                                    })
                                    ->rule(function ($state) {
                                        return function (string $attribute, $value, Closure $fail) use ($state) {
                                            if ($state !== '/' && (Str::startsWith($value, '/') || Str::endsWith($value, '/'))) {
                                                $fail(__('filament-fabricator::page-resource.errors.slug_starts_or_ends_with_slash'));
                                            }
                                        };
                                    })
                                    ->required(),

                                TextInput::make('description')
                                    ->label('Description')
                                    ->maxLength(255),

                                Select::make('layout')
                                    ->label(__('filament-fabricator::page-resource.labels.layout'))
                                    ->options(FilamentFabricator::getLayouts())
                                    ->default('default')
                                    ->required(),

                                Select::make('parent_id')
                                    ->label(__('filament-fabricator::page-resource.labels.parent'))
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->suffixAction(
                                        fn ($get, $context) => FormAction::make($context.'-parent')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn () => PageResource::getUrl($context, ['record' => $get('parent_id')]))
                                            ->openUrlInNewTab()
                                            ->visible(fn () => filled($get('parent_id')))
                                    )
                                    ->relationship(
                                        'parent',
                                        'title',
                                        function (Builder $query, ?PageContract $record) {
                                            if (filled($record)) {
                                                $query->where('id', '!=', $record->id);
                                            }
                                        }
                                    ),
                            ]),

                        Group::make()->schema(FilamentFabricator::getSchemaSlot('sidebar.after')),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        // Ottiene la configurazione della tabella dal genitore
        $parentTable = parent::table($table);

        // Aggiunge la nuova azione "Clona" all'array delle azioni esistenti
        return $parentTable->actions([
            Action::make('clone')
                ->label('Clona')
                ->icon('heroicon-o-square-2-stack')
                ->action(function (Model $record) {
                    $newRecord = $record->replicate();
                    $newRecord->title = $record->title.' - Copia';
                    $newRecord->slug = Str::slug($newRecord->title);
                    $newRecord->created_at = now();
                    $newRecord->updated_at = now();
                    $newRecord->save();
                }),
            ...$parentTable->getActions(), // Mantiene le azioni originali (View, Edit, Delete)
        ])
        ->bulkActions([
            BulkAction::make('generate_llms_txt')
                ->label('Genera llms.txt')
                ->icon('heroicon-o-document-text')
                ->modalHeading('Genera llms.txt')
                ->modalSubheading('Seleziona le pagine (bulk selection) e, opzionalmente, includi anche articoli del blog. Il file verrà salvato in public_html/llms.txt.')
                ->form([
                    TextInput::make('base_url')
                        ->label('Base URL')
                        ->helperText('Es: https://cavalliniservice.com (serve per generare link assoluti).')
                        ->default(fn () => (string) config('app.url'))
                        ->required(),
                    Select::make('article_ids')
                        ->label('Articoli da includere (opzionali)')
                        ->multiple()
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => Article::query()
                            ->where('title', 'like', "%{$search}%")
                            ->orderByDesc('id')
                            ->limit(50)
                            ->pluck('title', 'id')
                            ->toArray())
                        ->getOptionLabelsUsing(fn (array $values): array => Article::query()
                            ->whereIn('id', $values)
                            ->orderByDesc('id')
                            ->pluck('title', 'id')
                            ->toArray()),
                ])
                ->action(function (array $data, \Illuminate\Support\Collection $records) {
                    $baseUrl = (string) ($data['base_url'] ?? config('app.url'));
                    $baseUrl = trim($baseUrl);

                    $articleIds = $data['article_ids'] ?? [];
                    $articleIds = is_array($articleIds) ? $articleIds : [];

                    $articles = Article::query()
                        ->whereIn('id', $articleIds)
                        ->orderByDesc('id')
                        ->get();

                    $targetPath = base_path('public_html/llms.txt');

                    app(LlmsTxtGenerator::class)->writeToPublic(
                        pages: $records,
                        articles: $articles,
                        baseUrl: $baseUrl,
                        targetPath: $targetPath,
                    );

                    Notification::make()
                        ->title('llms.txt generato')
                        ->body("Salvato in: {$targetPath}")
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation(),
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }
}
