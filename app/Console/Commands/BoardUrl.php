<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:board-url {--unit= : Show only this unit code, e.g. UGT}')]
#[Description('Print the link a wall TV uses to open the display board without signing in')]
class BoardUrl extends Command
{
    public function handle(): int
    {
        $token = (string) config('board.token');

        if ($token === '') {
            $this->error('BOARD_TOKEN is not set, so the board needs a login. Set it in .env to get a TV link.');

            return self::FAILURE;
        }

        $this->line(route('board', array_filter([
            'unit' => $this->option('unit'),
            'token' => $token,
        ])));

        return self::SUCCESS;
    }
}
