<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $category = Category::create([
            'name' => $request->name
        ]);

        if ($category) {
            return response()->json(['message' => 'Kategória bola vytvorená'], Response::HTTP_CREATED);
        } else {
            return response()->json(['message' => 'Kategória nebola vytvorená'], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategória nebola nájdená'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategória nebola nájdená'], Response::HTTP_NOT_FOUND);
        }

        $category->update([
            'name' => $request->name
        ]);

        return response()->json(['message' => 'Kategória bola aktualizovaná', 'note' => $category]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategória nebola nájdená'], Response::HTTP_NOT_FOUND);
        }

        $category->delete();
        return response()->json(['message' => 'Kategória bola vymazaná']);
    }

    public function searchCategories(Request $request)
    {
        $query = $request->query('q');

        if (empty($query)) {
            return response()->json(['message' => 'Musíte zadať dopyt na vyhľadávanie'], Response::HTTP_BAD_REQUEST);
        }

        $categories = Category::searchByCategoryName($query); // Použitie vlastnej metódy z modelu

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'Žiadne kategórie sa nenašli'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($categories);
    }
}
