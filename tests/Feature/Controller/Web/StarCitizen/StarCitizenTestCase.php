<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\StarCitizen;

use Tests\Feature\Controller\Web\UserTestCase;

/**
 * Creates System Languages on Setup
 */
class StarCitizenTestCase extends UserTestCase
{
    /**
     * {@inheritdoc}
     * Adds needed System Languages for Translations
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
    }
}
