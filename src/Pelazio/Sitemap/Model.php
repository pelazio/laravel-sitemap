<?php

namespace Pelazio\Sitemap;

class Model
{
    public bool $testing = false;

    private array $items = [];

    private array $sitemaps = [];

    private null|string $title = null;

    private $link = null;

    private bool $useStyles = true;

    private ?string $sloc = '/vendor/sitemap/styles/';

    private bool $useCache = false;

    private string $cacheKey = 'laravel-sitemap.';

    private $cacheDuration = 3600;

    private bool $escaping = true;

    private bool $useLimitSize = false;

    private $maxSize = null;

    private bool $useGzip = false;

    public function __construct(array $config)
    {
        $this->useCache = $config['use_cache'] ??  $this->useCache;
        $this->cacheKey =  $config['cache_key'] ?? $this->cacheKey;
        $this->cacheDuration = $config['cache_duration'] ?? $this->cacheDuration;
        $this->escaping = $config['escaping'] ?? $this->escaping;
        $this->useLimitSize = $config['use_limit_size'] ?? $this->useLimitSize;
        $this->useStyles = $config['use_styles'] ?? $this->useStyles;
        $this->sloc = $config['styles_location'] ?? $this->sloc;
        $this->maxSize = $config['max_size'] ?? $this->maxSize;
        $this->testing = $config['testing'] ?? $this->testing;
        $this->useGzip = $config['use_gzip'] ?? $this->useGzip;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getSitemaps(): array
    {
        return $this->sitemaps;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getUseStyles(): bool
    {
        return $this->useStyles;
    }

    public function getSloc(): string
    {
        return $this->sloc;
    }

    public function getUseCache(): bool
    {
        return $this->useCache;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function getCacheDuration()
    {
        return $this->cacheDuration;
    }

    public function getEscaping(): bool
    {
        return $this->escaping;
    }

    public function getUseLimitSize(): bool
    {
        return $this->useLimitSize;
    }

    public function getMaxSize(): ?int
    {
        return $this->maxSize;
    }

    public function getUseGzip(): bool
    {
        return $this->useGzip;
    }

    public function setEscaping(bool $b): void
    {
        $this->escaping = $b;
    }

    public function setItems(array $items): void
    {
        $this->items[] = $items;
    }

    public function setSitemaps(array $sitemap): void
    {
        $this->sitemaps[] = $sitemap;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function setUseStyles(bool $useStyles): void
    {
        $this->useStyles = $useStyles;
    }

    public function setSloc(?string $sloc): void
    {
        $this->sloc = $sloc;
    }

    public function setUseLimitSize(bool $useLimitSize): void
    {
        $this->useLimitSize = $useLimitSize;
    }

    public function setMaxSize(int $maxSize): void
    {
        $this->maxSize = $maxSize;
    }

    public function setUseGzip(bool $useGzip = true): void
    {
        $this->useGzip = $useGzip;
    }

    public function limitSize(int $max = 50000): void
    {
        $this->items = array_slice($this->items, 0, $max);
    }

    public function resetItems(array $items = []): void
    {
        $this->items = $items;
    }

    public function resetSitemaps(array $sitemaps = []): void
    {
        $this->sitemaps = $sitemaps;
    }

    public function setUseCache(bool $useCache = true): void
    {
        $this->useCache = $useCache;
    }

    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    public function setCacheDuration($cacheDuration): void
    {
        $this->cacheDuration = $cacheDuration;
    }
}
