<?php

namespace App\service;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Symfony\Contracts\Cache\CacheInterface;

class MarkdownHelper{

    private $markdownParser;
    private $cacheInterface;

    public function __construct(MarkdownParserInterface $markdownParser, CacheInterface $cacheInterface)
    {
        $this->markdownParser = $markdownParser;
        $this->cacheInterface = $cacheInterface;
    }

    public function parse(string $source): string{
        return $this->cacheInterface->get('anything'.md5($source),function() use($source){
            return $this->markdownParser->transformMarkdown($source);
        });
    }
}