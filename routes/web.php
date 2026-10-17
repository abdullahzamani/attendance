<?php

use App\Http\Middleware\AuthorizeBoard;
use App\Livewire\Board;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Signed-in users, or a wall TV holding the board token (see config/board.php).
Route::get('/board', Board::class)->middleware(AuthorizeBoard::class)->name('board');
