<?php

namespace Equip\Responder;

use Equip\Adr\PayloadInterface;
use Equip\Adr\ResponderInterface;
use Equip\Formatter\AbstractFormatter;
use Equip\Formatter\JsonFormatter;
use Equip\Resolver\ResolverTrait;
use Equip\Structure\SortedDictionary;
use InvalidArgumentException;
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
            $formatter = $this->formatter($request);
            $response = $this->format($response, $formatter, $payload);
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
     * @return AbstractFormatter
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
     * @param ResponseInterface $response
     * @param AbstractFormatter $formatter
     * @param PayloadInterface $payload
     *
     * @return ResponseInterface
     */
    protected function format(
        ResponseInterface $response,
        AbstractFormatter $formatter,
        PayloadInterface  $payload
    ) {
        $response = $response->withStatus($formatter->status($payload));
        $response = $response->withHeader('Content-Type', $formatter->type());

        // Overwrite the body instead of making a copy and dealing with the stream.
        $response->getBody()->write($formatter->body($payload));

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     *  If a formatter does not implement the correct interface, or does not
     *  have a quality value.
     */
    protected function assertValid(array $data)
    {
        parent::assertValid($data);

        foreach ($data as $formatter => $quality) {
            if (!is_subclass_of($formatter, AbstractFormatter::class)) {
                throw new InvalidArgumentException(sprintf(
                    'All formatters in `%s` must implement `%s`',
                    static::class,
                    AbstractFormatter::class
                ));
            }

            if (!is_float($quality)) {
                throw new InvalidArgumentException(sprintf(
                    'All formatters in `%s` must have a quality value',
                    static::class
                ));
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
