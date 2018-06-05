<?php

/*
* Plugin Name: Pagely CLI
* Plugin URI: https://pagely.com/
* Description: Pagely WP-CLI Integrations
* Version: 0.1
* Author: Pagely
* Author URI: https://pagely.com
* */

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command( 'pagely', 'Pagely_CLI' );
}

/**
 * Pagely CLI
 */

class Pagely_CLI
{

    public function __construct()
    {

        if (!class_exists('Pagely_Alert')) {
            $this->gripePlugins();
        }

        $this->pagelyAlerts = Pagely_Alert::instance();
    }

    protected function gripe($msg, $exit = false)
    {
        WP_CLI::error($msg, $exit);
    }

    protected function gripePlugins()
    {
        $this->gripe('Oops, looks like the pagely plugins are missing', true);
    }

    protected function handleAlerts()
    {
        $errors = array_filter($this->pagelyAlerts->getAlerts(), function($item) {
             return (!$item['status']);
        });

        if ($errors) {
            $this->gripe('Uh-oh, something went wrong!');
            foreach($errors as $k => $v) {
                $this->gripe($v['msg']);
            }
            WP_CLI::halt(1);
        }
    }

    /**
     * Purge a particular URL from the Nginx cache.
     *
     * ## OPTIONS
     * <path>
     * : The URL to purge from cache. The special value '*' causes all cache to be purged
     *
     *
     * ## EXAMPLES
     *
     *     # Purge $site/my-post/
     *     $ wp pagely purge-nginx /my-post/
     *
     *     # Purge everything
     *     $ wp pagely purge-nginx '*'
     *
     * @subcommand purge-nginx
     */

    public function purgeNginx($args, $assoc_args)
    {

        if (!class_exists('PagelyCachePurge')) {
            $this->gripePlugins();
        }

        $purger = new PagelyCachePurge();

        $path = $args[0];

        if ('*' == $path ) {
            WP_CLI::log('Purging everything.');
            $purger->purgeAll();
        } else {
            WP_CLI::log("Purging: $path");
            $purger->purgePath($path);
        }

        if (!empty($purger->purgeErrors)) {

            $this->gripe('Uh-oh, something went wrong!');

            foreach ($purger->purgeErrors as $err) {

                $this->gripe($err);
            }

            WP_CLI::halt(1);
        }
        WP_CLI::success('Purge request sent.');

    }

    /**
     * Purge CDN zone cache
     *
     * ## EXAMPLES
     *
     *     $ wp pagely purge-cdn
     *
     * @subcommand purge-cdn
     */

    public function purgeCDN($args, $assoc_args)
    {

        if (!function_exists('pagely_purge_cdn')) {
            $this->gripePlugins();
        }

        pagely_purge_cdn();
        $this->handleAlerts();

        WP_CLI::success('CDN purge request sent.');
    }

}
