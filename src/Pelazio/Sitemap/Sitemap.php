<?php

namespace Pelazio\Sitemap;

use Illuminate\Filesystem\Filesystem as Filesystem;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactory;

class Sitemap
{
    public null|Model $model = null;

    public null|CacheRepository $cache = null;

    protected null|ConfigRepository $configRepository = null;

    protected null|Filesystem $file = null;

    protected null|ResponseFactory $response = null;

    protected null|ViewFactory $view = null;

    public function __construct(array $config, CacheRepository $cache, ConfigRepository $configRepository, Filesystem $file, ResponseFactory $response, ViewFactory $view)
    {
        $this->cache = $cache;
        $this->configRepository = $configRepository;
        $this->file = $file;
        $this->response = $response;
        $this->view = $view;

        $this->model = new Model($config);
    }

    public function setCache(?string $key = null, $duration = null, bool $useCache = true): void
    {
        $this->model->setUseCache($useCache);

        if (null !== $key) {
            $this->model->setCacheKey($key);
        }

        if (null !== $duration) {
            $this->model->setCacheDuration($duration);
        }
    }

    public function isCached(): bool
    {
        if ($this->model->getUseCache()) {
            if ($this->cache->has($this->model->getCacheKey())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add new sitemap item to $items array.
     *
     * @param string $loc
     * @param string $lastmod
     * @param string $priority
     * @param string $freq
     * @param array  $images
     * @param string $title
     * @param array  $translations
     * @param array  $videos
     * @param array  $googlenews
     * @param array  $alternates
     *
     * @return void
     */
    public function add(?string $loc, ?string $lastmod = null, ?string $priority = null, ?string $freq = null, array $images = [], ?string $title = null, array $translations = [], array $videos = [], array $googlenews = [], array $alternates = []): void
    {
        $params = [
            'loc'           => $loc,
            'lastmod'       => $lastmod,
            'priority'      => $priority,
            'freq'          => $freq,
            'images'        => $images,
            'title'         => $title,
            'translations'  => $translations,
            'videos'        => $videos,
            'googlenews'    => $googlenews,
            'alternates'    => $alternates,
        ];

        $this->addItem($params);
    }

    public function addItem(array $params = []): void
    {

        // if is multidimensional
        if (array_key_exists(1, $params)) {
            foreach ($params as $a) {
                $this->addItem($a);
            }

            return;
        }

        // get params
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        // set default values
        if (!isset($loc)) {
            $loc = '/';
        }
        if (!isset($lastmod)) {
            $lastmod = null;
        }
        if (!isset($priority)) {
            $priority = null;
        }
        if (!isset($freq)) {
            $freq = null;
        }
        if (!isset($title)) {
            $title = null;
        }
        if (!isset($images)) {
            $images = [];
        }
        if (!isset($translations)) {
            $translations = [];
        }
        if (!isset($alternates)) {
            $alternates = [];
        }
        if (!isset($videos)) {
            $videos = [];
        }
        if (!isset($googlenews)) {
            $googlenews = [];
        }

        // escaping
        if ($this->model->getEscaping()) {
            $loc = htmlentities($loc, ENT_XML1);

            if ($title != null) {
                htmlentities($title, ENT_XML1);
            }

            if ($images) {
                foreach ($images as $k => $image) {
                    foreach ($image as $key => $value) {
                        $images[$k][$key] = htmlentities($value, ENT_XML1);
                    }
                }
            }

            if ($translations) {
                foreach ($translations as $k => $translation) {
                    foreach ($translation as $key => $value) {
                        $translations[$k][$key] = htmlentities($value, ENT_XML1);
                    }
                }
            }

            if ($alternates) {
                foreach ($alternates as $k => $alternate) {
                    foreach ($alternate as $key => $value) {
                        $alternates[$k][$key] = htmlentities($value, ENT_XML1);
                    }
                }
            }

            if ($videos) {
                foreach ($videos as $k => $video) {
                    if (!empty($video['title'])) {
                        $videos[$k]['title'] = htmlentities($video['title'], ENT_XML1);
                    }
                    if (!empty($video['description'])) {
                        $videos[$k]['description'] = htmlentities($video['description'], ENT_XML1);
                    }
                }
            }

            if ($googlenews) {
                if (isset($googlenews['sitename'])) {
                    $googlenews['sitename'] = htmlentities($googlenews['sitename'], ENT_XML1);
                }
            }
        }

        $googlenews['sitename'] = isset($googlenews['sitename']) ? $googlenews['sitename'] : '';
        $googlenews['language'] = isset($googlenews['language']) ? $googlenews['language'] : 'en';
        $googlenews['publication_date'] = isset($googlenews['publication_date']) ? $googlenews['publication_date'] : date('Y-m-d H:i:s');

        $this->model->setItems([
            'loc'          => $loc,
            'lastmod'      => $lastmod,
            'priority'     => $priority,
            'freq'         => $freq,
            'images'       => $images,
            'title'        => $title,
            'translations' => $translations,
            'videos'       => $videos,
            'googlenews'   => $googlenews,
            'alternates'   => $alternates,
        ]);
    }

    public function addSitemap(string $loc, ?string $lastmod = null): void
    {
        $this->model->setSitemaps([
            'loc'     => $loc,
            'lastmod' => $lastmod,
        ]);
    }

    public function resetSitemaps(array $sitemaps = []): void
    {
        $this->model->resetSitemaps($sitemaps);
    }

    public function render(string $format = 'xml', ?string $style = null)
    {
        // limit size of sitemap
        if ($this->model->getMaxSize() > 0 && count($this->model->getItems()) > $this->model->getMaxSize()) {
            $this->model->limitSize($this->model->getMaxSize());
        } elseif ('google-news' == $format && count($this->model->getItems()) > 1000) {
            $this->model->limitSize(1000);
        } elseif ('google-news' != $format && count($this->model->getItems()) > 50000) {
            $this->model->limitSize();
        }

        $data = $this->generate($format, $style);

        return $this->response->make($data['content'], 200, $data['headers']);
    }

    public function generate(string $format = 'xml', ?string $style = null): array
    {
        // check if caching is enabled, there is a cached content and its duration isn't expired
        if ($this->isCached()) {
            ('sitemapindex' == $format) ? $this->model->resetSitemaps($this->cache->get($this->model->getCacheKey())) : $this->model->resetItems($this->cache->get($this->model->getCacheKey()));
        } elseif ($this->model->getUseCache()) {
            ('sitemapindex' == $format) ? $this->cache->put($this->model->getCacheKey(), $this->model->getSitemaps(), $this->model->getCacheDuration()) : $this->cache->put($this->model->getCacheKey(), $this->model->getItems(), $this->model->getCacheDuration());
        }

        if (!$this->model->getLink()) {
            $this->model->setLink($this->configRepository->get('app.url'));
        }

        if (!$this->model->getTitle()) {
            $this->model->setTitle('Sitemap for ' . $this->model->getLink());
        }

        $channel = [
            'title' => $this->model->getTitle(),
            'link'  => $this->model->getLink(),
        ];

        // check if styles are enabled
        if ($this->model->getUseStyles()) {
            if (null != $this->model->getSloc() && file_exists(public_path($this->model->getSloc() . $format . '.xsl'))) {
                // use style from your custom location
                $style = $this->model->getSloc() . $format . '.xsl';
            } else {
                // don't use style
                $style = null;
            }
        } else {
            // don't use style
            $style = null;
        }

        switch ($format) {
            case 'ror-rss':
                return ['content' => $this->view->make('sitemap::ror-rss', ['items' => $this->model->getItems(), 'channel' => $channel, 'style' => $style])->render(), 'headers' => ['Content-type' => 'text/rss+xml; charset=utf-8']];
            case 'ror-rdf':
                return ['content' => $this->view->make('sitemap::ror-rdf', ['items' => $this->model->getItems(), 'channel' => $channel, 'style' => $style])->render(), 'headers' => ['Content-type' => 'text/rdf+xml; charset=utf-8']];
            case 'html':
                return ['content' => $this->view->make('sitemap::html', ['items' => $this->model->getItems(), 'channel' => $channel, 'style' => $style])->render(), 'headers' => ['Content-type' => 'text/html; charset=utf-8']];
            case 'txt':
                return ['content' => $this->view->make('sitemap::txt', ['items' => $this->model->getItems(), 'style' => $style])->render(), 'headers' => ['Content-type' => 'text/plain; charset=utf-8']];
            case 'sitemapindex':
                return ['content' => $this->view->make('sitemap::sitemapindex', ['sitemaps' => $this->model->getSitemaps(), 'style' => $style])->render(), 'headers' => ['Content-type' => 'text/xml; charset=utf-8']];
            default:
                return ['content' => $this->view->make('sitemap::' . $format, ['items' => $this->model->getItems(), 'style' => $style])->render(), 'headers' => ['Content-type' => 'text/xml; charset=utf-8']];
        }
    }

    public function store(string $format = 'xml', string $filename = 'sitemap', ?string $path = null, ?string $style = null): void
    {
        // turn off caching for this method
        $this->model->setUseCache(false);

        // use correct file extension
        (in_array($format, ['txt', 'html'], true)) ? $fe = $format : $fe = 'xml';

        if (true == $this->model->getUseGzip()) {
            $fe = $fe . ".gz";
        }

        // use custom size limit for sitemaps
        if ($this->model->getMaxSize() > 0 && count($this->model->getItems()) > $this->model->getMaxSize()) {
            if ($this->model->getUseLimitSize()) {
                // limit size
                $this->model->limitSize($this->model->getMaxSize());
                $data = $this->generate($format, $style);
            } else {
                // use sitemapindex and generate partial sitemaps
                foreach (array_chunk($this->model->getItems(), $this->model->getMaxSize()) as $key => $item) {
                    // reset current items
                    $this->model->resetItems($item);

                    // generate new partial sitemap
                    $this->store($format, $filename . '-' . $key, $path, $style);

                    // add sitemap to sitemapindex
                    if ($path != null) {
                        // if using custom path generate relative urls for sitemaps in the sitemapindex
                        $this->addSitemap($filename . '-' . $key . '.' . $fe);
                    } else {
                        // else generate full urls based on app's domain
                        $this->addSitemap(url($filename . '-' . $key . '.' . $fe));
                    }
                }

                $data = $this->generate('sitemapindex', $style);
            }
        } elseif (('google-news' != $format && count($this->model->getItems()) > 50000) || ($format == 'google-news' && count($this->model->getItems()) > 1000)) {
            ('google-news' != $format) ? $max = 50000 : $max = 1000;

            // check if limiting size of items array is enabled
            if (!$this->model->getUseLimitSize()) {
                // use sitemapindex and generate partial sitemaps
                foreach (array_chunk($this->model->getItems(), $max) as $key => $item) {
                    // reset current items
                    $this->model->resetItems($item);

                    // generate new partial sitemap
                    $this->store($format, $filename . '-' . $key, $path, $style);

                    // add sitemap to sitemapindex
                    if (null != $path) {
                        // if using custom path generate relative urls for sitemaps in the sitemapindex
                        $this->addSitemap($filename . '-' . $key . '.' . $fe);
                    } else {
                        // else generate full urls based on app's domain
                        $this->addSitemap(url($filename . '-' . $key . '.' . $fe));
                    }
                }

                $data = $this->generate('sitemapindex', $style);
            } else {
                // reset items and use only most recent $max items
                $this->model->limitSize($max);
                $data = $this->generate($format, $style);
            }
        } else {
            $data = $this->generate($format, $style);
        }

        // clear memory
        if ('sitemapindex' == $format) {
            $this->model->resetSitemaps();
        }

        $this->model->resetItems();

        // if custom path
        if (null == $path) {
            $file = public_path() . DIRECTORY_SEPARATOR . $filename . '.' . $fe;
        } else {
            $file = $path . DIRECTORY_SEPARATOR . $filename . '.' . $fe;
        }

        if (true == $this->model->getUseGzip()) {
            // write file (gzip compressed)
            $this->file->put($file, gzencode($data['content'], 9));
        } else {
            // write file
            $this->file->put($file, $data['content']);
        }
    }
}
