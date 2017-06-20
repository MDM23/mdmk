<?php

Route::get(
    rtrim(config("projdoc.url"), "/") . "{url?}",
    \MDM23\Projdoc\Laravel\Controller::class . "@serve"
)
->where([ "url" => "/.*" ]);
