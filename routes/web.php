<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProduitCommandeController;
use App\Models\Produit;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [HomeController::class, 'home'])->name("site");
Route::get('/getProduits',function(){
    return Produit::select('id','nom', 'prix', 'description')->get();
});

Route::group(['middleware' => 'auth'], function () {

    // Route accessible à tous les utilisateurs authentifiés
    Route::get('/logout', [SessionsController::class, 'destroy']);

    // Routes spécifiques aux admins
    Route::group(['middleware' => 'admin'], function () {

        Route::get('dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::get('profile', function () {
            return view('profile');
        })->name('profile');

        Route::resource('user-management', UserManagementController::class);
        Route::resource('produits', ProduitController::class);
        Route::resource('commandes', CommandeController::class);
        Route::resource('restaurants', RestaurantController::class);


        

        Route::get('/user-profile', [InfoUserController::class, 'create']);
        Route::post('/user-profile', [InfoUserController::class, 'store']);

        Route::get('/login', function () {
            return view('dashboard');
        })->name('sign-up');
    });
	Route::get('/chat', function () {
		return view('chat');
	});
});



Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionsController::class, 'create']);
    Route::post('/session', [SessionsController::class, 'store']);
	Route::get('/login/forgot-password', [ResetController::class, 'create']);
	Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
	Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
	Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');

});

Route::get('/login', function () {
    return view('session/login-session');
})->name('login');

