<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * dependency injection component
 *
 * @package earc/di
 * @link https://github.com/Koudela/eArc-di/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DI;

use eArc\DI\Interfaces\ResolverCallableInterface;

class ResolverCallable implements ResolverCallableInterface
{
    protected $className;
    protected $callable;
    protected $arguments;
    protected $tags;

    public function __construct(string $fQCN, callable $callable, array $arguments=[], array $tags=[])
    {
        $this->className = $fQCN;
        $this->callable = $callable;
        $this->arguments = $arguments;
        $this->tags = array_flip($tags);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getTags(): array
    {
        return array_flip($this->tags);
    }

    public function isTaggedBy(array $tags=[]): bool
    {
        if (empty($tags)) {
            return true;
        }

        foreach ($tags as $tag) {
            if (isset($this->tags[$tag])) {
                return true;
            }
        }

        return false;
    }
}
