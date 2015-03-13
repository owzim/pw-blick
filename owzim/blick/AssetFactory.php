<?php

/**
 * Class definition of AssetFactory
 *
 * @author Christian (owzim) Raunitschka <git@raunitschka.de>
 * @copyright Copyright (c) 2015, Christian Raunitschka
 *
 * @version 0.2.0
 *
 * @filesource
 */

namespace owzim\Blick;

class AssetFactory
{

    /**
     * http://regexr.com/3a5d0
     *
     */
    const REMOTE_REGEX = '/^(https?\:)?\/\//';

    /**
     * Get a new Asset instance with properties depending on `$config`
     *
     * @param  string $fullName
     * @param  string $type
     * @param  object $config
     * @param  array $args
     * @return Asset
     */
    public static function get($fullName, $type, $config, $args = array(), $forceNew = false)
    {
        static $cache = null;
        if (is_null($cache)) $cache = array();
        $id = "{$fullName}-{$type}-" . implode('-', $args);
        if (!$forceNew && array_key_exists($id, $cache)) return $cache[$id];

        $class = __NAMESPACE__ . ($type === Asset::TYPE_IMG ? '\Image' : '\Asset');
        $asset = new $class($fullName, $type, $config->$type, $args);

        return $cache[$id] = $asset;
    }
}
