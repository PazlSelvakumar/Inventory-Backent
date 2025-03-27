<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierBranch;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;


class SupplierController extends BaseController
{

    public function __construct()
    {
        // Middleware to check specific permissions for each method
        $this->middleware('permission:create-supplier')->only('store');
        $this->middleware('permission:edit-supplier')->only('update');
        $this->middleware('permission:delete-supplier')->only('destroy');
        $this->middleware('permission:view-supplier')->only('index', 'show');
    }

    // Show Product Based Suppliers
    public function showProductBasedSuppliers($prct_id)
    {
        $decryptedPrctId = Crypt::decryptString($prct_id);
        $validator = Validator::make(['product_id' => $decryptedPrctId], [
            'product_id' => 'exists:products,id', // Ensures type_id exists
        ]);
        if ($validator->fails()) {
            return response()->json([
               'message' => 'Invalid type_id',
                'error' => $validator->errors()
            ], 400);
        }
        $suppliers = Supplier::where('product_id', $decryptedPrctId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $suppliers,
            'message' => 'Suppliers fetched successfully'
        ], 200);    
    }

    // index
    public function index()
    {
        $supplier = Supplier::all();
        $data=$supplier->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'encrypted_id' => Crypt::encryptString($supplier->id),
                'product_id' => $supplier->product_id,
                'supplier_name' => $supplier->supplier_name,
                'branch_name' => $supplier->branch_name,
                'branch_address'=> $supplier->branch_address,
                'gst_number'=> $supplier->gst_number
            ];
        });
        return response()->json(['data'=> $data ] ,200);
    }

    // store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>'required|max:255', 
            'supplier_name'=>'required|max:255', 
            'branch_name'=>'required|max:255', 
            'branch_address'=>'required|max:255', 
            'gst_number'=>'required|max:255',  
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }
        $req_supplier_name = removeSpecialCharacters($request->supplier_name);
        $req_supplier_branch_name = removeSpecialCharacters($request->branch_name);
        $req_supplier_gst_number = removeSpecialCharacters($request->gst_number);
        
        $existingSupplier = Supplier::whereRaw("REPLACE(supplier_name, ' ', '') = ?", [$req_supplier_name])
        ->whereRaw("REPLACE(branch_name, ' ', '') = ?", [$req_supplier_branch_name])
        ->whereRaw("REPLACE(gst_number, ' ', '') = ?", [$req_supplier_gst_number])
        ->first();
    
        if ($existingSupplier) {
            // Supplier exists
            return response()->json(['message' => 'Supplier already exists'], 409);
        }

        try {
            $supplier = new Supplier();
            $supplier->product_id = $request->product_id;
            $supplier->supplier_name = $request->supplier_name;
            $supplier->branch_name = $request->branch_name;
            $supplier->branch_address = $request->branch_address;
            $supplier->gst_number = $request->gst_number;
            $supplier->save();

            $encryptedId = Crypt::encryptString($supplier->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier created successfully',
                'data' => $supplier,
                'encrypted_id' => $encryptedId
            ], 201);
    
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error: Could not insert department',
                'error' => $e->getMessage(), 
            ], 500);
        }
    }


//edit
    public function edit($id){
        $decryptId=Crypt::decryptString($id);
        $suppliers = Supplier::findOrFail($decryptId);
        return response()->json([
          "suppliers"=>$suppliers
        ]);
    }

    // update_supplier
    public function update(Request $request, $id)

    {
        $validator = Validator::make($request->all(), [
            'product_id'=>'required|max:255', 
            'supplier_name'=>'required|max:255', 
            'branch_name'=>'required|max:255', 
            'branch_address'=>'required', 
            'gst_number'=>'required|max:255', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }

       
        $req_supplier_name = removeSpecialCharacters($request->supplier_name);
        $req_supplier_branch_name = removeSpecialCharacters($request->branch_name);
        $req_supplier_gst_number = removeSpecialCharacters($request->gst_number);
        
        $existingType = Supplier::whereRaw("REPLACE(supplier_name, ' ', '') = ?", [$req_supplier_name])
        ->whereRaw("REPLACE(branch_name, ' ', '') = ?", [$req_supplier_branch_name])
        ->whereRaw("REPLACE(gst_number, ' ', '') = ?", [$req_supplier_gst_number])
        ->first();

        if ($existingType) {
            // Supplier exists
            return response()->json(['message' => 'Supplier already exists'], 409);
        }

        try {
            // echo $id;exit;
            $decryptedId = Crypt::decryptString($id);
            $supplier = Supplier::findOrFail($decryptedId);
            $supplier->product_id = $request->product_id;
            $supplier->supplier_name = $request->supplier_name;
            $supplier->branch_name = $request->branch_name;
            $supplier->branch_address = $request->branch_address;
            $supplier->gst_number = $request->gst_number;
            $supplier->save();

            $encryptedId = Crypt::encryptString($supplier->id);
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier updated successfully',
                'data' => $supplier,
                'encrypted_id' => $encryptedId
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error: Could not update department',
                'error' => $e->getMessage(), 
            ], 500);    
        }
    }

    // delete_supplier
    public function destroy($id)
    {
        $decryptedId = Crypt::decryptString($id);
        $supplier = Supplier::findOrFail($decryptedId);
        $encryptedId = Crypt::encryptString($supplier->id);
        $supplier->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Supplier deleted successfully',
            'encrypted_id' => $encryptedId
        ], 200);
    }

    // get_branch details
    public function get_branch_details($supplier_id)
    {
        $decryptId = Crypt::decryptString($supplier_id);
        $supplierBranch = SupplierBranch::where('supplier_id', $decryptId)->get();
        $supplier = Supplier::findOrFail($decryptId);

        return response()->json([
            'supplier' =>$supplier,
            'supplierBranch' => $supplierBranch
        ], 200);
    }
}
