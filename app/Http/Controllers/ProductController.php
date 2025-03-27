<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controller as BaseController;

class ProductController extends BaseController
{

    public function __construct()
    {
        // Middleware to check specific permissions for each method
        $this->middleware('permission:create-product')->only('store');
        $this->middleware('permission:edit-product')->only('update');
        $this->middleware('permission:delete-product')->only('destroy');
        $this->middleware('permission:view-product')->only('index', 'show');
    }

    //Show Category based on the Products
    public function showCategoryBasedProducts($category_id)
    {
        $decryptedCategoryId = Crypt::decryptString($category_id);
        $validator = Validator::make(['category_id' => $decryptedCategoryId], [
            'category_id' => 'exists:categories,id', // Ensures type_id exists
        ]);
        if ($validator->fails()) {
            return response()->json([
               'message' => 'Invalid category_id',
                'error' => $validator->errors()
            ], 400);
        }
        $Products = Product::where('category_id', $decryptedCategoryId)->get();
        $Products->transform(function ($product) {
            $product->encrypted_id = Crypt::encryptString($product->id);
            return $product;
        });

        return response()->json([
            'status' =>"success",
            'data' => $Products,
            'message' => 'Products fetched successfully',
        ], 200);
    }


    public function index()
    {
        $product = Product::all();
        $data=$product->map(function ($product) {
            return [
                'id' => $product->id,
                'encrypted_id' => Crypt::encryptString($product->id),
                'category_id' => $product->category_id,
                'category_name' => $product->category->category_name,
                'product_name' => $product->product_name,
                'product_price' => $product->product_price,
                'product_description' => $product->product_description,
                'product_code' => $product->product_code,
                'hsn_code' => $product->hsn_code,
                'cgst' => $product->cgst,
                'sgst' => $product->sgst,
                'igst' => $product->igst,
                'total' => $product->total
            ];
        });
        return response()->json([
            'data' => $data,
        ], 200);
    }


    //edit
    public function edit (Request $request,$id){
        $decryptId=Crypt::decryptString($id);
        $products=Product::findOrFail($decryptId);
       return response()->json([
        'products' => $products
       ],200);
    }

    //Store the product
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!auth()->user()->can('create-product')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $validator = Validator::make($request->all(), [
            
            'category_id'=>'required|max:255', 
            'product_name'=>'required|max:255', 
            'product_price'=>'required|max:255',
            'product_description'=>'required|max:255', 
            'product_code'=>'required|max:255',
            'hsn_code'=>'required|max:255',
            'cgst'=>'required|max:255',
            'sgst'=>'required|max:255',
            'igst'=>'required|max:255',
            'total'=>'required|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }
        $req_product_name = removeSpecialCharacters($request->product_name);
        $existingType = Product::whereRaw("REPLACE(product_name, ' ', '') = ?", [$req_product_name])->first();
        if ($existingType) {
            return response()->json([
                'message' => 'Product name already exists'
            ], 400);
        }
        $category_id = Crypt::decryptString($request->category_id);
        try {
            $product = new Product();
            $product->category_id = $category_id;
            $product->product_name = $request->product_name;
            $product->product_price = $request->product_price;
            $product->product_description = $request->product_description;
            $product->product_code = $request->product_code;
            $product->hsn_code = $request->hsn_code;
            $product->cgst = $request->cgst;
            $product->sgst = $request->sgst;
            $product->igst = $request->igst;
            $product->total = $request->total;
            $product->save();
            $encryptedId=Crypt::encryptString($product->id);
            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => $product,
                'encrypted_id' => $encryptedId
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error: Could not insert product',
                'error' => $e->getMessage(), 
            ], 500);
        }
    }

    //update department
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id'=>'required|max:255', 
            'product_name'=>'required|max:255', 
            'product_price'=>'required|max:255',
            'product_description'=>'required|max:255', 
            'product_code'=>'required|max:255',
            'hsn_code'=>'required|max:255',
            'cgst'=>'required|max:255',
            'sgst'=>'required|max:255',
            'igst'=>'required|max:255',
            'total'=>'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }

        $decryptId = Crypt::decryptString($id);
        $product = Product::findOrFail($decryptId);
        $category_id = Crypt::decryptString($request->category_id);

        $product->update([
            'category_id' => $category_id,
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'product_description' => $request->product_description,
            'product_code' => $request->product_code,
            'hsn_code' => $request->hsn_code,
            'cgst' => $request->cgst,
            'sgst' => $request->sgst,
            'igst' => $request->igst,
            'total' => $request->total,
        ]);

        return response()->json([
            'status' =>'success',
            'message' => 'Product updated successfully',
            'product' => $product,
            'encrypted_id' => $id
        ], 200);
    }

    // DeleteProduct
    public function destroy($id)
    {
        $decryptedCategoryId = Crypt::decryptString($id);
        $product = Product::findOrFail($decryptedCategoryId);
        $encryptedId=Crypt::encryptString($product->id);
        $product->delete();

        return response()->json([
            'status' =>'success',
            'message' => $product->product_name.' deleted successfully',
            'encrypted_id' => $encryptedId
        ], 200);
    }


    // Calculate the total
    public function calculateTotal(Request $request)
    {
        $request->validate([
            'cgst' => 'numeric|min:0',
            'sgst' => 'numeric|min:0',
            'igst' => 'numeric|min:0',
            'product_price' => 'numeric|min:0',
        ]);
        $cgst = $request->input('cgst');
        $sgst = $request->input('sgst');
        $igst = $request->input('igst');
        $product_price = $request->input('product_price');
        $total_gst = $cgst + $sgst + $igst;
        $total = $product_price + ($product_price * $total_gst / 100);
        return response()->json([
            'success' => true,
            'data' => [
                'product_price' => $product_price,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'total' => $total,
            ],
        ], 200);
    }
}
