<?php

namespace Tests\Unit\Fabricator;

use App\Filament\Fabricator\PageBlocks\MiniMenu;
use Filament\Forms\Components\Builder\Block;
use PHPUnit\Framework\TestCase;

class MiniMenuBlockTest extends TestCase
{
    public function test_block_schema_is_a_block_instance(): void
    {
        $schema = MiniMenu::getBlockSchema();
        $this->assertInstanceOf(Block::class, $schema);
    }
}
