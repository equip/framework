<?php
namespace Spark;

use Spark\Adr\PayloadInterface;

class Payload implements PayloadInterface
{
    /**
     * @var integer
     */
    private $status;

    /**
     * @var array
     */
    private $output;

    /**
     * @var array
     */
    private $messages;

    public function withStatus($code)
    {
        $new = clone $this;
        $new->status = $code;
        return $new;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function withOutput(array $output)
    {
        $new = clone $this;
        $new->output = $output;
        return $new;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function withMessages(array $messages)
    {
        $new = clone $this;
        $new->messages = $messages;
        return $new;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
