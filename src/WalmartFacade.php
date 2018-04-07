<?php

namespace KeithBrink\Walmart;

use Illuminate\Support\Facades\Facade;

class WalmartFacade extends Facade
{
    protected static function getFacadeAccessor() { 
        return 'walmart';
    }
}