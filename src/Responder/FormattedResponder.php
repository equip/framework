<?php

namespace Equip\Responder;

use Equip\Adr\PayloadInterface;
use Equip\Adr\ResponderInterface;
use Equip\Exception\FormatterException;
use Equip\Formatter\FormatterInterface;
use Equip\Formatter\JsonFormatter;
use Equip\Resolver\ResolverTrait;
use Equip\Structure\SortedDictionary;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\ResolverInterface;

class FormattedResponder extends SortedDictionary implements ResponderInterface
{
    use ResolverTrait;

    /**
     * @var Negotiator
     */
    private $negotiator;

    /**
     * @param Negotiator $negotiator
     * @param ResolverInterface $resolver
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
     * @inheritDoc
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        if ($this->hasOutput($payload)) {
            $response = $this->format($request, $response, $payload);
        }

        return $response;
    }

    /**
     * Determine if the payload has usable output
     *
     * @param PayloadInterface $payload
     *
     * @return boolean
     */
    protected function hasOutput(PayloadInterface $payload)
    {
        return (bool) $payload->getOutput();
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
     * Retrieve the formatter to use for the current request.
     *
     * Uses content negotiation to find the best available output format for
     * the requested content type.
     *
     * @param ServerRequestInterface $request
     *
     * @return FormatterInterface
     */
    protected function formatter(ServerRequestInterface $request)
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
     * Update the response by formatting the payload.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param PayloadInterface $payload
     *
     * @return ResponseInterface
     */
    protected function format(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        $formatter = $this->formatter($request);

        $response = $response->withHeader('Content-Type', $formatter->type());
        // Overwrite the body instead of making a copy and dealing with the stream.
        $response->getBody()->write($formatter->body($payload));

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws FormatterException
     *  If $classes does not implement the correct interface,
     *  or does not have a quality value.
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
