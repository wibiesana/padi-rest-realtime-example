<?php

use Wibiesana\Padi\Core\Router;

$router = new Router();

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here you can register API routes for your application. These routes are
| loaded by the Router class and automatically support middleware,
| automatic response formatting, and exception handling.
|
*/

// ============================================================================
// SITE & HEALTH CHECK ROUTES
// ============================================================================
// Public routes for site information and health monitoring

$router->get('/', 'SiteController@index');
$router->get('/health', 'SiteController@health');

// Site information routes
$router->group(['prefix' => 'site'], function ($router) {
    $router->get('/info', 'SiteController@info');
    $router->get('/endpoints', 'SiteController@endpoints');
});

// ============================================================================
// AUTHENTICATION ROUTES (PUBLIC)
// ============================================================================
// User registration, login, password reset, and token management

$router->group(['prefix' => 'auth'], function ($router) {
    // User registration & login (rate limited)
    $router->post('/register', 'AuthController@register')->middleware('RateLimitMiddleware');
    $router->post('/login', 'AuthController@login')->middleware('RateLimitMiddleware');

    // Token management
    $router->post('/refresh', 'AuthController@refresh');
    $router->post('/logout', 'AuthController@logout');

    // Password recovery (rate limited)
    $router->post('/forgot-password', 'PasswordResetController@forgotPassword')->middleware('RateLimitMiddleware');
    $router->post('/reset-password', 'PasswordResetController@passwordReset')->middleware('RateLimitMiddleware');

    // Get current user info (protected)
    $router->get('/me', 'AuthController@me')->middleware('AuthMiddleware');
});

// ============================================================================
// USERS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for users - requires authentication
$router->group(['prefix' => 'users', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->get('/', 'UserController@index');           // List users with pagination
    $router->get('/all', 'UserController@all');         // Get all users
    $router->get('/{id}', 'UserController@show');
    $router->post('/', 'UserController@store');         // Create new user
    $router->put('/{id}', 'UserController@update');     // Update user
    $router->delete('/{id}', 'UserController@destroy'); // Delete user
});


// ============================================================================
// DEMO & EXAMPLE ROUTES (PROTECTED)
// ============================================================================
// Examples showcasing Role-Based Access Control and Real-time SSE broadcasting.
// NOTE: You can safely delete this entire group block and its controllers if not needed.

$router->group(['prefix' => 'examples', 'middleware' => ['AuthMiddleware']], function ($router) {
    // RBAC Examples
    $router->get('/rbac/stats', 'ExampleRBACController@getStats');
    $router->get('/rbac/users', 'ExampleRBACController@listUsers');
    $router->get('/rbac/my-profile', 'ExampleRBACController@getMyProfile');
    $router->put('/rbac/my-profile', 'ExampleRBACController@updateMyProfile');
    $router->post('/rbac/students', 'ExampleRBACController@createStudent');

    // Real-time SSE Examples
    $router->post('/realtime/chat', 'ExampleRealtimeController@broadcastChatMessage');
    $router->post('/realtime/notify', 'ExampleRealtimeController@sendPrivateNotification');
    $router->post('/realtime/alert', 'ExampleRealtimeController@sendSystemAlert');
    $router->post('/realtime/token', 'ExampleRealtimeController@getCustomSubscribeToken');
});


// ============================================================================
// CHATS ROUTES
// ============================================================================
// Protected operations for chats - requires authentication
$router->group(['prefix' => 'chats', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'ChatController@index');           // List chats with pagination
    $router->get('/all', 'ChatController@all');         // Get all chats
    $router->get('/{id}', 'ChatController@show');       // Get specific item
    $router->post('/', 'ChatController@store');         // Create new item
    $router->put('/{id}', 'ChatController@update');     // Update item
    $router->delete('/{id}', 'ChatController@destroy'); // Delete item
});



// ============================================================================
// COMMENTS ROUTES
// ============================================================================
// Protected operations for comments - requires authentication
$router->group(['prefix' => 'comments', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'CommentController@index');           // List comments with pagination
    $router->get('/all', 'CommentController@all');         // Get all comments
    $router->get('/{id}', 'CommentController@show');       // Get specific item
    $router->post('/', 'CommentController@store');         // Create new item
    $router->put('/{id}', 'CommentController@update');     // Update item
    $router->delete('/{id}', 'CommentController@destroy'); // Delete item
});



// ============================================================================
// JOBS ROUTES
// ============================================================================
// Protected operations for jobs - requires authentication
$router->group(['prefix' => 'jobs', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'JobController@index');           // List jobs with pagination
    $router->get('/all', 'JobController@all');         // Get all jobs
    $router->get('/{id}', 'JobController@show');       // Get specific item
    $router->post('/', 'JobController@store');         // Create new item
    $router->put('/{id}', 'JobController@update');     // Update item
    $router->delete('/{id}', 'JobController@destroy'); // Delete item
});



// ============================================================================
// POST TAGS ROUTES
// ============================================================================
// Protected operations for post-tags - requires authentication
$router->group(['prefix' => 'post-tags', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'PostTagController@index');           // List post-tags with pagination
    $router->get('/all', 'PostTagController@all');         // Get all post-tags
    $router->get('/{id}', 'PostTagController@show');       // Get specific item
    $router->post('/', 'PostTagController@store');         // Create new item
    $router->put('/{id}', 'PostTagController@update');     // Update item
    $router->delete('/{id}', 'PostTagController@destroy'); // Delete item
});



// ============================================================================
// POSTS ROUTES
// ============================================================================
// Protected operations for posts - requires authentication
$router->group(['prefix' => 'posts', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'PostController@index');           // List posts with pagination
    $router->get('/all', 'PostController@all');         // Get all posts
    $router->get('/{id}', 'PostController@show');       // Get specific item
    $router->post('/', 'PostController@store');         // Create new item
    $router->put('/{id}', 'PostController@update');     // Update item
    $router->delete('/{id}', 'PostController@destroy'); // Delete item
});



// ============================================================================
// TAGS ROUTES
// ============================================================================
// Protected operations for tags - requires authentication
$router->group(['prefix' => 'tags', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'TagController@index');           // List tags with pagination
    $router->get('/all', 'TagController@all');         // Get all tags
    $router->get('/{id}', 'TagController@show');       // Get specific item
    $router->post('/', 'TagController@store');         // Create new item
    $router->put('/{id}', 'TagController@update');     // Update item
    $router->delete('/{id}', 'TagController@destroy'); // Delete item
});


return $router;
