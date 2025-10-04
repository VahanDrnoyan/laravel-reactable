<?php

namespace TrueFans\LaravelReactable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TrueFans\LaravelReactable\LaravelReactable
 */
class LaravelReactable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TrueFans\LaravelReactable\LaravelReactable::class;
    }
}
