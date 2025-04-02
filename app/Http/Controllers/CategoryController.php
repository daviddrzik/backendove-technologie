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
        try {
            // Validácia vstupu
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:64|unique:categories,name'
            ]);

            // Vytvorenie kategórie
            $category = Category::create([
                'name' => $validated['name']
            ]);

            return response()->json([
                'message' => 'Kategória bola vytvorená',
                'category' => $category
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            // Ak validácia zlyhá, vrátime chybové hlásenie
            return response()->json([
                'message' => 'Chyba pri vytváraní kategórie',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
        try {
            // Validácia vstupu
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:64|unique:categories,name,' . $id
            ]);

            // Nájdeme poznámku
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['message' => 'Kategória nebola nájdená'], Response::HTTP_NOT_FOUND);
            }

            // Aktualizujeme iba tie polia, ktoré sú v requeste
            $category->update($validated);

            return response()->json([
                'message' => 'Kategória bola aktualizovaná',
                'category' => $category
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chyba pri aktualizácii kategórie',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
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
