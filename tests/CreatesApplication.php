<?php declare(strict_types = 1);

namespace Tests;

use App\Models\Account\Admin\AdminGroup;
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
        if (!isset(static::$application)) {
            static::$application = require __DIR__.'/../bootstrap/app.php';
            static::$application->make(Kernel::class)->bootstrap();
        }

        return static::$application;
    }

    protected function createAdminGroups()
    {
        factory(AdminGroup::class)->states('bureaucrat')->create();
        factory(AdminGroup::class)->states('sysop')->create();
        factory(AdminGroup::class)->states('sichter')->create();
        factory(AdminGroup::class)->states('mitarbeiter')->create();
        factory(AdminGroup::class)->states('user')->create();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        // Required if you use HTTP tests
        $this->app['auth']->guard(null)->logout();
        $this->app['auth']->shouldUse(null);
        $this->flushSession();

        // Always required
        $this->app = null;
        parent::tearDown();
    }
}
