<?php
namespace Spark\Resolver;

interface ResolverInterface
{
    /**
     * @param mixed $spec
     * @return callable
     */
    public function __invoke($spec);
}
