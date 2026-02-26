<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;

it('cleans query names for api searches', function () {
    $controller = new class extends Controller
    {
        public function exposeCleanQueryName(string $name): string
        {
            return $this->cleanQueryName($name);
        }
    };

    expect($controller->exposeCleanQueryName('Alpha_Centauri'))->toBe('Alpha Centauri')
        ->and($controller->exposeCleanQueryName('Sol%20System'))->toBe('Sol System');
});

it('normalizes search request query input', function () {
    $request = new class extends SearchRequest
    {
        public function runPrepareForValidation(): void
        {
            $this->prepareForValidation();
        }
    };

    $request = $request::create('/search', 'POST', ['query' => '  Sol_System  ']);
    $request->runPrepareForValidation();

    expect($request->input('query'))->toBe('Sol System');
});
