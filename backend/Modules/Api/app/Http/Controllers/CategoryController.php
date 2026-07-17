<?php

declare(strict_types=1);

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Api\Http\Resources\CategoryResource;
use Modules\Catalog\Models\Category;

/**
 * The storefront category tree for the mobile navigation. Returns the active root
 * categories with their active children eager-loaded, so the app builds its menu
 * from a single request.
 */
class CategoryController
{
    public function index(): AnonymousResourceCollection
    {
        $roots = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('position')
            ->get();

        return CategoryResource::collection($roots);
    }
}
