<?php

use Benjacho\BelongsToManyField\Http\Controllers\ResourceController;

Route::get('/{resource}/options/{relationship}/{optionsLabel}/{dependsOnValue?}/{dependsOnKey?}', [ResourceController::class, 'index']);
Route::get('/field/{resource}/options/{relationship}/{optionsLabel}/{dependsOnField}/{dependsOnValue}', [ResourceController::class, 'dependsOnField']);
