<?php
namespace Equip;

use Equip\Adr\PayloadInterface;

class Payload implements PayloadInterface
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $input;

    /**
     * @var array
     */
    private $output;

    /**
     * @var array
     */
    private $messages;

    /**
     * @inheritDoc
     */
    public function withStatus($status)
    {
        $copy = clone $this;
        $copy->status = $status;

        return $copy;
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function withInput(array $input)
    {
        $copy = clone $this;
        $copy->input = $input;

        return $copy;
    }

    /**
     * @inheritDoc
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @inheritDoc
     */
    public function withOutput(array $output)
    {
        $copy = clone $this;
        $copy->output = $output;

        return $copy;
    }

    /**
     * @inheritDoc
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @inheritDoc
     */
    public function withMessages(array $messages)
    {
        $copy = clone $this;
        $copy->messages = $messages;

        return $copy;
    }

    /**
     * @inheritDoc
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
