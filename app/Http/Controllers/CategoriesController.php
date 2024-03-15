<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;



class CategoriesController extends Controller
{

    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json(["data" => $categories], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }




    public function store(CreateCategoryRequest $request)
    {
        try {

            $request->validated();
            $category = Category::create([
                "name" => $request['name'],
            ]);

            return response()->json(["data" => $category], 201, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }


    public function show(string $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) return response()->json(["error" => "Kategorija sa ovim ID-em ne postoji."], 404, ['status' => 'fail']);
            return response()->json(["data" => $category], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }



    public function update(Request $request, string $id)
    {
        try {
            $category = Category::findOrFail($id);
            if (!$category) return response()->json(["error" => "Kategorija sa ovim ID-em ne postoji."], 404, ['status' => 'fail']);

            $rules = [
                "name" => "required|unique:categories,name," . $category->id
            ];

            $validateRules = $request->validate($rules);
            $category->update($validateRules);

            return response()->json(["data" => $category], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }


    public function destroy(string $id)
    {
        try {
            Category::destroy($id);
            return response()->json([], 204, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
}
