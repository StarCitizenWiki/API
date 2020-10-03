<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Category;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Category\Category;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

/**
 * Comm-Link Category Controller
 */
class CategoryController extends Controller
{
    /**
     * CommLinkController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * All Categories
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.rsi.comm-links.view');

        return view(
            'user.rsi.comm_links.categories.index',
            [
                'categories' => Category::query()->orderBy('name')->get(),
            ]
        );
    }

    /**
     * Get All Comm-Links in a given Category
     *
     * @param Category $category
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function show(Category $category): View
    {
        $this->authorize('web.user.rsi.comm-links.view');

        return view(
            'user.rsi.comm_links.index',
            [
                'commLinks' => $category->commLinks()
                    ->orderByDesc('cig_id')
                    ->paginate(20),
            ]
        );
    }
}
