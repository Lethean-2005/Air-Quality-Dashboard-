<?php

use App\Http\Controllers\Admin\AdminHealthAlertController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Api\AirQualityController;
use App\Http\Controllers\Api\AirQualityDataController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Api\AqiController;
use App\Http\Controllers\Api\AqiOceaniaController;
use App\Http\Controllers\Api\PollutionDataController;
use App\Http\Controllers\Api\WeatherAqiController;
use App\Http\Controllers\FireDataController;
use App\Http\Controllers\Api\ApiAnalyController;
use App\Http\Controllers\Api\AqiCompareController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LocationSearchController;
use App\Http\Controllers\Api\SvgController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public routes and routes protected by auth:sanctum middleware.
|
*/

// Route::get('/admin/city-aqi', [CityAQIAdminController::class, 'index']);
// Public routes
Route::post('/contact', [ContactController::class, 'store']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google OAuth login
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Air quality public data
Route::prefix('air-quality')->group(function () {
    Route::get('/phnom-penh', [AirQualityController::class, 'getPhnomPenhAirQualityOpenWeather']);
});

Route::prefix('admin')->group(function () {
    Route::get('/aqi-meta', [AdminHealthAlertController::class, 'fetchMeta']);
    Route::get('/alert-config', [AdminHealthAlertController::class, 'getAlertConfig']);
    Route::post('/alert-config', [AdminHealthAlertController::class, 'updateAlertConfig']);
    Route::get('/alert-history', [AdminHealthAlertController::class, 'alertHistory']);
    Route::post('/alert-history', [AdminHealthAlertController::class, 'storeAlertHistory']);
    Route::get('/current-aqi', [AdminHealthAlertController::class, 'currentAqi']);
});


// // Additional AQI endpoints
// Route::get('/aqi', [AqiController::class, 'getCityAqi']);
// Route::get('/aqi-global', [AqiController::class, 'global']);
// Route::get('/airquality', [AqiController::class, 'getGlobalAQI']);
// Route::get('/cambodia-aqi', [PollutionDataController::class, 'getCambodiaAqi']);


Route::get('/waqi-city', [AqiCompareController::class, 'getCityPollution']);
Route::get('/global-aqi', [AqiCompareController::class, 'getGlobalAQI']);
Route::get('/country-aqi-info', [AqiCompareController::class, 'getGlobalAQI']);
Route::get('/aqi-global', [AqiCompareController::class, 'global']);
Route::get('/aqi', [PollutionDataController::class, 'getAqiData']);
Route::get('/cities', [PollutionDataController::class, 'getCities']);
Route::get('/aqi-by-country', [PollutionDataController::class, 'getAqiByCountry']);
Route::get('/aqi-history', [\App\Http\Controllers\Api\AqiHistoryController::class, 'index']);
Route::get('/aqi-calendar', [\App\Http\Controllers\Api\AqiCalendarController::class, 'index']);
Route::get('/weather-forecast', [\App\Http\Controllers\Api\WeatherForecastController::class, 'index']);
// Smart Health Alert: Location -> AQI + Weather -> Decision Engine -> Smart Message
Route::get('/health-alert/decision', [\App\Http\Controllers\Api\HealthAlertDecisionController::class, 'decide']);
Route::post('/health-alert/telegram-send', [\App\Http\Controllers\Api\HealthAlertDecisionController::class, 'sendTelegram']);
Route::get('/health-alert/telegram-updates', [\App\Http\Controllers\Api\HealthAlertDecisionController::class, 'telegramUpdates']);
// Telegram calls this directly once registered via setWebhook (see the controller docblock) — public, no auth.
Route::post('/telegram/webhook', [\App\Http\Controllers\Api\HealthAlertDecisionController::class, 'telegramWebhook']);
// Analy Page
Route::get('/aqi-data', [ApiAnalyController::class, 'fetchFilteredData']);

Route::get('/news', [NewsController::class, 'index']); // public for user site
Route::get('/search-locations', [LocationSearchController::class, 'search']); // public location search

// Country background SVGs — served from local cache only (populate via `php artisan svg:cache {country}`)
Route::get('/svg', [SvgController::class, 'index']);
Route::get('/svg/{country}', [SvgController::class, 'show']);

// Country polygons for the world AQI choropleth. Served through the API (not /storage)
// because static files under /storage bypass Laravel middleware and get no CORS headers,
// so a browser on another origin (the Vue dev server) cannot fetch them via axios.
Route::get('/world-geojson', function () {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $path = 'geojson/custom.geo.json';

    if (!$disk->exists($path)) {
        return response()->json(['status' => 'error', 'message' => 'World GeoJSON not found in storage.'], 404);
    }

    return response($disk->get($path), 200)
        ->header('Content-Type', 'application/json')
        ->header('Cache-Control', 'public, max-age=3600');
});

// Routes protected by authentication
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);



    //favourite routes
    Route::get('/favourites', [FavouriteController::class, 'index']); // list favourites
    Route::post('/favourites', [FavouriteController::class, 'store']); // add favourite
    Route::delete('/favourites/{city_name}', [FavouriteController::class, 'destroy']); // remove favourite


    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);              // List users
        Route::post('/', [UserController::class, 'store']);             // Create user
        Route::get('/{id}', [UserController::class, 'show']);           // Show user
        Route::put('/{id}', [UserController::class, 'update']);         // Update user
        Route::delete('/{id}', [UserController::class, 'destroy']);     // Delete user
    });

    // Authenticated user's own profile routes
    Route::get('/me', [UserController::class, 'profile']);
    Route::put('/me', [UserController::class, 'updateProfile']);
    Route::delete('/me/profile-image', [UserController::class, 'removeProfileImage']);
    Route::get('/me/role', [UserController::class, 'role']);

    // Profile routes (can consider merging with /me routes if preferred)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/image', [ProfileController::class, 'uploadImage']);
    Route::post('/profile/update', [UserController::class, 'updateProfile']); // Kept as is (possibly duplicate with /me PUT)


    // Admin-only routes inside auth middleware
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        // Add other admin routes here if any
    });

    //news routes
    Route::post('/news', [NewsController::class, 'store']);
    Route::patch('/news/{news}', [NewsController::class, 'update']);
    Route::delete('/news/{news}', [NewsController::class, 'destroy']);
});

Route::get('/categories', [CategoryController::class, 'index']);           // list
Route::post('/categories/create', [CategoryController::class, 'store']);   // create
Route::get('/categories/{id}', [CategoryController::class, 'show']);       // show one
Route::put('/categories/{category}/update', [CategoryController::class, 'update']); // update
Route::delete('/categories/{category}/delete', [CategoryController::class, 'destroy']); // delete
