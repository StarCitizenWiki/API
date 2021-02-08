<?php declare(strict_types = 1);

namespace Tests;

use App\Models\Account\User\UserGroup;
use App\Models\System\Language;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    protected static $application;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Create all Admin Groups
     */
    protected function createUserGroups()
    {
        if (UserGroup::count() === 0) {
            UserGroup::factory()->bureaucrat()->create();
            UserGroup::factory()->sysop()->create();
            UserGroup::factory()->sichter()->create();
            UserGroup::factory()->mitarbeiter()->create();
            UserGroup::factory()->user()->create();
        }
    }

    /**
     * Creates all System Languages
     */
    protected function createSystemLanguages()
    {
        if (Language::count() === 0) {
            Language::factory()->create(
                [
                    'locale_code' => 'en_EN',
                ]
            );
            Language::factory()->create(
                [
                    'locale_code' => 'de_DE',
                ]
            );
        }
    }
}
