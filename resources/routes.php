<?php

use function MDM23\Projdoc\join_paths;
use Illuminate\Http\Request;

// HACK: This ist just a temporary solution. We can't use the controller
//       directly, because Laravel does not include the trailing slash in the
//       route parameter. There must be a cleaner way to do this, without
//       dealing with the raw Request instance!
Route::get(
    join_paths(config("projdoc.url")) . "{url?}",
    function (Request $request, $url = "") {
        if ("/" === substr($request->getUri(), -1)) {
            $url .= "/";
        }

        return app(MDM23\Projdoc\Laravel\Controller::class)->serve($url);
    }
)
->where([ "url" => "\/.*" ]);
