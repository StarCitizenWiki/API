<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\CommLink;

/**
 * Comm Link Category Controller
 */
class CategoryController extends Controller
{
    /**
     * Get All Comm Links in a given Category
     *
     * @param \App\Models\Rsi\CommLink\Category\Category $category
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $links = CommLink::where('category_id', $category->id)->paginate(20);

        return view(
            'admin.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
