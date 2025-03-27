<?php

namespace App\Http\Controllers;

use App\Models\TypeMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Routing\Controller as BaseController;


class TypeController extends BaseController
{ 

    public function __construct()
    {
        // Middleware to check specific permissions for each method
        $this->middleware('auth');

        
        $this->middleware('permission:create-type')->only('store');
        $this->middleware('permission:edit-type')->only('update');
        $this->middleware('permission:delete-type')->only('destroy');
        $this->middleware('permission:view-type')->only('index', 'show');
    }


    //my helper function
    public function test(Request $request) {
        $req_type_name = removeSpecialCharacters($request->name);
        return $req_type_name;
    }

    //index type
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $type = TypeMaster::all();
        $data = $type->map(function ($type) {
            return [
                'id' => $type->id,
                'encrypted_id' => Crypt::encryptString($type->id), 
                'type_name' => $type->type_name, 
            ];
        });
        return response()->json([
            'message' => 'Token is valid!',
            'data' => $data,
        ], 200);

    }   





    //store type
    public function store(Request $request)
    {       
        $request->validate([
            'type_name' => 'required|string|max:255',
        ]);
        $req_type_name = removeSpecialCharacters($request->type_name);
        $existingType = TypeMaster::whereRaw("REPLACE(type_name, ' ', '') = ?", [$req_type_name])->first();
        if ($existingType) {
            return response()->json([
                'message' => 'Type name already exists'
            ], 400);
        }

        $type = new TypeMaster();
        $type->type_name = $request->type_name;
        $type->save();

        $id = $type->id;         
        $encryptedId = Crypt::encryptString(value: $type->id); 

        return response()->json(
            ['data'=> $type, 'encryptedId' => $encryptedId],201);
    }

    //show type
    public function edit($id)
    {
        $decryptId = Crypt::decryptString($id);
        $type = TypeMaster::findOrFail($decryptId);

        return response()->json([
            'type' => $type,
            'encryptId'=>$id
        ], 200);
    }
 

    //update type
    public function update(Request $request, $id)
    {
        $request->validate([
            'type_name' =>'required|string|max:255',
        ]);

        $req_type_name = removeSpecialCharacters($request->type_name);
        $existingType = TypeMaster::whereRaw("REPLACE(type_name, ' ', '') = ?", [$req_type_name])->first();
        if ($existingType) {
            return response()->json([
                'message' => 'Type name already exists'
            ], 400);
        }
        $decryptId = Crypt::decryptString($id);
        $type = TypeMaster::findOrFail($decryptId);
        $type->update([
            'type_name' => $request->type_name
        ]);
        $encryptedId = Crypt::encryptString($id); 

        return response()->json([
            'message' => 'Type updated successfully',
            'encryptedId'=>$encryptedId,
            'type' => $type
        ], 200);
    }


    //delete type
    public function destroy($id)
    {
        $decryptId = Crypt::decryptString($id);
        $type = TypeMaster::findOrFail($decryptId);
        $type_name = $type->type_name;
        $type->delete();

        $encryptedId = Crypt::encryptString(value: $id);

        return response()->json([        
            'message' =>  $type_name .' is deleted successfully',
            'encryptedId' => $encryptedId
        ], 200);
    }
}


