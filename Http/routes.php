<?php

/*
|--------------------------------------------------------------------------
| Language Settings
|--------------------------------------------------------------------------
*/
$lang = null;

if (App::environment() == 'testing') {
    $lang = 'en';
}

LaravelLocalization::setLocale($lang);
