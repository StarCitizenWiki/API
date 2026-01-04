<?php

namespace App\Console\Commands\Game;

use App\Models\Game\VehicleData;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;

class ReviewVehicleMatches extends Command
{
    protected $signature = 'game:review-vehicle-matches
                            {--limit=20 : Number of unmatched vehicles to review}';

    protected $description = 'Interactively review and match unmatched game vehicles';

    public function handle(): int
    {
        $unmatched = VehicleData::query()
            ->whereNull('shipmatrix_id')
            ->with('manufacturer')
            ->limit($this->option('limit'))
            ->get();

        if ($unmatched->isEmpty()) {
            $this->info('No unmatched vehicles found!');

            return self::SUCCESS;
        }

        $this->info("Found {$unmatched->count()} unmatched vehicles");
        $newOverrides = [];

        foreach ($unmatched as $vehicle) {
            $this->newLine();
            $this->line("Game Vehicle: <fg=cyan>{$vehicle->name}</>");
            $this->line("Class Name: <fg=gray>{$vehicle->class_name}</>");
            $this->line("Manufacturer: <fg=gray>{$vehicle->manufacturer?->name}</>");

            $suggestions = $this->findPotentialMatches($vehicle->name);

            if ($suggestions->isEmpty()) {
                $this->warn('No potential matches found');

                continue;
            }

            $selectedId = search(
                label: 'Select matching ShipMatrix vehicle (or press Ctrl+C to skip):',
                options: fn (string $value) => strlen($value) > 0
                    ? ShipMatrixVehicle::query()
                        ->where('name', 'LIKE', "%{$value}%")
                        ->limit(10)
                        ->pluck('name', 'id')
                        ->all()
                    : $suggestions->pluck('name', 'id')->all(),
                placeholder: 'Type to search...',
            );

            if ($selectedId === null) {
                continue;
            }

            $selected = ShipMatrixVehicle::find($selectedId);

            if (confirm("Confirm: '{$vehicle->name}' → '{$selected->name}'?")) {
                $vehicle->update(['shipmatrix_id' => $selected->id]);

                $this->info('✓ Matched!');

                if (confirm('Add this to config overrides?', default: false)) {
                    $newOverrides[$vehicle->name] = $selected->name;
                }
            }
        }

        // suggested overrides
        if (! empty($newOverrides)) {
            $this->newLine();
            $this->line('<fg=green>Add these to config/game.php:</>');
            $this->line("'vehicle_name_overrides' => [");
            foreach ($newOverrides as $from => $to) {
                $this->line("    '{$from}' => '{$to}',");
            }
            $this->line('],');
        }

        return self::SUCCESS;
    }

    private function findPotentialMatches(string $name): Collection
    {
        return ShipMatrixVehicle::query()
            ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($name).'%'])
            ->orWhereRaw('LOWER(slug) LIKE ?', ['%'.mb_strtolower(Str::slug($name)).'%'])
            ->limit(10)
            ->get();
    }
}
