<?php
namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;
use Lukasoppermann\Httpstatus\Httpstatus;

abstract class AbstractFormatter
{
    /**
     * @var Httpstatus
     */
    private $http_status;

    /**
     * @param Httpstatus $http_status
     */
    public function __construct(Httpstatus $http_status)
    {
        $this->http_status = $http_status;
    }

    /**
     * Get the content types this formatter can satisfy.
     *
     * @return array
     */
    public static function accepts()
    {
        throw new \RuntimeException(sprintf(
            '%s::%s() must be defined to declare accepted content types',
            static::class,
            __FUNCTION__
        ));
    }

    /**
     * Get the content type of the response body.
     *
     * @return string
     */
    abstract protected function type();

    /**
     * Get the response body from the payload.
     *
     * @param PayloadInterface $payload
     *
     * @return string
     */
    abstract protected function body(PayloadInterface $payload);

    /**
     * Get the response status from the payload.
     *
     * @param PayloadInterface $payload
     *
     * @return integer
     */
    public function status(PayloadInterface $payload)
    {
        $status = $payload->getStatus();

        // Legacy logic
        // @todo Remove this in 2.0
        if (is_int($status)) {
            if ($status >= PayloadInterface::OK && $status < PayloadInterface::ERROR) {
                return 200;
            }
            if ($status >= PayloadInterface::ERROR && $status < PayloadInterface::INVALID) {
                return 500;
            }
            if ($status >= PayloadInterface::INVALID && $status < PayloadInterface::UNKNOWN) {
                return 400;
            }
            return 520;
        }

        return $this->http_status->getStatusCode($status);
    }
}
