<?php

namespace MDM23\Projdoc\Laravel;

use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\Yaml\Yaml;
use Parsedown;

class Controller extends BaseController
{
    private $parsedown;

    public function __construct(Parsedown $parsedown)
    {
        $this->parsedown = $parsedown;
    }

    public function serve($url = "/")
    {
        $rawMarkdown = $content = $this->getContent($url);

        $meta = [
            "base"   => "/" . ltrim(config("projdoc.url"), "/"),
            "layout" => "projdoc::default",
        ];

        if (preg_match("/^---\n(.*)---\n+(.*)/s", $rawMarkdown, $matches)) {
            $content = $matches[2];
            $meta    = array_merge($meta, Yaml::parse($matches[1]) ?? []);
        }

        return view(
            $meta["layout"],
            [
                "content" => $this->parsedown->text($content),
                "meta"    => $meta,
            ]
        );
    }

    private function getContent($url)
    {
        $dir = rtrim(config("projdoc.sources"), "/") . $url;

        $locations = [
            $dir . ".md",
            $dir . "/index.md",
            $dir . "/readme.md",
        ];

        foreach ($locations as $location) {
            if (file_exists($location)) {
                return file_get_contents($location);
            }
        }

        abort(404);
    }
}
