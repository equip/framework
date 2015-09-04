<?php

namespace Spark\Responder;

use Negotiation\NegotiatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\PayloadInterface;
use Spark\Adr\ResponderInterface;
use Spark\Formatter\AbstractFormatter;
use Spark\Resolver\ResolverInterface;

class FormattedResponder implements ResponderInterface
{
    /**
     * @var NegotiatorInterface
     */
    private $negotiator;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var array
     */
    private $formatters = [
        'Spark\Formatter\JsonFormatter' => 1.0,
        'Spark\Formatter\HtmlFormatter' => 0.9,
    ];

    /**
     * @param NegotiatorInterface $negotiator
     * @param ResolverInterface   $resolver
     */
    public function __construct(
        NegotiatorInterface $negotiator,
        ResolverInterface   $resolver
    ) {
        $this->negotiator = $negotiator;
        $this->resolver   = $resolver;
    }

    /**
     * Retrieve available formatters.
     *
     * @return array
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * Return an instance with the specified formatters.
     *
     * @param  array $formatters
     * @return self
     */
    public function withFormatters(array $formatters)
    {
        arsort($formatters);

        $self = clone $this;
        $self->formatters = $formatters;
        return $self;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        PayloadInterface       $payload
    ) {
        $formatter = $this->formatter($request);
        $response  = $this->format($response, $formatter, $payload);

        return $response;

    }

    /**
     * Retrieve a map of accepted priorities with the responsible formatter.
     *
     * @return array
     */
    protected function priorities()
    {
        $priorities = [];
        $formatters = array_keys($this->formatters);

        foreach ($formatters as $spec) {
            foreach ($spec::accepts() as $type) {
                $priorities[$type] = $spec;
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
     * @param  ServerRequestInterface $request
     * @return AbstractFormatter
     */
    protected function formatter(ServerRequestInterface $request)
    {
        $accept     = current($request->getHeader('Accept'));
        $priorities = $this->priorities();
        $preferred  = $this->negotiator->getBest($accept, array_keys($priorities));

        if ($preferred) {
            $formatter = $priorities[$preferred->getValue()];
        } else {
            $formatter = array_shift($priorities);
        }

        return call_user_func($this->resolver, $formatter);
    }

    /**
     * Update the response by formatting the payload.
     *
     * @param  ResponseInterface $response
     * @param  AbstractFormatter $formatter
     * @param  PayloadInterface  $payload
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
}
