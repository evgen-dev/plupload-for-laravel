<?php

namespace EvgenDev\LaravelPlupload;

use Illuminate\Contracts\Config\Repository as Config;

class Plupload
{
        /**
         * Config Instance.
         *
         * @var \Illuminate\Contracts\Config\Repository
         */
        protected $config;

        /**
         * Constructor.
         *
         * @param \Illuminate\Contracts\Config\Repository
         */
        public function __construct(Config $config)
        {
            $this->config = $config;
        }

    /**
     * Get a plupload configuration option.
     *
     * @param string $option
     *
     * @return mixed
     */
    public function getConfigOption($option)
    {
        return $this->config->get("plupload-for-laravel::plupload.{$option}");
    }
}
