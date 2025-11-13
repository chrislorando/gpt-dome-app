<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('renders landing page hero and cta elements', function () {
    $response = get('/');

    $response->assertSuccessful();
    $response->assertSee('Explore AI models in one clean place', false);
    $response->assertSee('Start Exploring', false);
    $response->assertSee('100% Free', false);
    $response->assertSee('Multi Features', false);
    $response->assertSee('Open Source', false);
});
