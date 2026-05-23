<?php

declare(strict_types=1);

it('renders the starmap locations link in the main side menu', function (): void {
    $view = $this->blade('<x-app.main-sidemenu />');

    $view->assertSee(route('web.locations.index'), false)
        ->assertSeeText('Locations');
});
