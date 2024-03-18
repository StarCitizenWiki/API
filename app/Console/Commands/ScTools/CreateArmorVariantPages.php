<?php

namespace App\Console\Commands\ScTools;

use App\Models\SC\Char\Clothing\Armor;
use Carbon\Carbon;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CreateArmorVariantPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc-tools:create-armor-variant-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $template = <<<'TEMPLATE'
{{Item
|uuid = <UUID>
|image =
|name = <ITEM NAME>
|manufacturer = <MANUFACTURER CODE>
}}

The '''<ITEM NAME>''' is a <ARMOR CLASS> <ITEM TYPE> manufactured by [[{{subst:MFURN|<MANUFACTURER CODE>}}]].<VARIANTINFO><ref name="ig3221">{{Cite game|build=[[Star Citizen Alpha 3.22.1|Alpha 3.22.1]]|accessdate=<CURDATE>}}</ref>

== Description ==
{{Item description}}

== Item ports ==
{{Item ports}}

== Acquisition ==
{{Item availability}}

== Model ==
=== Variants ===
{{Item variants}}

== References ==
<references />

{{Navplate manufacturers|<MANUFACTURER CODE>}}
TEMPLATE;

    private array $baseQuery = [
        'format' => 'json',
    ];

    private CookieJar $cookies;

    private array $typeMapping = [
        'Char_Armor_Arms' => 'Arms armor',
        'Char_Armor_Torso' => 'Torso armor',
        'Char_Armor_Legs' => 'Legs armor',
        'Char_Armor_Helmet' => 'Helmet',
        'Char_Armor_Backpack' => 'Backpack',
        'Char_Armor_Undersuit' => 'Undersuit',
    ];

    private string $csrfToken;

    public function __construct()
    {
        parent::__construct();

        $this->cookies = new CookieJar();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tokens = $this->getTokens();
        if ($tokens === null) {
            return;
        }

        $status = $this->login($tokens['logintoken']);

        if ($status === null || $status['result'] !== 'Success') {
            $this->error('Could not login.');

            return;
        }

        $tokens = $this->getTokens();
        $this->csrfToken = $tokens['csrftoken'];

        Armor::query()
            ->whereNotNull('base_id')
            ->where('class_name', 'NOT LIKE', '%test%')
            ->chunk(50, function (Collection $armors) {
                $armors->each(function (Armor $armor) {
                    $this->createPage($armor);
                });
            });
    }

    private function getTokens(): ?array
    {
        $loginToken = $this->getBaseClient()
            ->get('api.php', $this->baseQuery + [
                'action' => 'query',
                'meta' => 'tokens',
                'type' => 'csrf|login',
            ]);

        if (! isset($loginToken['query']['tokens']['logintoken'], $loginToken['query']['tokens']['logintoken'])) {
            $this->error('Could not retrieve Login Token');

            return null;
        }

        return [
            'logintoken' => $loginToken['query']['tokens']['logintoken'],
            'csrftoken' => $loginToken['query']['tokens']['csrftoken'],
        ];
    }

    private function login(string $loginToken): ?array
    {
        $loginResponse = $this->getBaseClient()
            ->asForm()
            ->post('api.php', $this->baseQuery + [
                'action' => 'login',
                'lgname' => config('services.sc_tools.bot_name'),
                'lgpassword' => config('services.sc_tools.bot_password'),
                'lgtoken' => $loginToken,
            ]);

        return $loginResponse->json('login');
    }

    private function getBaseClient(): PendingRequest
    {
        return Http::baseUrl(config('services.sc_tools.url'))
            ->withOptions([
                'cookies' => $this->cookies,
            ])
            ->withHeader('User-Agent', 'Star-Citizen-Wiki-API/v2.1.0')
            ->timeout(600)
            ->acceptJson();
    }

    private function createPage(Armor $armor)
    {
        $content = $this->prepareTemplate($armor);

        $response = $this->getBaseClient()
            ->asForm()
            ->post('api.php', $this->baseQuery + [
                'action' => 'edit',
                'title' => $this->getPageTitle($armor),
                'text' => $content,
                'md5' => md5($content),
                'bot' => true,
                'token' => $this->csrfToken,
            ]);
    }

    private function prepareTemplate(Armor $armor): string
    {
        $name = $this->getPageTitle($armor);

        $pageContent = $this->template;
        $pageContent = str_replace('<UUID>', $armor->uuid, $pageContent);
        $pageContent = str_replace('<ITEM NAME>', $name, $pageContent);
        $pageContent = str_replace(
            '<ITEM TYPE>',
            strtolower($this->typeMapping[$armor->type] ?? $armor->type),
            $pageContent
        );
        $pageContent = str_replace(
            '<ARMOR CLASS> ',
            $armor->sub_type === 'UNDEFINED' ? '' : strtolower($armor->sub_type.' '),
            $pageContent
        );
        $pageContent = str_replace('<MANUFACTURER CODE>', $armor->manufacturer->code, $pageContent);
        $pageContent = str_replace('<CURDATE>', Carbon::now()->format('Y-m-d'), $pageContent);

        $pageContent = str_replace('a under', 'an under', $pageContent);
        $pageContent = str_replace('a arms', 'an arms', $pageContent);

        if ($this->getSuffix($armor) !== null || (str_contains($armor->class_name, '_01_15') && str_contains($name, 'Black/Silver'))) {
            $info = sprintf(" This item is shown as '''%s''' in game.", $armor->name);

            $pageContent = str_replace(
                '<VARIANTINFO>',
                $info,
                $pageContent
            );
        } else {
            $pageContent = str_replace('<VARIANTINFO>', '', $pageContent);
        }

        if (str_contains($armor->class_name, '_01_15') && str_contains($name, 'Black/Silver')) {
            $pageContent = $pageContent."\n[[Category:Items with non-unique name in game]]";
        }

        return $pageContent;
    }

    private function getSuffix(Armor $armor): ?string
    {
        return match (true) {
            str_contains($armor->class_name, '_hd_sec') => ' Hurston Security',
            str_contains($armor->class_name, '_irn') => ' Iron',
            str_contains($armor->class_name, '_gld') => ' Gold',
            str_contains($armor->class_name, '_microtech') => ' microTech',
            str_contains($armor->class_name, '_carrack') && ! str_contains($armor->name, 'Carrack') => ' Carrack Edition',
            str_contains($armor->class_name, '_9tails') => ' (Nine Tails)',
            str_contains($armor->class_name, '_xenothreat') => ' (Xenothreat)',
            default => null
        };
    }

    private function getPageTitle(Armor $armor): string
    {
        $name = $armor->name.($this->getSuffix($armor) ?? '');
        if (str_contains($armor->class_name, '_01_15') && str_contains($name, 'Black/Silver')) {
            $name = str_replace('Black', 'Tan', $name);
        }

        return $name;
    }
}
