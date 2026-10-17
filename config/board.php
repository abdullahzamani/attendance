<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Display board access token
    |--------------------------------------------------------------------------
    |
    | Lets a wall TV open the read-only display board without signing in, at
    | /board?token=<this value>. Leave empty to require a login for the board.
    | Print the full link with `php artisan app:board-url`.
    |
    */

    'token' => env('BOARD_TOKEN'),

];
