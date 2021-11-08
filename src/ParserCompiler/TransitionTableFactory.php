<?php

namespace App\ParserCompiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
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

            if ($parserCompiler === null) {
                throw new \RuntimeException('Cannot find parser compiler');
            }

            $parserCompiler->compile();
            return $parserCompiler->getTransitionTable();
        });
    }

}