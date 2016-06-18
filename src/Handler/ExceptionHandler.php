<?php

namespace Equip\Handler;

use Equip\Exception\ExceptionInterface;
use Equip\Exception\HttpException;
use Equip\Formatter\WhoopsHtmlFormatter;
use Equip\Formatter\WhoopsJsonFormatter;
use Equip\Formatter\WhoopsPlainFormatter;
use Equip\Payload;
use Equip\Resolver\ResolverTrait;
use Equip\Responder\FormattedResponder;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\ResolverInterface;
use Throwable;

class ExceptionHandler
{
    use ResolverTrait;

    /**
     * Allowed range for a valid HTTP status code.
     *
     * @const integer
     * @const integer
     */
    const MINIMUM_HTTP_CODE = 100;
    const MAXIMUM_HTTP_CODE = 599;

    /**
     * The code for missing or invalid HTTP status code.
     *
     * @const integer
     */
    const MISSING_HTTP_CODE = 500;

    /**
     * An exception handlers as the formatters.
     *
     * @var array $formatters
     */
    private $formatters = [];

    /**
     * The modified code for missing or invalid HTTP status code.
     *
     * @var integer
     */
    private $missingHttpCode;

    /**
     * @param ResolverInterface $resolver
     * @param array $formatters
     * @param integer $missingHttpCode
     */
    public function __construct(
        ResolverInterface $resolver,
        array $formatters = [
            WhoopsHtmlFormatter::class => 1.0,
            WhoopsJsonFormatter::class => 1.0,
            WhoopsPlainFormatter::class => 1.0,
        ],
        $missingHttpCode = self::MISSING_HTTP_CODE
    ) {
        $this->resolver = $resolver;
        $this->formatters = $formatters;
        $this->missingHttpCode = $missingHttpCode;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        try {
            return $next($request, $response);
        } catch (ExceptionInterface $exception) {
            return $this->withEquipException($request, $response, $exception);
        } catch (Throwable $throwable) {
            return $this->withException($request, $response, $throwable);
        } catch (Exception $exception) {
            return $this->withException($request, $response, $exception);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param ExceptionInterface $exception
     *
     * @return ResponseInterface
     */
    public function withEquipException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ExceptionInterface $exception
    ) {
        if ($exception instanceof HttpException) {
            $response = $exception->withResponse($response);
        }

        return $this->withException($request, $response, $exception);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Throwable|Exception $exception
     *
     * @return ResponseInterface
     */
    public function withException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $exception
    ) {
        $response = $this->status($response, $exception);
        $response = $this->format($request, $response, $exception);

        return $response;
    }

    /**
     * Get the response with the status code from the exception.
     *
     * @param ResponseInterface $response
     * @param Throwable|Exception $exception
     *
     * @return ResponseInterface
     */
    private function status(
        ResponseInterface $response,
        $exception
    ) {
        $exceptionCode = $exception->getCode();

        $code = filter_var($exceptionCode, FILTER_VALIDATE_INT, [
            'options' => [
                'default'   => $this->missingHttpCode,
                'min_range' => self::MINIMUM_HTTP_CODE,
                'max_range' => self::MAXIMUM_HTTP_CODE,
            ]
        ]);

        return $response->withStatus($code);
    }

    /**
     * Update the response by formatting the exception handler.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Throwable|Exception $exception
     *
     * @return ResponseInterface
     */
    private function format(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $exception
    ) {
        $formatter = $this->resolve(FormattedResponder::class);
        $formatter = $formatter->withValues($this->formatters);

        $payload = $this->payload($request, $response, $exception);

        return $formatter($request, $response, $payload);
    }

    /**
     * Get the payload with the request, response, exception.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Throwable|Exception $exception
     *
     * @return Payload
     */
    private function payload(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $exception
    ) {
        $payload = $this->resolve(Payload::class);
        $output = compact('request', 'response', 'exception');

        return $payload->withOutput($output);
    }
}
