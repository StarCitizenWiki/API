<?php

namespace App\Console\Commands\SC;

use App\Models\SC\Mission\Giver;
use App\Models\SC\Mission\Mission;
use App\Models\SC\Mission\Type;
use App\Models\SC\Reputation\Reward;
use App\Models\SC\Reputation\Scope;
use App\Models\SC\Reputation\Standing;
use App\Models\System\Language;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportMissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:import-missions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->importRewards();
        $this->importStandings();
        $this->importScopes();
        $this->importTypes();
        $this->importGiver();
        $this->importMissions();
        $this->syncMissions();
    }

    private function importRewards(): void
    {
        $this->withProgressBar(File::allFiles(scdata('reputation/rewards')), function (string $file) {
            $data = File::json($file);

            if (isset($data['missionGiverBonuses'])) {
                // TODO: Import Bonuses
                return;
            }

            Reward::query()->updateOrCreate([
                'uuid' => $data['__ref'],
            ], [
                'editor_name' => $data['editorName'],
                'reputation_amount' => $data['reputationAmount'],
                'class_name' => $data['ClassName'],
            ]);
        });
    }

    private function importStandings(): void
    {
        $this->withProgressBar(File::allFiles(scdata('reputation/standings')), function (string $file) {
            $data = File::json($file);

            if (str_starts_with($data['displayName'], '@')) {
                $data['displayName'] = null;
            }

            if (str_starts_with($data['perkDescription'], '@')) {
                $data['perkDescription'] = null;
            }

            if ($data['description'] === 'desc' || str_starts_with($data['description'], '@')) {
                $data['description'] = null;
            }

            Standing::query()->updateOrCreate([
                'uuid' => $data['__ref'],
            ], [
                'name' => $data['name'],
                'description' => $data['description'],
                'display_name' => $data['displayName'],
                'perk_description' => $data['perkDescription'],
                'min_reputation' => $data['minReputation'],
                'drift_reputation' => $data['driftReputation'],
                'drift_time_hours' => $data['driftTimeHours'],
                'gated' => $data['gated'],
            ]);
        });
    }

    private function importScopes(): void
    {
        $this->withProgressBar(File::allFiles(scdata('reputation/scopes')), function (string $file) {
            $data = File::json($file);

            if ($data['description'] === 'desc' || str_starts_with($data['description'], '@')) {
                $data['description'] = null;
            }

            /** @var Scope $scope */
            $scope = Scope::query()->updateOrCreate([
                'uuid' => $data['__ref'],
            ], [
                'scope_name' => $data['scopeName'],
                'display_name' => $data['displayName'],
                'description' => $data['description'],
                'class_name' => $data['ClassName'],
                'initial_reputation' => $data['standingMap']['reputationCeiling'],
                'reputation_ceiling' => $data['standingMap']['initialReputation'],
            ]);

            $ids = collect($data['standingMap']['standings'])->map(function (array $standing) {
                return Standing::query()->where('uuid', $standing['value'])->first()?->id;
            })->filter(fn ($id) => $id !== null);

            $scope->standings()->sync($ids);
        });
    }

    private function importTypes(): void
    {
        $this->withProgressBar(File::allFiles(scdata('missions/types')), function (string $file) {
            $data = File::json($file);

            Type::query()->updateOrCreate([
                'uuid' => $data['__ref'],
            ], [
                'name' => $data['LocalisedTypeName'],
            ]);
        });
    }

    private function importGiver(): void
    {
        $this->withProgressBar(File::allFiles(scdata('missions/missiongiver')), function (string $file) {
            $data = File::json($file);

            $texts = [
                'displayName',
                'description',
                'headquarters',
            ];

            foreach ($texts as $text) {
                if (str_starts_with($data[$text], '@')) {
                    $data[$text] = null;
                }
            }

            /** @var Giver $giver */
            $giver = Giver::query()->updateOrCreate([
                'uuid' => $data['__ref'],
            ], [
                'name' => $data['displayName'],
                'headquarters' => $data['headquarters'],
                'invitation_timeout' => $data['invitationTimeout'],
                'visit_timeout' => $data['visitTimeout'],
                'short_cooldown' => $data['shortCooldown'],
                'medium_cooldown' => $data['mediumCooldown'],
                'long_cooldown' => $data['longCooldown'],
            ]);

            if (! empty($data['description'])) {
                $giver->translations()->updateOrCreate([
                    'locale_code' => Language::ENGLISH,
                ], [
                    'translation' => $data['description'],
                ]);
            }
        });
    }

    private function importMissions(): void
    {
        $this->withProgressBar(File::allFiles(scdata('missions')), function (string $file) {
            $data = File::json($file);

            if (empty($data['title'])) {
                return;
            }

            /** @var Mission $mission */
            $mission = Mission::query()->updateOrCreate([
                'uuid' => $data['__ref'],
            ], [
                'not_for_release' => $data['notForRelease'],
                'title' => $data['title'],
                'title_hud' => $data['titleHUD'],
                'mission_giver' => $data['missionGiver'],
                'comms_channel_name' => $data['commsChannelName'],
                'locality_available' => $data['localityAvailable'],
                'location_mission_available' => $data['locationMissionAvailable'],
                'initially_active' => $data['initiallyActive'],
                'notify_on_available' => $data['notifyOnAvailable'],
                'show_as_offer' => $data['showAsOffer'],
                'mission_buy_in_amount' => $data['missionBuyInAmount'],
                'refund_buy_in_on_withdraw' => $data['refundBuyInOnWithdraw'],
                'has_complete_button' => $data['hasCompleteButton'],
                'handles_abandon_request' => $data['handlesAbandonRequest'],
                'mission_module_per_player' => $data['missionModulePerPlayer'],
                'max_instances' => $data['maxInstances'],
                'max_players_per_instance' => $data['maxPlayersPerInstance'],
                'max_instances_per_player' => $data['maxInstancesPerPlayer'],
                'can_be_shared' => $data['canBeShared'],
                'once_only' => $data['onceOnly'],
                'tutorial' => $data['tutorial'],
                'display_allied_markers' => $data['displayAlliedMarkers'],
                'available_in_prison' => $data['availableInPrison'],
                'fail_if_sent_to_prison' => $data['failIfSentToPrison'],
                'fail_if_became_criminal' => $data['failIfBecameCriminal'],
                'fail_if_leave_prison' => $data['failIfLeavePrison'],
                'request_only' => $data['requestOnly'],
                'respawn_time' => $data['respawnTime'],
                'respawn_time_variation' => $data['respawnTimeVariation'],
                'instance_has_life_time' => $data['instanceHasLifeTime'],
                'show_life_time_in_mobi_glas' => $data['showLifeTimeInMobiGlas'],
                'instance_life_time' => $data['instanceLifeTime'],
                'instance_life_time_variation' => $data['instanceLifeTimeVariation'],
                'can_reaccept_after_abandoning' => $data['canReacceptAfterAbandoning'],
                'abandoned_cooldown_time' => $data['abandonedCooldownTime'],
                'abandoned_cooldown_time_variation' => $data['abandonedCooldownTimeVariation'],
                'can_reaccept_after_failing' => $data['canReacceptAfterFailing'],
                'has_personal_cooldown' => $data['hasPersonalCooldown'],
                'personal_cooldown_time' => $data['personalCooldownTime'],
                'personal_cooldown_time_variation' => $data['personalCooldownTimeVariation'],
                'module_handles_own_shutdown' => $data['moduleHandlesOwnShutdown'],
                'linked_mission' => $data['linkedMission'],
                'lawful_mission' => $data['lawfulMission'],
                'invitation_mission' => $data['invitationMission'],
                'version' => config('api.sc_data_version'),
                'type_id' => Type::query()->where('uuid', $data['type'])->first()?->id,
                'giver_id' => Giver::query()->where('uuid', $data['missionGiverRecord'])->first()?->id,
            ]);

            if (! str_starts_with($data['description'], '@')) {
                $mission->translations()->updateOrCreate([
                    'locale_code' => Language::ENGLISH,
                ], [
                    'translation' => $data['description'],
                ]);
            }

            if (! empty($data['missionReward'])) {
                $mission->reward()->updateOrCreate([
                    'mission_id' => $mission->id,
                ], [
                    'amount' => $data['missionReward']['reward'],
                    'max' => $data['missionReward']['max'],
                    'plus_bonuses' => $data['missionReward']['plusBonusses'],
                    'currency' => $data['missionReward']['currencyType'],
                    'reputation_bonus' => $data['missionReward']['reputationBonus'],
                ]);
            }

            if (! empty($data['missionDeadline'])) {
                $mission->deadline()->updateOrCreate([
                    'mission_id' => $mission->id,
                ], [
                    'mission_completion_time' => $data['missionDeadline']['missionCompletionTime'],
                    'mission_auto_end' => $data['missionDeadline']['missionAutoEnd'],
                    'mission_result_after_timer_end' => $data['missionDeadline']['missionResultAfterTimerEnd'],
                    'mission_end_reason' => $data['missionDeadline']['missionEndReason'],
                ]);
            }
        });
    }

    private function syncMissions(): void
    {
        $this->withProgressBar(File::allFiles(scdata('missions')), function (string $file) {
            $data = File::json($file);

            /** @var Mission $mission */
            $mission = Mission::query()->where('uuid', $data['__ref'])->first();

            if ($mission === null) {
                return;
            }

            $ids = collect($data['requiredMissions'])->map(function (array $standing) {
                return Mission::query()->where('uuid', $standing['value'])->first()?->id;
            })->filter(fn ($id) => $id !== null);

            $mission->requiredMissions()->sync($ids);

            $ids = collect($data['associatedMissions'])->map(function (array $standing) {
                return Mission::query()->where('uuid', $standing['value'])->first()?->id;
            })->filter(fn ($id) => $id !== null);

            $mission->associatedMissions()->sync($ids);
        });
    }
}
