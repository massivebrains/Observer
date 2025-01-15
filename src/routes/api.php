<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::any('/', fn () => response()->json([]));

Route::group(['prefix' => 'policies'], function () {
    Route::post('/', \App\Http\Controllers\Policies\StoreController::class);
    Route::delete('/', \App\Http\Controllers\Policies\DeleteController::class);
    Route::get('/summary', \App\Http\Controllers\Policies\SummaryController::class);
});

Route::group(['prefix' => 'incidents'], function () {
    Route::get('/', \App\Http\Controllers\Incidents\GetController::class);
    Route::get('/{incident}/failed-subjects', \App\Http\Controllers\Incidents\FailedSubjectsController::class);
});
