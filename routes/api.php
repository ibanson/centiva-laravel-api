<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\BrokerController;

Route::prefix('api')->group(function () {

    // Retourner toutes les équipes et leurs courtiers
    Route::get('/teams', [TeamController::class, 'index']);

    // Retourner une équipe en particulier avec ses courtiers
    Route::get('/teams/{team}', [TeamController::class, 'show']);

     // Retourner toutes les courtiers et l'équipe à laquelle ils appartiennent
    Route::get('/brokers', [BrokerController::class, 'index']);

    // Retourner un courtier et l'équipe à laquelle il appartient
    Route::get('/brokers/{broker}', [BrokerController::class, 'show']);

    // Soft deleter une équipe (et ses courtiers associés)
    Route::delete('/teams/{team}', [TeamController::class, 'destroy']);

    // Ajouter un courtier à une équipe existante
    Route::post('/teams/{team}/brokers', [BrokerController::class, 'store']);

});
