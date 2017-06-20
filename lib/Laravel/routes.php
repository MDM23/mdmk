<?php

Route::get("projdoc{url}", \MDM23\Projdoc\Laravel\Controller::class . "@serve")->where([
    "url" => "^/.*$|^$",
]);
