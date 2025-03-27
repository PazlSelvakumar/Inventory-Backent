<?php
    
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;

   
class RoleController extends BaseController
{
    // public function __construct()
    // {
    //     // Middleware to check specific permissions for each method
    //     $this->middleware('auth');
    //     $this->middleware('permission:role-create')->only('store');
    //     $this->middleware('permission:role-edit')->only('update');
    //     $this->middleware('permission:role-delete')->only('destroy');
    //     $this->middleware('permission:role-list')->only('index', 'show');
    //     $this->middleware('permission:create-permission')->only('store');
    //     $this->middleware('permission:edit-permission')->only('update');
    //     $this->middleware('permission:delete-permission')->only('destroy');
    //     $this->middleware('permission:view-permission')->only('index', 'show');
    // }
    
    //---------------------------------------------   Permission    -------------------------------------------//

    //Create new Permission
    public function createPermission(Request $request)
    {

        $request->validate([
            'name' => 'required|unique:permissions,name|max:255',
            'guard_name' => 'sometimes|nullable|string|max:255',
        ]);
        try {
            $permission = Permission::create([
                'name' => $request->input('name'),
                'guard_name' => $request->input('guard_name', 'web'), // Default to 'web' if not specified
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Permission created successfully',
                'permission' => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    //update permission
    public function updatePermission(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name|max:255',
            'guard_name' => 'sometimes|nullable|string|max:255',
        ]);
        try {
            $permission = Permission::findOrFail($id);

            if ($request->has('name')) {
                $permission->name = $request->input('name');
            }

            if ($request->has('guard_name')) {
                $permission->guard_name = $request->input('guard_name');
            }
            $permission->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Permission updated successfully',
                'permission' => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Delete Permission
    public function deletePermission($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $assignedRoles = Role::whereHas('permissions', function ($query) use ($id) {
                $query->where('permissions.id', $id);
            })->count();
            if ($assignedRoles > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete permission. It is assigned to one or more roles.',
                    'assigned_roles_count' => $assignedRoles
                ], 400);
            }
            $permission->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Permission deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Available Permission
    public function getAvailablePermissions()
    {
        $permissions = Permission::all()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name
            ];
        });
        return response()->json([
            'permissions' => $permissions
        ]);
    }   
    
    

    //Groupby available permissions
    public function getGroupedPermissions()
    {
        $permissions = Permission::all()
            ->groupBy(function ($permission) {
                $parts = explode('-', $permission->name);
                return count($parts) > 1 ? $parts[0] : 'general';
            })
            ->map(function ($group) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name
                    ];
                });
            });
        return response()->json([
            'grouped_permissions' => $permissions
        ]);
    }



    // Assigned Permission for roles
    public function assignPermissions(Request $request)
    {
        $guardName = 'web';
        $validatedData = $request->validate([
            'id' => 'sometimes|exists:roles,id',
            'name' => ['required', 'string', 'unique:roles,name' . ($request->has('id') ? ',' . $request->input('id') : '')],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id,guard_name,' . $guardName
        ]);
        if ($request->has('id')) {
            $role = Role::findOrFail($validatedData['id']);
            $role->update([
                'name' => $validatedData['name']
            ]);
        } else {
            $role = Role::create([
                'name' => $validatedData['name'],
                'guard_name' => $guardName
            ]);
        }
        $permissions = Permission::whereIn('id', $request->input('permissions'))
            ->where('guard_name', $guardName)
            ->get();
        $role->syncPermissions($permissions);
        return response()->json([
            'message' => $request->has('id') 
                ? 'Role updated and permissions assigned successfully'
                : 'Role created and permissions assigned successfully',
            'role' => $role,
            'assigned_permissions' => $role->permissions->pluck('name')
        ]);
    }

//---------------------------------------------   Role    -------------------------------------------//

    //get Roles
    public function getAvailableRoles()
    {
        $roles = Role::pluck('name','name')->all();
        return response()->json([
         'roles'=>$roles
        ]);
    }

    //View Role and Role Based Permissions
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'status' => 'success',
            'data' => $roles,
            'total' => $roles->count()
        ], 200);
    }

    //Show
    public function show($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $role
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found'
            ], 404);
        }
    }


    //Store the data in new Role
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|max:255',
            'guard_name' => 'sometimes|nullable|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        try {
            $role = Role::create([
                'name' => $request->input('name'),
                'guard_name' => $request->input('guard_name', 'web')
            ]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->input('permissions'));
            }

            $role->load('permissions');

            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    //Update Role
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['sometimes','required',Rule::unique('roles')->ignore($id),'max:255'], 
            'guard_name' => 'sometimes|nullable|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            $role = Role::findOrFail($id);

            if ($request->has('name')) {
                $role->name = $request->input('name');
            }

            if ($request->has('guard_name')) {
                $role->guard_name = $request->input('guard_name');
            }

            $role->save();

            if ($request->has('permissions')) {
                $role->syncPermissions($request->input('permissions'));
            }

            $role->load('permissions');

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    // Delete Role
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            $assignedUsers = User::role($role)->count();
            if ($assignedUsers > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete role. It is assigned to users.',
                    'assigned_users_count' => $assignedUsers
                ], 400);
            }
            $role->syncPermissions([]);
            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
   
    
}
