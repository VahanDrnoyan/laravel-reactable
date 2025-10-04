<?php

namespace TrueFans\LaravelReactable;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TrueFans\LaravelReactable\Commands\LaravelReactableCommand;
use TrueFans\LaravelReactable\Livewire\Reactions;

class LaravelReactableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-reactable')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_reactions_table')
            ->hasCommand(LaravelReactableCommand::class);
    }

    public function bootingPackage(): void
    {
        // Register Livewire components
        Livewire::component('reactions', Reactions::class);
    }
}
