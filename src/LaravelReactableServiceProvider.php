<?php

namespace TrueFans\LaravelReactable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TrueFans\LaravelReactable\Commands\LaravelReactableCommand;

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
            ->hasMigration('create_laravel_reactable_table')
            ->hasCommand(LaravelReactableCommand::class);
    }
}
