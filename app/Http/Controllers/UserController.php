<?php
namespace App\Http\Controllers; 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;

use Spatie\Permission\Models\Role;
use Hash;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;


class UserController extends BaseController
{
    
    public function __construct(){

        $this->middleware('auth');
        $this->middleware('permission:create-users')->only('store');
        $this->middleware('permission:edit-users')->only('update');
        $this->middleware('permission:delete-users')->only('destroy');
        $this->middleware('permission:view-users')->only('index', 'show');

    }

// public function __construct()
// {
//     $this->middleware('auth');
//     $this->middleware(function ($request, $next) {
//         $user = auth()->user(); // Get the authenticated user
//         if ($user) {
//             $userPermissions = json_decode($user->permissions, true) ?? [];
//             if (!empty($userPermissions)) {
//                 $viewUserPermissions = Permission::whereIn('id', $userPermissions)->pluck('name')->toArray();
//                 if (!in_array("view-users", $viewUserPermissions)) {
//                     abort(403, "Unauthorized: You don't have permission to view users.");
//                 }
//             } else {
//                 abort(403, "Unauthorized: No valid permissions found.");
//             }
//         } else {
//             abort(403, "Unauthorized: User not found.");
//         }
//         return $next($request);
//     })->only(['index', 'show']);
// }




// public function __construct()
// {
//     $this->middleware('auth');

//     $this->middleware(function ($request, $next) {
//         $user = auth()->user(); // Get the authenticated user
//             $userPermissions = json_decode($user->permissions, true) ?? [];
//                 $viewUserPermissions = Permission::whereIn('id', $userPermissions)->pluck('name')->toArray();
//                 if (!in_array("view-users", $viewUserPermissions)) {
//                     abort(403, "Unauthorized: You don't have permission to view users.");
//                 }
//     })->only(['index', 'show']);


//     $this->middleware(function ($request, $next) {
//         $user = auth()->user(); // Get the authenticated user
//             $userPermissions = json_decode($user->permissions, true) ?? [];
//                 $viewUserPermissions = Permission::whereIn('id', $userPermissions)->pluck('name')->toArray();
//                 if (!in_array("create-users", $viewUserPermissions)) {
//                     abort(403, "Unauthorized: You don't have permission to view users.");
//                 }
//     })->only(['create','store']);


//     $this->middleware(function ($request, $next) {
//         $user = auth()->user(); // Get the authenticated user
//             $userPermissions = json_decode($user->permissions, true) ?? [];
//                 $viewUserPermissions = Permission::whereIn('id', $userPermissions)->pluck('name')->toArray();
//                 if (!in_array("edit-users", $viewUserPermissions)) {
//                     abort(403, "Unauthorized: You don't have permission to view users.");
//                 }
//     })->only(['update']);


//     $this->middleware(function ($request, $next) {
//         $user = auth()->user(); // Get the authenticated user
//             $userPermissions = json_decode($user->permissions, true) ?? [];
//                 $viewUserPermissions = Permission::whereIn('id', $userPermissions)->pluck('name')->toArray();
//                 if (!in_array("delete-users", $viewUserPermissions)) {
//                     abort(403, "Unauthorized: You don't have permission to view users.");
//                 }
//     })->only(['destroy']);
// }



// public function __construct()
// {
//     $this->middleware('auth');

//     $this->middleware(function ($request, $next) {
//         $user = auth()->user(); // Get the authenticated user

//         if ($user) {
//             // Fetch user's permissions once and reuse
//             $userPermissions = json_decode($user->permissions, true) ?? [];
//             $userPermissionNames = Permission::whereIn('id', $userPermissions)->pluck('name')->toArray();

//             $routePermissions = [
//                 'index'   => 'view-users',
//                 'show'    => 'view-users',
//                 'create'  => 'create-users',
//                 'store'   => 'create-users',
//                 'update'  => 'edit-users',
//                 'destroy' => 'delete-users',
//             ];

//             foreach ($routePermissions as $method => $permission) {
//                 if (request()->routeIs($method) && !in_array($permission, $userPermissionNames)) {
//                     abort(403, "Unauthorized: You don't have permission to {$permission}.");
//                 }
//             }
//         } else {
//             abort(403, "Unauthorized: No valid user found.");
//         }

//         return $next($request);
//     });
// }



















    

    public function test()
    {
        $user = auth()->user();
        return $user;


        $roles = Role::all();
        $permissions = Permission::all(); 
        $user = Auth::user();
        
        
        
        $userPermissions = json_decode($user->permissions, true) ?? []; 
        
        $matchedPermissions = $permissions->whereIn('id', $userPermissions);

        
        if ($matchedPermissions->isNotEmpty()) {
            
            return response()->json([
                'message' => 'User has valid permissions',
                'matched_permissions' => $matchedPermissions
            ]);
        } else {
            
            return response()->json([
                'message' => 'User does not have valid permissions'
            ], 403);
        }
        
        
    }


    public function checkPermissions()
    {

        $user = Auth::user();

        $permissions = Permission::all(); 
       
        $userPermissions = json_decode($user->permissions, true) ?? []; 
        
        $matchedPermissions = $permissions->whereIn('id', $userPermissions);



        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $matchedPermissions,
            'has_admin_role' => $user->hasRole('admin'),
            'can_create_user' => $user->can('role-create')
        ]);
    }


    public function index()
    {    
        $users = User::all();
        
        $data = $users->map(function($user){
            return[
               'id'=>$user->id,
               'type_id'=>$user->type_id,
               'dept_id'=>$user->dept_id,
               'encryptedUserId'=>Crypt::encryptString($user->id),
               'encryptedTypeId'=>Crypt::encryptString($user->type_id),
               'encryptedDepartmentId'=>Crypt::encryptString($user->dept_id),
               'name'=>$user->name,
               'email'=>$user->email,
               'role'=>$user->role,
            ];

        });

        

        return response()->json([
            'message' => 'Welcome to the Admin Dashboard',
            'users' =>$data
        ]);
    }


    
    public function create()
    {
        $roles = Role::all();

        $user = Auth::user();

        $permissions = Permission::all(); 
       
        $userPermissions = json_decode($user->permissions, true) ?? []; 
        
        $matchedPermissions = $permissions->whereIn('id', $userPermissions);

        
        return response()->json([
         'roles'=>$roles,
         'permissions'=>$matchedPermissions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'sometimes|nullable|string|max:255',
            'dept_id' => 'sometimes|nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email', 
            'role' => 'required',
            'password' => 'required|string|min:8',
            'permissions' => 'sometimes|array|nullable',
            'permissions.*' => 'exists:permissions,id' 
        ]);

        try {
            $decrypt_type_id = $request->type_id ? Crypt::decryptString($request->type_id) : null;
            $decrypt_dept_id = $request->dept_id ? Crypt::decryptString($request->dept_id) : null;
            $strtolowerRole = $request->role ? strtolower($request->role) : null;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid encrypted data'], 400);
        }


        // Create User
        $user = new User();
        $user->type_id = $decrypt_type_id;
        $user->dept_id = $decrypt_dept_id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role = $strtolowerRole;


        if ($request->has('permissions') && is_array($request->permissions)) {
            $user->permissions = json_encode($request->permissions);
        } else {
            $user->permissions = null;
        }


        $user->save();

        $role = Role::firstOrCreate([
            'name' => $strtolowerRole,
            'guard_name' => 'web'
        ]);
        $user->assignRole($role);

        $permission = Permission::firstOrCreate([
            'name' => 'role-list',
            'guard_name' => 'web'
        ]);

        $role->givePermissionTo($permission);
        $encryptedId = Crypt::encryptString($user->id);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'user_role'=>$request->role,
            'encryptedId' => $encryptedId
        ], 201);
    }

        
    
    public function edit($id){
        $decrypt_id = Crypt::decryptString($id);
        $user = User::find($decrypt_id);
        if(!$user){
            return response()->json([
               'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'message' => 'User details',
            'user' => $user,
            'encrypt_id'=>$id
        ]);
    }



    public function update($id, Request $request){

        $request->validate([
            'type_id' =>'sometimes|nullable|string|max:255',
            'dept_id' =>'sometimes|nullable|string|max:255',
            'name' =>'required|string|max:255',
            'email' =>'required|string|email|max:255',
            'role' =>'required|string',
            'password' => 'nullable|string|min:8'
        ]);

        try {
            // Decrypt the type_id and dept_id
            $decrypt_type_id = $request->type_id ? Crypt::decryptString($request->type_id) : null;
            $decrypt_dept_id = $request->dept_id ? Crypt::decryptString($request->dept_id) : null;
            $strtolowerRole = $request->role ? strtolower($request->role) : null;
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid encrypted data'], 400);
        }


        $decrypt_id = Crypt::decryptString($id);
        $user = User::find($decrypt_id);
        if(!$user){
            return response()->json([
               'message' => 'User not found'
            ], 404);
        }
        
        $user->type_id = $decrypt_type_id;
        $user->dept_id = $decrypt_dept_id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $strtolowerRole;
        
        if($request->has('password')){
            $user->password = bcrypt($request->password);
        }
        
        $user->save();
        return response()->json([
           'message' => 'User updated successfully',
            'user' => $user,
            'encryptedId'=>$id
        ]);
        
    }


    public function destroy($id){
        $decrypt_id = Crypt::decryptString($id);
        $user = User::find($decrypt_id);
        if(!$user){
            return response()->json([
               'message' => 'User not found'
            ], 404);
        }
        $user->delete();
        return response()->json([
           'message' => 'User deleted successfully'
        ]);

    }



    
    public function adminDashboard()
    {
        return response()->json([
            'message' => 'Welcome to the Admin Dashboard',
            'user' => Auth::user()
        ]);
    }


    public function managerDashboard()
    {
        return response()->json([
            'message' => 'Welcome to the Manager Dashboard',
            'user' => Auth::user()
        ]);
    }

 
    public function userDashboard()
    {
        return response()->json([
            'message' => 'Welcome to the User Dashboard',
            'user' => Auth::user()
        ]);
    }
}
