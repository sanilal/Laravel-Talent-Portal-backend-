<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories with their subcategories
     * GET /api/v1/public/categories
     * 
     * Compatible with yourmoca.com structure
     */
    public function index(): JsonResponse
    {
        $categories = Category::with(['subcategories' => function ($query) {
            $query->active()->ordered();
        }])
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'category' => [
                        'id' => $category->id,
                        'categoryName' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'icon' => $category->icon,
                        'color' => $category->color,
                        'orderType' => $category->sort_order,
                        'subcategories' => $category->subcategories->map(function ($sub) {
                            return [
                                'id' => $sub->id,
                                'subcategoryName' => $sub->name,
                                'slug' => $sub->slug,
                                'description' => $sub->description,
                                'icon' => $sub->icon,
                            ];
                        }),
                    ],
                ];
            });

        return response()->json([
            'status' => 1,
            'message' => 'Data Retrieved Successfully',
            'data' => $categories,
        ]);
    }

    /**
     * Get a single category with subcategories
     * GET /api/v1/public/categories/{id}
     */
    public function show($id): JsonResponse
    {
        $category = Category::with(['subcategories' => function ($query) {
            $query->active()->ordered();
        }])->findOrFail($id);

        return response()->json([
            'status' => 1,
            'message' => 'Category retrieved successfully',
            'data' => [
                'id' => $category->id,
                'categoryName' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => $category->icon,
                'color' => $category->color,
                'orderType' => $category->sort_order,
                'subcategories' => $category->subcategories->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'subcategoryName' => $sub->name,
                        'slug' => $sub->slug,
                        'description' => $sub->description,
                        'icon' => $sub->icon,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get subcategories for a specific category
     * GET /api/v1/public/categories/{categoryId}/subcategories
     */
    public function subcategories($categoryId): JsonResponse
    {
        $category = Category::findOrFail($categoryId);
        
        $subcategories = Subcategory::where('category_id', $categoryId)
            ->active()
            ->ordered()
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'subcategoryName' => $sub->name,
                    'slug' => $sub->slug,
                    'description' => $sub->description,
                    'icon' => $sub->icon,
                ];
            });

        return response()->json([
            'status' => 1,
            'message' => 'Subcategories retrieved successfully',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
            'data' => $subcategories,
        ]);
    }

    /**
     * Get a single subcategory
     * GET /api/v1/public/subcategories/{id}
     */
    public function showSubcategory($id): JsonResponse
    {
        $subcategory = Subcategory::with('category')->findOrFail($id);

        return response()->json([
            'status' => 1,
            'message' => 'Subcategory retrieved successfully',
            'data' => [
                'id' => $subcategory->id,
                'subcategoryName' => $subcategory->name,
                'slug' => $subcategory->slug,
                'description' => $subcategory->description,
                'icon' => $subcategory->icon,
                'category' => [
                    'id' => $subcategory->category->id,
                    'categoryName' => $subcategory->category->name,
                    'slug' => $subcategory->category->slug,
                ],
            ],
        ]);
    }
}