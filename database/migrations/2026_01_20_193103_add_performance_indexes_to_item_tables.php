<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createIndexIfNotExists('game_item_data', 'name');
        $this->createIndexIfNotExists('game_item_data', 'class_name');
        $this->createIndexIfNotExists('game_item_data', 'classification');
        $this->createIndexIfNotExists('game_item_data', ['type', 'sub_type']);
        $this->createIndexIfNotExists('game_item_data', ['manufacturer_id', 'type']);
        $this->createIndexIfNotExists('game_item_data', ['game_version_id', 'class_name']);
        $this->createIndexIfNotExists('game_item_data', ['base_id', 'game_version_id']);
        $this->createIndexIfNotExists('game_item_data', ['base_id', 'game_version_id', 'name']);

        $this->createIndexIfNotExists('game_manufacturers', 'name');

        $this->createIndexIfNotExists('game_vehicle_data', 'name');
        $this->createIndexIfNotExists('game_vehicle_data', 'display_name');
        $this->createIndexIfNotExists('game_vehicle_data', 'class_name');
        $this->createIndexIfNotExists('game_vehicle_data', ['manufacturer_id', 'size']);
        $this->createIndexIfNotExists('game_vehicle_data', ['career', 'role']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('game_item_data', 'name');
        $this->dropIndexIfExists('game_item_data', 'class_name');
        $this->dropIndexIfExists('game_item_data', 'classification');
        $this->dropIndexIfExists('game_item_data', ['type', 'sub_type']);
        $this->dropIndexIfExists('game_item_data', ['manufacturer_id', 'type']);
        $this->dropIndexIfExists('game_item_data', ['game_version_id', 'class_name']);
        $this->dropIndexIfExists('game_item_data', ['base_id', 'game_version_id']);
        $this->dropIndexIfExists('game_item_data', ['base_id', 'game_version_id', 'name']);

        $this->dropIndexIfExists('game_manufacturers', 'name');

        $this->dropIndexIfExists('game_vehicle_data', 'name');
        $this->dropIndexIfExists('game_vehicle_data', 'display_name');
        $this->dropIndexIfExists('game_vehicle_data', 'class_name');
        $this->dropIndexIfExists('game_vehicle_data', ['manufacturer_id', 'size']);
        $this->dropIndexIfExists('game_vehicle_data', ['career', 'role']);
    }

    private function createIndexIfNotExists(string $table, array|string $columns): void
    {
        $indexName = is_array($columns) ? implode('_', $columns) : $columns;
        $indexName = "{$table}_{$indexName}_index";

        if (! $this->indexExists($indexName)) {
            Schema::table($table, function (Blueprint $t) use ($columns) {
                $t->index($columns);
            });
        }
    }

    private function dropIndexIfExists(string $table, array|string $columns): void
    {
        $indexName = is_array($columns) ? implode('_', $columns) : $columns;
        $indexName = "{$table}_{$indexName}_index";

        if ($this->indexExists($indexName)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }

    private function indexExists(string $indexName): bool
    {
        $connection = DB::connection();

        if ($connection->getDriverName() === 'pgsql') {
            return DB::table('pg_indexes')
                ->where('indexname', $indexName)
                ->where('schemaname', 'public')
                ->exists();
        }

        if ($connection->getDriverName() === 'sqlite') {
            return DB::scalar(
                "SELECT COUNT(*) FROM sqlite_master WHERE type='index' AND name=?",
                [$indexName]
            ) > 0;
        }

        return false;
    }
};
