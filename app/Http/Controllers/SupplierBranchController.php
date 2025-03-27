<?php

namespace App\Http\Controllers;

use App\Models\SupplierBranch;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;


class SupplierBranchController extends BaseController
{

    public function __construct()
    {
        // Middleware to check specific permissions for each method
        $this->middleware('permission:create-supplier-branch')->only('store');
        $this->middleware('permission:edit-supplier-branch')->only('update');
        $this->middleware('permission:delete-supplier-branch')->only('destroy');
        $this->middleware('permission:view-supplier-branch')->only('index', 'show');
    }
    

    //  Show Supplier Based Supplier Branches
    public function showSupplierBasedSupplierBranches($supplier_id)
    {
        $decrySupplierId = Crypt::decryptString($supplier_id);
        $validator = Validator::make(['supplier_id' => $decrySupplierId], [
            'supplier_id' => 'exists:suppliers,id', // Ensures type_id exists
        ]);
        if ($validator->fails()) {
            return response()->json([
               'message' => 'Invalid supplier_id',
                'error' => $validator->errors()
            ], 400);
        }
        $supplier_branch = SupplierBranch::where('supplier_id', $decrySupplierId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $supplier_branch,
            'message' => 'Supplier Branches fetched successfully'
        ], 200);    
    }

    //index
    public function index()
    {
        $supplierbranch = SupplierBranch::all();
        $supplierbranch = $supplierbranch->map(function ($supplierbranch) {
            return [
                'id' => $supplierbranch->id,
                'encrypted_id' => Crypt::encryptString($supplierbranch->id),
                'supplier_id' => $supplierbranch->supplier_id,
                'supplier_name' => $supplierbranch->supplier->supplier_name,
                'branch_name' => $supplierbranch->branch_name,
                'mail_id' => $supplierbranch->mail_id,
                'mobile_number' => $supplierbranch->mobile_number,
                'phone_number' => $supplierbranch->phone_number,
                'branch_address' => $supplierbranch->branch_address,
                'tin_number' => $supplierbranch->tin_number,
                'gst_number' => $supplierbranch->gst_number
            ];
        });
        return response()->json(['data'=> $supplierbranch], 200);
    }

    //store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id'=>'required|max:255', 
            'branch_name'=>'required|max:255', 
            'mail_id'=>'required|max:255',
            'mobile_number'=>'required|max:255', 
            'phone_number'=>'required|max:255',
            'branch_address'=>'required|max:255',
            'tin_number'=>'required|max:255',
            'gst_number'=>'required|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }
        $req_supplier_branch_name = removeSpecialCharacters($request->branch_name);
        $req_supplier_gst_number = removeSpecialCharacters($request->gst_number);
        $existingSupplierBranch = SupplierBranch::where('supplier_id', $request->supplier_id)
            ->whereRaw("REPLACE(branch_name, ' ', '') = ?", [$req_supplier_branch_name])
            ->whereRaw("REPLACE(gst_number, ' ', '') = ?", [$req_supplier_gst_number])
            ->first();
        if ($existingSupplierBranch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier branch already exists',
            ], 409);
        }

        try {
            $supplier_branch = new SupplierBranch();
            $supplier_branch->supplier_id = $request->supplier_id;
            $supplier_branch->branch_name = $request->branch_name;
            $supplier_branch->mail_id = $request->mail_id;
            $supplier_branch->mobile_number = $request->mobile_number;
            $supplier_branch->phone_number = $request->phone_number;
            $supplier_branch->branch_address = $request->branch_address;
            $supplier_branch->tin_number = $request->tin_number;
            $supplier_branch->gst_number = $request->gst_number;
            $supplier_branch->save();
            $encryptedId = Crypt::encryptString($supplier_branch->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier Branch created successfully',
                'data' => $supplier_branch,
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

    // update_supplier_branch
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id'=>'required|max:255', 
            'branch_name'=>'required|max:255', 
            'mail_id'=>'required|max:255',
            'mobile_number'=>'required|max:255',
            'phone_number'=>'required|max:255',
            'branch_address'=>'required|max:255',
            'tin_number'=>'required|max:255',
            'gst_number'=>'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }
            $decryptId = Crypt::decryptString($id);
            $supplier_branch = SupplierBranch::findOrFail($decryptId);
            $supplier_branch->supplier_id = $request->supplier_id;
            $supplier_branch->branch_name = $request->branch_name;
            $supplier_branch->mail_id = $request->mail_id;
            $supplier_branch->mobile_number = $request->mobile_number;
            $supplier_branch->phone_number = $request->phone_number;
            $supplier_branch->branch_address = $request->branch_address;
            $supplier_branch->tin_number = $request->tin_number;
            $supplier_branch->gst_number = $request->gst_number;
            $supplier_branch->save();

            $encryptId = Crypt::encryptString($supplier_branch->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier Branch updated successfully',
                'data' => $supplier_branch,
                'encrypted_id' => $encryptId
            ], 200);
    }

    // delete_supplier_branch
    public function destroy($id)
    {
        $decryptId=Crypt::decryptString($id);
        $supplier_branch = SupplierBranch::findOrFail($decryptId);
        $encryptId = Crypt::encryptString($supplier_branch->id);
        $supplier_branch->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Supplier Branch deleted successfully',
            'encrypted_id' => $encryptId
        ], 200);
    }
}
