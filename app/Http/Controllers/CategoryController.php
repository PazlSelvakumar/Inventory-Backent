<?php

namespace App\Http\Controllers; 

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;


class CategoryController extends BaseController
{
    //Construct
    public function __construct()
    {
        // Middleware to check specific permissions for each method
        $this->middleware('auth');
        $this->middleware('permission:create-category')->only('store');
        $this->middleware('permission:edit-category')->only('update');
        $this->middleware('permission:delete-category')->only('destroy');
        $this->middleware('permission:view-category')->only('index', 'show');
    }
    
    //Show Departments based on the Category
    public function showDepartmentBasedCategories($dept_id)
    {
        $encryptedId = Crypt::encryptString($dept_id);
        $decryptedDeptId = Crypt::decryptString($encryptedId);
        $validator = Validator::make(['department_id' => $decryptedDeptId], [
            'department_id' => 'exists:departments,id', // Ensures department_id exists
        ]);
        if ($validator->fails()) {
            return response()->json([
            'message' => 'Invalid department_id',
                'error' => $validator->errors()
            ], 400);
        }
        $category = Category::where('department_id', $decryptedDeptId)->get();
        return response()->json([
            'status' => 'Success',
            'data' => $category,
            'message' => 'Categories Fetched successfully'
        ], 200);
    }

    // index
    public function index()
    {
        $categories = Category::all();
        $data=$categories->map(function ($category) {
            return [
                'id' => $category->id,
                'encrypted_id' => Crypt::encryptString($category->id),
                'category_name' => $category->category_name,
                'department_id' => $category->department_id,
            ];
        });
        return response()->json($data);
    }
    
    //Edit
    public function edit($id){ 
        $decryptedDeptId = Crypt::decryptString($id);
        $category = Category::findOrFail($decryptedDeptId);
        return response()->json([
            'category' => $category
        ], 200);
    }
    
    //Store
    public function store(Request $request)
    { 
        $validatedData = $request->validate([
            'category_name' =>'required|max:255',
            'department_id' =>'required',
        ]);
        if ($validatedData === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }
        $req_category_name = removeSpecialCharacters($request->category_name);
        $existingType = Category::whereRaw("REPLACE(category_name, ' ', '') = ?", [$req_category_name])->first();
        if ($existingType) {
            return response()->json([
                'message' => 'Category name already exists'
            ], 400);
        }

        $dept_id=$request->department_id;
        $decryptedDeptId = Crypt::decryptString($dept_id);
       
        try{
            $category = new Category();
            $category->department_id = $decryptedDeptId;
            $category->category_name = $request->category_name;
            $category->save();
            $encryptedId = Crypt::encryptString($category->id);
            return response()->json(['data'=> $category,'encryptedId'=>$encryptedId], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error: Could not insert Category',
                'error' => $e->getMessage(), // Shows the actual SQL error
            ], 500);
        }
    }
    
    //Update
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'department_id' =>'required',
            'category_name' =>'required|max:255',
        ]);
        if ($validatedData === null) {
            return response()->json([
               'status' => 'error',
               'message' => 'Validation failed',
            ], 422);
        }
        $req_category_name = removeSpecialCharacters($request->category_name);
        $existingType = Category::whereRaw("REPLACE(category_name, ' ', '') = ?", [$req_category_name])->first();
        if ($existingType) {
            return response()->json([
                'message' => 'Category name already exists'
            ], 400);
        }
        $decryptId = Crypt::decryptString($id);
        $category = Category::findOrFail($decryptId);
        $dept_id=$request->department_id;
        $decryptedDeptId = Crypt::decryptString($dept_id);
        $category->update([
            'department_id' => $decryptedDeptId,
            'category_name' => $request->category_name,
        ]);
        $encryptedId = Crypt::encryptString($id);
        return response()->json([
            'message' => 'Department updated successfully',
            'category' => $category,
            'encryptedId' => $id,
        ],  );
    }


    //Delete
    public function destroy($id)
    {
        $decryptId = Crypt::decryptString($id);
        $category = Category::findOrFail($decryptId);
        $category_name = $category->category_name;
        $category->delete();
        $encryptedId = Crypt::encryptString($id);
        return response()->json([
            'message' => $category_name.' deleted successfully',
            'encryptedId' => $encryptedId,
        ], 200);
    }
}
