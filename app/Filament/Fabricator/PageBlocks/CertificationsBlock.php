<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;

class CertificationsBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('certifications')
            ->label('Certificazioni e Link')
            ->schema([
                Repeater::make('certifications')
                    ->label('Certificazioni')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Logo Ente')
                            ->image()
                            ->columnSpan(1),
                        TextInput::make('title')
                            ->label('Titolo Certificazione')
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('issuer')
                            ->label('Ente Rilascio')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('issue_date')
                            ->label('Data Rilascio')
                            ->columnSpan(2),
                        TextInput::make('credential_id')
                            ->label('ID Credenziale')
                            ->columnSpan(2),
                        TextInput::make('credential_url')
                            ->label('URL Credenziale')
                            ->url()
                            ->columnSpan(2),
                    ])->columns(3),

                Repeater::make('important_links')
                    ->label('Link Importanti')
                    ->schema([
                        TextInput::make('link_title')
                            ->label('Titolo Link')
                            ->required(),
                        TextInput::make('link_url')
                            ->label('URL')
                            ->url()
                            ->required(),
                    ])
            ]);
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.certifications-block');
    }
} 