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
            factory(UserGroup::class)->states('bureaucrat')->create();
            factory(UserGroup::class)->states('sysop')->create();
            factory(UserGroup::class)->states('sichter')->create();
            factory(UserGroup::class)->states('mitarbeiter')->create();
            factory(UserGroup::class)->states('user')->create();
            factory(UserGroup::class)->states('editor')->create();
        }
    }

    /**
     * Creates all System Languages
     */
    protected function createSystemLanguages()
    {
        if (Language::count() === 0) {
            factory(Language::class)->create(
                [
                    'locale_code' => 'en_EN',
                ]
            );
            factory(Language::class)->create(
                [
                    'locale_code' => 'de_DE',
                ]
            );
        }
    }
}
