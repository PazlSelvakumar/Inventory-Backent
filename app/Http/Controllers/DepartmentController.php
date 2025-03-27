<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;


class DepartmentController extends BaseController
{
    
    //_Construct
    public function __construct()
    {
        // Middleware to check specific permissions for each method
        $this->middleware('auth');
        $this->middleware('permission:create-department')->only('store');
        $this->middleware('permission:edit-department')->only('update');
        $this->middleware('permission:delete-department')->only('destroy');
        $this->middleware('permission:view-department')->only('index', 'show');
    }
 
    // Show type based department
    public function showTypeBasedDepartments($type_id)
    {
        $encryptedTypeId = Crypt::encryptString($type_id);
        $decryptedTypeId = Crypt::decryptString($encryptedTypeId);
        $validator = Validator::make(['type_id' => $decryptedTypeId], [
            'type_id' => 'exists:type_masters,id', // Ensures type_id exists
        ]);
        if ($validator->fails()) {
            return response()->json([
               'message' => 'Invalid type_id',
                'error' => $validator->errors()
            ], 400);
        }
        $departments = Department::where('type_id', $type_id)->get();
        $departments = $departments->map(function ($department) {
            $department->encrypted_id = Crypt::encryptString($department->id);
            return $department;
        });
        
        return response()->json([
            'status' => 'Success',
            'data' => $departments,
            'message' => 'Departments Fetched successfully'
        ], 200);    
    }


   //index department
    public function index()
    {
        $department = Department::all();
        $data = $department->map(function ($department) {
            return [
                'id' => $department->id,
                'encrypted_id' => Crypt::encryptString($department->id),
                'type_id' => $department->type_id,
                'type_name' => $department->type->type_name,
                'department_name' => $department->department_name,
            ];
        });

        return response()->json([
            'data'=>$data
        ],200);
    }

     //show department
     public function edit($id)
     {
         $department = Department::findOrFail($id);
         return response()->json([
             'department' => $department
         ], 200);
     }
  

    //Store department
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'type_id' => 'required', 
            'department_name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }
        $req_department_name = removeSpecialCharacters($request->department_name);
        $existingType = Department::whereRaw("REPLACE(department_name, ' ', '') = ?", [$req_department_name])->first();
        if ($existingType) {
            return response()->json([
                'message' => 'Department name already exists'
            ], 400);
        }
            $decryptedTypeId = Crypt::decryptString($request->type_id);
            try {
                $department = new Department();
                $department->type_id = $decryptedTypeId;
                $department->department_name = $request->department_name;
                $department->save();

                $encryptedId = Crypt::encryptString($department->id);
        
                return response()->json([
                    'status' => 'success',
                    'message' => 'Department created successfully',
                    'data' => $department,
                    'encryptedId' => $encryptedId
                ], 201);
        
            } catch (QueryException $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Database Error: Could not insert department',
                    'error' => $e->getMessage(), 
                ], 500);
            }
    }


    //Update department
    public function update(Request $request, $id)
    {
        $request->validate([
            'type_id' =>'required',
            'department_name' =>'required|string|max:255',
        ]);
        $decryptId = Crypt::decryptString($id);
        $department = Department::findOrFail($decryptId);
        $decryptedTypeId = Crypt::decryptString($request->type_id);
        $department->update([
            'type_id' => $decryptedTypeId,
            'department_name' => $request->department_name,
        ]);
        $encryptedId = Crypt::encryptString($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Department updated successfully',
            'data' => $department,
            'encryptedId' => $encryptedId
        ], 200);
    }
    
    //Delete department
    public function destroy($id)
    {
        $decryptId = Crypt::decryptString($id);
        $department = Department::findOrFail($decryptId);
        $encryptedId = Crypt::encryptString($id);
        $department->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Department deleted successfully',
            'encryptedId' => $encryptedId
        ], 200);
    }

}
