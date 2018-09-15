<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:58
 */

namespace Tests\Feature\Controller\Web\Admin\StarCitizen;

use Tests\Feature\Controller\Web\Admin\AdminTestCase;

/**
 * Creates System Languages on Setup
 */
class StarCitizenTestCase extends AdminTestCase
{
    /**
     * {@inheritdoc}
     * Adds needed System Languages for Translations
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createSystemLanguages();
    }
}
