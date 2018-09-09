<?php declare(strict_types = 1);

namespace Tests;

use App\Models\Account\Admin\AdminGroup;
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
    protected function createAdminGroups()
    {
        if (AdminGroup::count() === 0) {
            factory(AdminGroup::class)->states('bureaucrat')->create();
            factory(AdminGroup::class)->states('sysop')->create();
            factory(AdminGroup::class)->states('sichter')->create();
            factory(AdminGroup::class)->states('mitarbeiter')->create();
            factory(AdminGroup::class)->states('user')->create();
            factory(AdminGroup::class)->states('editor')->create();
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
