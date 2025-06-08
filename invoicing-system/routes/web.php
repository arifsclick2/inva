<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // Assumes a welcome.blade.php will exist
});
