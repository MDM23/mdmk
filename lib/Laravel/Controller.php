<?php

namespace MDM23\Projdoc\Laravel;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function serve($url)
    {
        dump($url);
    }
}
