<?php

namespace Tests\Unit\Fabricator;

use App\Filament\Fabricator\PageBlocks\BlogIndexUx;
use Filament\Forms\Components\Builder\Block;
use PHPUnit\Framework\TestCase;

class BlogIndexUxBlockTest extends TestCase
{
    public function test_block_schema_is_a_block_instance(): void
    {
        $schema = BlogIndexUx::getBlockSchema();
        $this->assertInstanceOf(Block::class, $schema);
    }
}
