<?php

namespace MDM23\Projdoc;

function join_paths()
{
    $isAbsolute = func_get_args()[0][0] === "/";

    $joined = implode(
        "/",
        array_filter(
            array_map(
                function ($slice) {
                    return trim($slice, "/");
                },
                func_get_args()
            )
        )
    );

    if ($isAbsolute) {
        $joined = "/" . $joined;
    }

    return $joined;
}
