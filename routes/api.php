<?php

use Illuminate\Http\Request;   
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierBranchController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//test route


Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {

    // User Management
        Route::get('/check-permissions', [UserController::class, 'checkPermissions']);
        Route::get('/users', [UserController::class, 'adminDashboard']);
        Route::get('/users/index', [UserController::class, 'index']);
        Route::get('/users/create', [UserController::class, 'create']);
        Route::post('/users/store', [UserController::class, 'store']);  //->middleware('role:Admin')
        Route::get('/users/edit/{id}', [UserController::class, 'edit']);
        Route::put('/users/update/{id}', [UserController::class, 'update']);
        Route::delete('/users/delete/{id}', [UserController::class, 'destroy']);

    
    
    //Type Management
        Route::get('/type',[TypeController::class,'index'])->name('type');
        Route::post('/type/store',[TypeController::class,'store'])->name('type.store')->middleware('role:admin');
        Route::get('/type/{encryptedId}/edit',[TypeController::class,'edit'])->name('type.edit')->middleware('role:admin');
        Route::put('/type/{id}',[TypeController::class,'update'])->name('type.update')->middleware('role:admin');
        Route::delete('/type/{id}',[TypeController::class,'destroy'])->name('type.destroy')->middleware('role:admin');



    //Department
        Route::get('/type/departments/{type_id}',[DepartmentController::class,'showTypeBasedDepartments'])->name('showTypeBasedDepartments');
        Route::get('/department',[DepartmentController::class,'index'])->name('department');
        Route::post('/department/store',[DepartmentController::class,'store'])->name('department.store');
        Route::get('/department/{id}/edit',[DepartmentController::class,'edit'])->name('type.edit');
        Route::put('/department/{id}',[DepartmentController::class,'update'])->name('type.update');
        Route::delete('/department/{id}',[DepartmentController::class,'destroy'])->name('type.destroy');

        

    // Category
        Route::get('/department/categories/{dept_id}',[CategoryController::class,'showDepartmentBasedCategories'])->name('showDepartmentBasedCategories');
        Route::get('/category',[CategoryController::class,'index'])->name('category');
        Route::post('/category/store',[CategoryController::class,'store'])->name('category.store');
        Route::get('/category/{id}/edit',[CategoryController::class,'edit'])->name('category.edit');
        Route::put('/category/{id}',[CategoryController::class,'update'])->name('category.update');
        Route::delete('/category/{id}',[CategoryController::class,'destroy'])->name('category.destroy');




    // Product
        Route::get('/category/products/{category_id}',[ProductController::class,'showCategoryBasedProducts'])->name('showCategoryBasedProducts');
        Route::get('/product',[ProductController::class,'index'])->name('product');
        Route::post('/product/store',[ProductController::class,'store'])->name('product.store')->middleware('role:admin');
        Route::get('/product/{id}/edit',[ProductController::class,'edit'])->name('product.edit');
        Route::put('/product/{id}',[ProductController::class,'update'])->name('product.update');
        Route::delete('/product/{id}',[ProductController::class,'destroy'])->name('product.destroy');
        Route::post('/product/calculate-total', [ProductController::class, 'calculateTotal']);




    //suplier
        Route::get('/product/suppliers/{prct_id}',[SupplierController::class,'showProductBasedSuppliers'])->name('showProductBasedSuppliers');
        Route::get('/supplier',[SupplierController::class,'index'])->name('supplier');
        Route::post('/supplier/store',[SupplierController::class,'store'])->name('supplier.store');
        Route::get('/supplier/{id}/edit',[SupplierController::class,'edit'])->name('supplier.edit');
        Route::put('/supplier/{id}',[SupplierController::class,'update'])->name('supplier.update');
        Route::delete('/supplier/{id}',[SupplierController::class,'destroy'])->name('supplier.destroy');
        Route::get('/supplier/branch-details/{id}',[SupplierController::class,'get_branch_details'])->name('supplier.branch-details');    // supplier id based supplier details and branch details




    // SupplierBranch
        Route::get('/supplier/supplier-branches/{supplier_id}',[SupplierBranchController::class,'showSupplierBasedSupplierBranches'])->name('showProductBasedSupplierses');
        Route::get('/supplier-branch',[SupplierBranchController::class,'index'])->name('supplier-branch');
        Route::post('/supplier-branch/store',[SupplierBranchController::class,'store'])->name('supplier-branch.store');
        Route::get('/supplier-branch/{id}/edit',[SupplierBranchController::class,'edit'])->name('supplier-branch.edit');
        Route::put('/supplier-branch/{id}',[SupplierBranchController::class,'update'])->name('supplier-branch.update');
        Route::delete('/supplier-branch/{id}',[SupplierBranchController::class,'destroy'])->name('supplier-branch.destroy');
       

       
    //Role    
        Route::resource('roles', RoleController::class);
        Route::get('/get-roles',[RoleController::class, 'getAvailableRoles']);
       


    //Permission    
        Route::get('/permissions',[RoleController::class, 'getAvailablePermissions']);
        Route::get('/get-grouped-permissions',[RoleController::class, 'getGroupedPermissions']);
        Route::get('/assign-permission',[RoleController::class, 'assignPermissions']);
        Route::post('/permissions', [RoleController::class, 'createPermission']);
        Route::put('/permissions/{id}', [RoleController::class, 'updatePermission']);
        Route::delete('/permissions/{id}', [RoleController::class, 'deletePermission']);


    //Test    
        Route::get('/name',[UserController::class,'test'])->name('type');

});












