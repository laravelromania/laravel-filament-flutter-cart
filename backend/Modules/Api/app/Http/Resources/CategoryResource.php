<?php

declare(strict_types=1);

namespace Modules\Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Models\Category;

/**
 * A storefront category. When the `children` relation is loaded it is serialised
 * recursively, so `GET /api/v1/categories` can return the whole tree in one call.
 *
 * @property-read Category $resource
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $category = $this->resource;

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->parent_id,
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
