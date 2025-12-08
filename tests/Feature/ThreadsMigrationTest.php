<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ThreadsMigrationTest extends TestCase
{
    public function test_threads_table_has_expected_columns(): void
    {
        // Arrange & Act
        $hasTable = Schema::hasTable('threads');

        // Assert stato
        $this->assertTrue($hasTable, 'La tabella threads deve esistere dopo le migration.');

        // Assert contenuto/logica: colonne chiave
        $this->assertTrue(Schema::hasColumn('threads', 'thread_id'));
        $this->assertTrue(Schema::hasColumn('threads', 'ip_address'));
        $this->assertTrue(Schema::hasColumn('threads', 'team_slug'));
        $this->assertTrue(Schema::hasColumn('threads', 'created_at'));
        $this->assertTrue(Schema::hasColumn('threads', 'updated_at'));
    }
}
