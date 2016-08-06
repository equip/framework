<?php

namespace Equip;

use Equip\Exception\FormatterException;
use Equip\Formatter\FormatterInterface;
use Equip\Formatter\JsonFormatter;
use Equip\Resolver\ResolverTrait;
use Equip\Structure\SortedDictionary;
use Negotiation\Negotiator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Relay\ResolverInterface;

class ContentNegotiation extends SortedDictionary
{
    use ResolverTrait;

    /**
     * @var Negotiator
     */
    private $negotiator;

    /**
     * @param Negotiator $negotiator
     * @param ResolverInterface $resolver
     * @param array $formatters
     */
    public function __construct(
        Negotiator $negotiator,
        ResolverInterface $resolver,
        array $formatters = [
            JsonFormatter::class => 1.0,
        ]
    ) {
        $this->negotiator = $negotiator;
        $this->resolver   = $resolver;

        parent::__construct($formatters);
    }

    /**
     * Update the response by formatting raw output content.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $content
     *
     * @return ResponseInterface
     */
    public function apply(
        RequestInterface $request,
        ResponseInterface $response,
        $content
    ) {
        $formatter = $this->formatter($request);

        $response = $response->withHeader('Content-Type', $formatter->type());

        // Overwrite the body instead of making a copy and dealing with the stream.
        // HTTP Factories would make this much better! (PSR-17)
        $response->getBody()->write($formatter->format($content));

        return $response;
    }

    /**
     * Retrieve the formatter to use for the current request.
     *
     * Uses content negotiation to find the best available output format for
     * the requested content type.
     *
     * @param RequestInterface $request
     *
     * @return FormatterInterface
     */
    protected function formatter(RequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept');
        $priorities = $this->priorities();

        if (!empty($accept)) {
            $preferred = $this->negotiator->getBest($accept, array_keys($priorities));
        }

        if (!empty($preferred)) {
            $formatter = $priorities[$preferred->getValue()];
        } else {
            $formatter = array_shift($priorities);
        }

        return $this->resolve($formatter);
    }

    /**
     * Retrieve a map of accepted priorities with the responsible formatter.
     *
     * @return array
     */
    protected function priorities()
    {
        $priorities = [];

        foreach ($this as $formatter => $quality) {
            foreach ($formatter::accepts() as $type) {
                $priorities[$type] = $formatter;
            }
        }

        return $priorities;
    }

    /**
     * @inheritDoc
     *
     * @throws FormatterException
     *  If $classes does not implement the correct interface, or does not have
     *  a quality value.
     */
    protected function assertValid(array $classes)
    {
        parent::assertValid($classes);

        foreach ($classes as $formatter => $quality) {
            if (!is_subclass_of($formatter, FormatterInterface::class)) {
                throw FormatterException::invalidClass($formatter);
            }

            if (!is_float($quality)) {
                throw FormatterException::needsQuality($formatter);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function sortValues()
    {
        arsort($this->values);
    }
}
