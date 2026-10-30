<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\BrowsingContext as Protocol;

/**
 * A tab, a window or a frame. A classic WebDriver window handle is its id.
 */
final class BrowsingContext
{
    public function __construct(private readonly BiDi $bidi, private readonly string $id)
    {
    }

    public static function create(BiDi $bidi, CreateParameters $parameters): self
    {
        return new self($bidi, $bidi->send(Protocol::create($parameters))->context);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * This context and those below it, as far down as $maxDepth.
     *
     * @return list<Info>
     */
    public function getTree(?int $maxDepth = null): array
    {
        return $this->bidi->send(Protocol::getTree(new GetTreeParameters($maxDepth, $this->id)))->contexts;
    }

    /**
     * @return string the PNG image, base64 encoded
     */
    public function captureScreenshot(): string
    {
        return $this->bidi->send(Protocol::captureScreenshot(new CaptureScreenshotParameters($this->id)))->data;
    }

    public function startScreencast(): StartScreencastResult
    {
        return $this->bidi->send(Protocol::startScreencast(new StartScreencastParameters($this->id)));
    }

    public function stopScreencast(string $screencast): StopScreencastResult
    {
        return $this->bidi->send(Protocol::stopScreencast(new StopScreencastParameters($screencast)));
    }

    public function close(): void
    {
        $this->bidi->send(Protocol::close(new CloseParameters($this->id)));
    }
}
