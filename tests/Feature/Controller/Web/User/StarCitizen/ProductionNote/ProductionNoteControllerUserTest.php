<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\StarCitizen\ProductionNote;

use App\Models\Account\User\UserGroup;
use Illuminate\Http\Response;

/**
 * @covers \App\Policies\Web\User\TranslationPolicy<extended>
 *
 * @covers \App\Models\StarCitizen\ProductionNote\ProductionNote<extended>
 */
class ProductionNoteControllerUserTest extends ProductionNoteControllerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const RESPONSE_STATUSES = [
        'index' => Response::HTTP_OK,

        'edit' => Response::HTTP_FORBIDDEN,
        'edit_not_found' => Response::HTTP_NOT_FOUND,

        'update' => Response::HTTP_FORBIDDEN,
        'update_not_found' => Response::HTTP_NOT_FOUND,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
