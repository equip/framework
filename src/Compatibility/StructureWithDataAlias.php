<?php

namespace Equip\Compatibility;

/**
 * @since 1.3.0
 */
trait StructureWithDataAlias
{
    abstract public function withValues(array $values);

    /**
     * Backwards compatability with Destrukt.
     *
     * @deprecated since 1.3.0, to be removed in 2.0.0
     *
     * @param array $data
     *
     * @return static
     */
    public function withData(array $data)
    {
        return $this->withValues($data);
    }
}
