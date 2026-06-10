<?php

use App\Models\Game\StarmapLocationData;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_starmap_location_data', function ($table) {
            $table->uuid('location_uuid')->nullable();
            $table->string('location_slug')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('star_system_name')->nullable();
            $table->string('type_classification')->nullable();
            $table->string('jurisdiction_name')->nullable();
            $table->string('affiliation_name')->nullable();
            $table->string('respawn_location_type')->nullable();
            $table->boolean('hide_in_starmap')->default(false);
            $table->boolean('hide_in_world')->default(false);
            $table->boolean('hide_minor_locations')->default(false);
            $table->string('parent_type_name')->nullable();
            $table->uuid('parent_location_uuid')->nullable();
            $table->string('parent_location_slug')->nullable();
            $table->string('star_name')->nullable();
            $table->string('star_type_name')->nullable();
            $table->uuid('star_location_uuid')->nullable();
            $table->string('star_location_slug')->nullable();
            $table->boolean('has_resources')->default(false);
            $table->integer('child_count')->default(0);
            $table->string('tag_name')->nullable();
            $table->uuid('tag_uuid')->nullable();
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->backfillPostgres();
        } else {
            $this->backfillSqlite();
        }
    }

    private function backfillPostgres(): void
    {
        // Identity from starmap_locations
        DB::statement('
            UPDATE game_starmap_location_data gsld
            SET location_uuid = loc.uuid,
                location_slug = loc.slug
            FROM game_starmap_locations loc
            WHERE gsld.starmap_location_id = loc.id
        ');

        // Parent/star detail (rows with both parent and star)
        DB::statement('
            UPDATE game_starmap_location_data gsld
            SET parent_name = parent.name,
                star_system_name = star.name,
                parent_type_name = parent.type_name,
                parent_location_uuid = ploc.uuid,
                parent_location_slug = ploc.slug,
                star_name = star.name,
                star_type_name = star.type_name,
                star_location_uuid = sloc.uuid,
                star_location_slug = sloc.slug
            FROM game_starmap_location_data parent
                LEFT JOIN game_starmap_locations ploc ON parent.starmap_location_id = ploc.id,
                game_starmap_location_data star
                LEFT JOIN game_starmap_locations sloc ON star.starmap_location_id = sloc.id
            WHERE gsld.parent_data_id = parent.id
              AND gsld.star_data_id = star.id
        ');

        // Rows with NULL parent but has star
        DB::statement('
            UPDATE game_starmap_location_data gsld
            SET star_name = star.name,
                star_type_name = star.type_name,
                star_location_uuid = sloc.uuid,
                star_location_slug = sloc.slug
            FROM game_starmap_location_data star
                LEFT JOIN game_starmap_locations sloc ON star.starmap_location_id = sloc.id
            WHERE gsld.star_data_id = star.id
              AND gsld.parent_data_id IS NULL
        ');

        // JSON extracts
        DB::statement("
            UPDATE game_starmap_location_data SET
                type_classification = data #>> '{Type,Classification}',
                jurisdiction_name = data #>> '{Jurisdiction,Name}',
                affiliation_name = data #>> '{Affiliation,DisplayName}',
                respawn_location_type = data #>> '{RespawnLocationType}',
                hide_in_starmap = COALESCE((data #>> '{HideInStarmap}')::boolean, false),
                hide_in_world = COALESCE((data #>> '{HideInWorld}')::boolean, false),
                hide_minor_locations = COALESCE((data #>> '{OnlyShowWhenParentSelected}')::boolean, false)
        ");

        // Tag from entity_tags
        DB::statement('
            UPDATE game_starmap_location_data gsld
            SET tag_uuid = et.uuid,
                tag_name = et.name
            FROM game_entity_tags et
            WHERE gsld.location_hierarchy_entity_tag_id = et.id
        ');

        // Child count
        DB::statement('
            UPDATE game_starmap_location_data gsld
            SET child_count = COALESCE(aggregated.cnt, 0)
            FROM (
                SELECT parent_data_id, COUNT(*) as cnt
                FROM game_starmap_location_data
                WHERE parent_data_id IS NOT NULL
                GROUP BY parent_data_id
            ) aggregated
            WHERE gsld.id = aggregated.parent_data_id
        ');

        // Has resources
        DB::statement('
            UPDATE game_starmap_location_data gsld
            SET has_resources = EXISTS(
                SELECT 1 FROM game_resource_location_placements grlp
                WHERE grlp.starmap_location_data_id = gsld.id
            )
        ');
    }

    private function backfillSqlite(): void
    {
        StarmapLocationData::query()->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $data = $row->data ?? [];
                $updates = [];

                // Identity
                if ($row->starmap_location_id) {
                    $loc = DB::table('game_starmap_locations')->where('id', $row->starmap_location_id)->first();
                    if ($loc) {
                        $updates['location_uuid'] = $loc->uuid;
                        $updates['location_slug'] = $loc->slug;
                    }
                }

                // Parent
                if ($row->parent_data_id) {
                    $parent = StarmapLocationData::find($row->parent_data_id);
                    if ($parent) {
                        $updates['parent_name'] = $parent->name;
                        $updates['parent_type_name'] = $parent->type_name;
                        $ploc = DB::table('game_starmap_locations')->where('id', $parent->starmap_location_id)->first();
                        $updates['parent_location_uuid'] = $ploc?->uuid;
                        $updates['parent_location_slug'] = $ploc?->slug;
                    }
                }

                // Star
                if ($row->star_data_id) {
                    $star = StarmapLocationData::find($row->star_data_id);
                    if ($star) {
                        $updates['star_name'] = $star->name;
                        $updates['star_type_name'] = $star->type_name;
                        $updates['star_system_name'] = $star->name;
                        $sloc = DB::table('game_starmap_locations')->where('id', $star->starmap_location_id)->first();
                        $updates['star_location_uuid'] = $sloc?->uuid;
                        $updates['star_location_slug'] = $sloc?->slug;
                    }
                }

                // JSON extracts
                $updates['type_classification'] = trim((string) ($data['Type']['Classification'] ?? '')) ?: null;
                $updates['jurisdiction_name'] = trim((string) ($data['Jurisdiction']['Name'] ?? '')) ?: null;
                $updates['affiliation_name'] = trim((string) ($data['Affiliation']['DisplayName'] ?? '')) ?: null;
                $updates['respawn_location_type'] = trim((string) ($data['RespawnLocationType'] ?? '')) ?: null;
                $updates['hide_in_starmap'] = (bool) ($data['HideInStarmap'] ?? false);
                $updates['hide_in_world'] = (bool) ($data['HideInWorld'] ?? false);
                $updates['hide_minor_locations'] = (bool) ($data['OnlyShowWhenParentSelected'] ?? false);

                // Tag
                if ($row->location_hierarchy_entity_tag_id) {
                    $tag = DB::table('game_entity_tags')->where('id', $row->location_hierarchy_entity_tag_id)->first();
                    if ($tag) {
                        $updates['tag_uuid'] = $tag->uuid;
                        $updates['tag_name'] = $tag->name;
                    }
                }

                // Child count
                $updates['child_count'] = StarmapLocationData::where('parent_data_id', $row->id)->count();

                // Has resources
                $updates['has_resources'] = DB::table('game_resource_location_placements')
                    ->where('starmap_location_data_id', $row->id)->exists();

                DB::table('game_starmap_location_data')->where('id', $row->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        Schema::table('game_starmap_location_data', function ($table) {
            $table->dropColumn([
                'location_uuid', 'location_slug', 'parent_name', 'star_system_name',
                'type_classification', 'jurisdiction_name', 'affiliation_name',
                'respawn_location_type', 'hide_in_starmap', 'hide_in_world',
                'hide_minor_locations', 'parent_type_name', 'parent_location_uuid',
                'parent_location_slug', 'star_name', 'star_type_name',
                'star_location_uuid', 'star_location_slug', 'has_resources',
                'child_count', 'tag_name', 'tag_uuid',
            ]);
        });
    }
};
