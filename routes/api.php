<?php
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResturantController;
use App\Http\Controllers\airportController;
use App\Http\Controllers\landmarkController;
use App\http\Controllers\TripController;
use App\Http\Controllers\hotelController;
use App\Http\Controllers\shoping_centerController;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::group(['Middleware'=>'auth:api','prefix'=>'auth'],function($router){
    Route::post('/register',[UserController::class,'register']);
    Route::post('/login',[UserController::class,'login']);
    Route::get('/profile',[UserController::class,'profile']);
    Route::post('/logout',[UserController::class,'logout']);
    Route::put('/updatePassword',[UserController::class,'updatePassword']);
    Route::post('uploadProfileImage',action: [UserController::class,'uploadProfileImage']);
});

Route::group(['prefix'=> 'stories'],routes:function($router){
    Route::post('add_story',[StoryController::class, 'add_story']);
    Route::get('get_stories/{id}', [StoryController::class, 'show']);
    Route::get('get_all_stories', [StoryController::class, 'get_All_Stories']);
    Route::post('update_story/{id}', [StoryController::class, 'update_story']);
    Route::delete('delete_story/{id}', [StoryController::class, 'delete_story']);

});

Route::group(['prefix'=> 'password'],routes:function($router){
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode']);
    Route::post('/verify-reset-code', [ForgotPasswordController::class, 'verifyResetCode']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
    });

Route::group(['prefix'=> 'resturant'],routes:function($router){
    Route::post('store_new_item',[ResturantController::class, 'store_new_item']);
    Route::get('show_specific_item/{id}', [ResturantController::class, 'show_specific_item']);
    Route::get('Display_all_item', [ResturantController::class, 'Display_all_item']);
    Route::put('update_an_item/{id}', [ResturantController::class, 'update_an_item']);
    Route::delete('Delete_an_item/{id}', [ResturantController::class, 'Delete_an_item']);

});
   
Route::group(['prefix'=> 'Landmark'],routes:function($router){
    Route::post('store_new_item',[landmarkController::class, 'store_new_item']);
    Route::get('show_specific_item/{id}', [landmarkController::class, 'show_specific_item']);
    Route::get('Display_all_item', [landmarkController::class, 'Display_all_item']);
    Route::put('update_an_item/{id}', [landmarkController::class, 'update_an_item']);
    Route::delete('Delete_an_item/{id}', [landmarkController::class, 'Delete_an_item']);

});

Route::group(['prefix'=> 'airport'],routes:function($router){
    Route::post('store_new_item',[airportController::class, 'store_new_item']);
    Route::get('show_specific_item/{id}', [airportController::class, 'show_specific_item']);
    Route::get('Display_all_item', [airportController::class, 'Display_all_item']);
    Route::put('update_an_item/{id}', [airportController::class, 'update_an_item']);
    Route::delete('Delete_an_item/{id}', [airportController::class, 'Delete_an_item']);

});

Route::group(['prefix'=> 'hotel'],routes:function($router){
    Route::post('store_new_item',[hotelController::class, 'store_new_item']);
    Route::get('show_specific_item/{id}', [hotelController::class, 'show_specific_item']);
    Route::get('Display_all_item', [hotelController::class, 'Display_all_item']);
    Route::put('update_an_item/{id}', [hotelController::class, 'update_an_item']);
    Route::delete('Delete_an_item/{id}', [hotelController::class, 'Delete_an_item']);

});

Route::group(['prefix'=> 'shoping_center'],routes:function($router){
    Route::post('store_new_item',[shoping_centerController::class, 'store_new_item']);
    Route::get('show_specific_item/{id}', [shoping_centerController::class, 'show_specific_item']);
    Route::get('Display_all_item', [shoping_centerController::class, 'Display_all_item']);
    Route::put('update_an_item/{id}', [shoping_centerController::class, 'update_an_item']);
    Route::delete('Delete_an_item/{id}', [shoping_centerController::class, 'Delete_an_item']);

});

Route::group(['prefix'=> 'Review'],routes:function($router){
    Route::post('store_new_review',[ReviewController::class, 'store_new_review']);
    Route::get('show_specific_Review/{id}', [ReviewController::class, 'show_specific_Review']);
    Route::get('Display_all_Review', [ReviewController::class, 'Display_all_Review']);
    Route::put('update_an_Review/{id}', [ReviewController::class, 'update_an_Review']);
    Route::delete('Delete_an_Review/{id}', [ReviewController::class, 'Delete_an_Review']);


    Route::get('topRatedItems', [ReviewController::class, 'topRatedItems']);

});

Route::group(['prefix'=> 'Trip'],routes:function($router){
    Route::post('store_new_trip',[TripController::class, 'store_new_trip']);
    Route::get('show_specific_trip/{id}', [TripController::class, 'show_specific_trip']);
    Route::get('Display_all_trip', [TripController::class, 'Display_all_trip']);
    Route::put('update_an_trip/{id}', [TripController::class, 'update_an_trip']);
    Route::delete('Delete_an_trip/{id}', [TripController::class, 'Delete_an_trip']);

});


Route::get('/db-test', function () {
    try {
        return response()->json([
            'database' => DB::connection()->getDatabaseName(),
            'stories_count' => DB::table('stories')->count(),
            'users_count' => DB::table('users')->count(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Database connection failed',
        ], 500);
    }
});
