<?php

namespace MDM23\Projdoc\Laravel;

use function MDM23\Projdoc\join_paths;
use Illuminate\Config\Repository as Config;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\Factory as ViewFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;
use File;
use ParsedownExtra as Parsedown;

class Controller extends BaseController
{
    private $config;

    private $filesystem;

    private $parsedown;

    private $viewFactory;

    public function __construct(
        Config      $config,
        Filesystem  $filesystem,
        Parsedown   $parsedown,
        ViewFactory $viewFactory
    ) {
        $this->config      = $config;
        $this->filesystem  = $filesystem;
        $this->parsedown   = $parsedown;
        $this->viewFactory = $viewFactory;
    }

    public function serve($url = "")
    {
        $filePath = join_paths($this->config->get("projdoc.sources"), $url);
        $requestedDirectory = "/" === substr($url, -1);

        if (preg_match("#^/\\\$assets(/.*)$#", $url, $matches)) {
            $assetPath = join_paths(
                realpath(__DIR__ . "/../../resources/assets"),
                $matches[1]
            );

            return $this->serveAsset($assetPath);
        }

        if (!$requestedDirectory and $this->filesystem->isFile($filePath)) {
            return $this->serveAsset($filePath);
        }

        if (!$requestedDirectory and $this->filesystem->exists($filePath . ".md")) {
            return $this->serveMarkdown($filePath . ".md");
        }

        $foundIndexFile = $this->firstExistantFile([
            $filePath . "/index.md",
            $filePath . "/readme.md",
        ]);

        if (!$foundIndexFile) {
            throw new NotFoundHttpException();
        }

        if (!$requestedDirectory) {
            return new RedirectResponse(
                join_paths("/", $this->config->get("projdoc.url"), $url) . "/"
            );
        }

        return $this->serveMarkdown($foundIndexFile);
    }

    private function firstExistantFile($haystack)
    {
        foreach ($haystack as $filePath) {
            if ($this->filesystem->exists($filePath)) {
                return $filePath;
            }
        }

        return false;
    }

    private function serveMarkdown($file)
    {
        $meta = [
            "base"   => "/" . join_paths($this->config->get("projdoc.url")),
            "layout" => "projdoc::default",
        ];

        return $this
            ->viewFactory
            ->make(
                $meta["layout"],
                [
                    "content" => $this->parsedown->text(
                        $this->filesystem->get($file)
                    ),
                    "meta" => $meta
                ]
            );
    }

    private function serveAsset($file)
    {
        if (!$this->filesystem->isFile($file)) {
            throw new NotFoundHttpException();
        }

        $response = new Response($this->filesystem->get($file));
        $response->header("Content-Type", $this->guessMimeType($file));

        return $response;
    }

    private function guessMimeType($filename)
    {
        $mimeTypes = [
            "jp(e?)g" => "image/jpeg",
            "gif"     => "image/gif",
            "png"     => "image/png",
            "svg"     => "image/svg+xml",
            "css"     => "text/css",
            "js"      => "application/javascript",
        ];

        foreach ($mimeTypes as $regex => $mimeType) {
            if (preg_match("/\." . $regex . "\$/i", $filename)) {
                return $mimeType;
            }
        }

        return $this->filesystem->mimeType($filename);
    }
}
