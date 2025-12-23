<?php

namespace Tests\Unit\Fabricator;

use App\Filament\Fabricator\PageBlocks\BlogShowUx;
use Filament\Forms\Components\Builder\Block;
use PHPUnit\Framework\TestCase;

class BlogShowUxBlockTest extends TestCase
{
    public function test_block_schema_is_a_block_instance(): void
    {
        $schema = BlogShowUx::getBlockSchema();
        $this->assertInstanceOf(Block::class, $schema);
    }
}
