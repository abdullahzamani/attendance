<?php

use App\Livewire\Board;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/board', Board::class)->middleware('auth')->name('board');
