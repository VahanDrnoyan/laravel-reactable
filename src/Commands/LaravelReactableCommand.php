<?php

namespace TrueFans\LaravelReactable\Commands;

use Illuminate\Console\Command;

class LaravelReactableCommand extends Command
{
    public $signature = 'laravel-reactable';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
