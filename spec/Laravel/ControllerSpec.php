<?php

namespace spec\MDM23\Projdoc\Laravel;

use function MDM23\Projdoc\join_paths;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\View;
use ParsedownExtra as Parsedown;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

class ControllerSpec extends ObjectBehavior
{
    private $config;
    private $filesystem;
    private $parsedown;
    private $viewFactory;
    private $view;
    private $fileCache = [];

    function let(
        Config      $config,
        Filesystem  $filesystem,
        Parsedown   $parsedown,
        ViewFactory $viewFactory,
        View        $view
    ) {
        $this->beConstructedWith(
            $this->config      = $config,
            $this->filesystem  = $filesystem,
            $this->parsedown   = $parsedown,
            $this->viewFactory = $viewFactory
        );

        $this->view = $view;

        $this->parsedown->text(Argument::type("string"))->will(function ($args) {
            return sprintf("markdown(%s)", $args[0]);
        });

        $this->config->get("projdoc.url")->willReturn("/doc/");
        $this->config->get("projdoc.sources")->willReturn("/some-folder/src/");

        $self = $this;

        $this->filesystem->exists(Argument::type("string"))->will(function ($args) use ($self) {
            return array_key_exists($args[0], $self->fileCache);
        });

        $this->filesystem->isFile(Argument::type("string"))->will(function ($args) use ($self) {
            return array_key_exists($args[0], $self->fileCache);
        });

        $this->filesystem->get(Argument::type("string"))->will(function ($args) use ($self) {
            return $self->fileCache[$args[0]];
        });

        $this
            ->viewFactory
            ->make(
                Argument::type("string"),
                Argument::type("array")
            )
            ->will(function ($args) use ($view) {
                $view->getName()->willReturn($args[0]);
                $view->getData()->willReturn($args[1]);
                return $view;
            });
    }

    function it_is_a_laravel_controller()
    {
        $this->shouldHaveType(
            \Illuminate\Routing\Controller::class
        );
    }

    function it_serves_markdown_files_with_the_default_layout()
    {
        $this->hasFile("foo/bar.md", "foobar");

        $result = $this->serve("/foo/bar");
        $result->shouldBe($this->view);

        $result->getName()->shouldEqual("projdoc::default");
        $result->getData()->shouldHaveInArray("content", "markdown(foobar)");
    }

    function it_prefers_real_files_over_sub_folders()
    {
        $this->hasFile("sub/index.md", "sub_index_content");
        $this->hasFile("sub.md", "file_content");

        $result = $this->serve("/sub");
        $result->shouldBe($this->view);

        $result->getData()->shouldHaveInArray("content", "markdown(file_content)");
    }

    function it_serves_index_files_when_url_ends_with_slash()
    {
        $this->hasFile("foobar/index.md", "foobar_index_content");
        $this->hasFile("foobar.md", "foobar_content");

        $result = $this->serve("/foobar/");
        $result->shouldBe($this->view);

        $result->getData()->shouldHaveInArray("content", "markdown(foobar_index_content)");
    }

    function it_serves_the_root_index_file()
    {
        $this->hasFile("index.md", "root_index");

        $result = $this->serve("/");
        $result->shouldBe($this->view);

        $result->getData()->shouldHaveInArray("content", "markdown(root_index)");
    }

    function it_redirects_to_a_folder_when_there_is_an_index_file()
    {
        $this->hasFile("child/index.md", "child_index");

        $result = $this->serve("/child");
        $result->shouldBeAnInstanceOf(RedirectResponse::class);
        $result->getTargetUrl()->shouldEqual("/doc/child/");
    }

    function it_serves_vendor_assets()
    {
        $this->hasVendorAsset("foo-theme.css");

        $result = $this->serve("/\$assets/foo-theme.css");
        $result->shouldBeAnInstanceOf(Response::class);
        $responseMime = $result->getWrappedObject()->headers->get("Content-Type");

        if ("text/css" !== $responseMime) {
            throw new FailureException(sprintf(
                "Expected response to have mime type text/css. Got: %s",
                $responseMime
            ));
        }
    }

    function it_serves_project_assets()
    {
        $this->hasFile("images/graphic.svg", "");

        $result = $this->serve("/images/graphic.svg");
        $result->shouldBeAnInstanceOf(Response::class);
        $responseMime = $result->getWrappedObject()->headers->get("Content-Type");

        if ("image/svg+xml" !== $responseMime) {
            throw new FailureException(sprintf(
                "Expected response to have mime type image/svg+xml. Got: %s",
                $responseMime
            ));
        }
    }

    public function getMatchers(): array
    {
        return [
            "haveInArray" => function ($subject, $path, $value) {
                if (!is_array($subject)) {
                    throw new FailureException(sprintf(
                        "Expected subject to be an array but got %s",
                        gettype($subject)
                    ));
                }

                $realValue = Arr::get($subject, $path);

                if (!is_scalar($realValue) or !is_scalar($value)) {
                    throw new RuntimeException(
                        "Comparison of non-scalar values is currently unsupported!"
                    );
                }

                if ($value !== $realValue) {
                    throw new FailureException(sprintf(
                        "subject.%s should be %s - got %s",
                        $path,
                        var_export($value, true),
                        var_export($realValue, true)
                    ));
                }

                return true;
            },
        ];
    }

    private function hasFile($path, $content)
    {
        $realPath = join_paths("/some-folder/src/", $path);
        $this->fileCache[$realPath] = $content;
    }

    private function hasVendorAsset($path)
    {
        $realPath = join_paths(
            realpath(__DIR__ . "/../../resources/assets"),
            $path
        );

        $this->fileCache[$realPath] = "asset-stub";
    }
}
