<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;

class AddGameVersion extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:add-version
        {code : Game version in the format Major.Minor.Patch.SCOPE.Buildnumber (scope must be LIVE, PTU, or EPTU)}
        {--released-at= : Release date/time (e.g. 2025-12-06 or 2025-12-06 15:30)}
        {--default : Set this version as the default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new game version';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawCode = (string) $this->argument('code');

        $parsed = $this->parseVersion($rawCode);

        if ($parsed === null) {
            $this->error('Invalid game version format. Expected Major.Minor.Patch.SCOPE.Buildnumber with scope LIVE, PTU, or EPTU (e.g. 4.4.0-LIVE.10753606).');

            return self::FAILURE;
        }

        if ($this->versionExists($parsed['code'])) {
            $this->error(sprintf('Game version "%s" already exists.', $parsed['code']));

            return self::FAILURE;
        }

        $releasedAt = $this->parseReleaseDate((string) $this->option('released-at'));

        if ($releasedAt === false) {
            $this->error('Invalid released-at value. Use a parseable date/time like "2025-12-06" or "2025-12-06 15:30".');

            return self::FAILURE;
        }

        $setDefault = (bool) $this->option('default');

        DB::transaction(function () use ($parsed, $releasedAt, $setDefault): void {
            if ($setDefault) {
                GameVersion::query()
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            GameVersion::query()->create([
                'code' => $parsed['code'],
                'channel' => $parsed['scope'],
                'released_at' => $releasedAt,
                'is_default' => $setDefault,
            ]);
        });

        $this->info(sprintf('Game version "%s" created.', $parsed['code']));

        return self::SUCCESS;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, callable>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'code' => function (): string {
                return text(
                    label: 'Enter game version (Major.Minor.Patch.SCOPE.Buildnumber)',
                    placeholder: '4.4.0-LIVE.10753606',
                    validate: function (string $value): ?string {
                        return $this->parseVersion($value) !== null
                            ? null
                            : 'Format must be Major.Minor.Patch-SCOPE.Buildnumber with scope LIVE, PTU, or EPTU (e.g. 4.4.0-LIVE.10753606).';
                    }
                );
            },
        ];
    }

    /**
     * @return array{code: string, scope: string}|null
     */
    private function parseVersion(string $code): ?array
    {
        $pattern = '/^(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)-(?<scope>[a-z]+)\.(?<build>\d+)$/i';

        if (! preg_match($pattern, trim($code), $matches)) {
            return null;
        }

        if (! $this->isAllowedScope($matches['scope'])) {
            return null;
        }

        return [
            'code' => sprintf(
                '%s.%s.%s-%s.%s',
                $matches['major'],
                $matches['minor'],
                $matches['patch'],
                Str::upper($matches['scope']),
                $matches['build']
            ),
            'scope' => Str::lower($matches['scope']),
        ];
    }

    private function versionExists(string $code): bool
    {
        return GameVersion::query()
            ->whereRaw('LOWER(code) = ?', [Str::lower($code)])
            ->exists();
    }

    private function isAllowedScope(string $scope): bool
    {
        $allowed = ['LIVE', 'PTU', 'EPTU'];

        return in_array(Str::upper($scope), $allowed, true);
    }

    private function parseReleaseDate(string $releasedAt): Carbon|false|null
    {
        if ($releasedAt === '' || $releasedAt === null) {
            return null;
        }

        try {
            return Carbon::parse($releasedAt);
        } catch (\Throwable) {
            return false;
        }
    }
}
