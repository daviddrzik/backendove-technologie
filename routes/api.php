<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookApiController;
use App\Http\Controllers\BookRestController;
use App\Http\Controllers\BookRpcController;
use App\Http\Controllers\BookSacController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/hello', function () {
//    //return view('welcome');
//    echo "Hello API!";
//});
//
//Route::get('/test', [TestController::class, 'testAction']);
//
//Route::post('/books/{id}/borrow', [BookRpcController::class, 'borrowBook']); //kvoli csrf tokenu je to tu
//Route::post('/books/{id}/return', [BookRpcController::class, 'returnBook']);
//Route::post('/books/{id}/borrow2', [BookRpcController::class, 'borrowBookService']);
//
//Route::post('/book/{id}', BookSacController::class);
//Route::resource('books', BookRestController::class);
//Route::apiResource('books', BookApiController::class);


Route::apiResource('/notes', NoteController::class)->middleware('auth:sanctum');

Route::get('/notes-with-users', [NoteController::class, 'notesWithUsers']);
Route::get('/users-with-note-count', [NoteController::class, 'usersWithNoteCount']);
Route::get('/search-notes', [NoteController::class, 'searchNotes']);

Route::get('/users-with-notes-count', [NoteController::class, 'usersWithNotesCount']);
Route::get('/longest-and-shortest-note', [NoteController::class, 'longestAndShortestNote']);
Route::get('/notes-last-week', [NoteController::class, 'notesLastWeek']);

Route::apiResource('/categories', CategoryController::class);
Route::get('/search-categories', [CategoryController::class, 'searchCategories']);

// Autentifikácia
Route::prefix('user')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



