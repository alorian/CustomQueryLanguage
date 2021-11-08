<?php

namespace App\ParserCompiler;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TransitionTableFactory
{

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __invoke(CacheInterface $cache, ContainerInterface $container): TransitionTable
    {
        return $cache->get('transitions_table', static function () use ($container) {
            $parserCompiler = $container->get(ParserCompiler::class);
            $parserCompiler->compile();
            return $parserCompiler->getTransitionTable();
        });
    }

}