<?php

/**
 * Class definition of Image
 *
 * @author Christian (owzim) Raunitschka <git@raunitschka.de>
 * @copyright Copyright (c) 2015, Christian Raunitschka
 *
 * @version 0.3.0
 *
 * @filesource
 *
 * @property string $default
 * @property string $markup
 * @property string $version
 * @property string $path
 * @property string $url
 */

namespace owzim\Blick;

class Image extends Asset
{
    /**
     * borrowed from \PageImage::size and modified
     *
     * @param  int $width
     * @param  int $height
     * @param  array $options
     * @return Image
     */
    function size($width, $height, $options = null)
    {

        if($this->ext == 'svg') return $this;

        if(!is_array($options)) {
            if(is_string($options)) {
                // optionally allow a string to be specified with crop direction, for shorter syntax
                if(strpos($options, ',') !== false) $options = explode(',', $options); // 30,40
                $options = array('cropping' => $options);
            } else if(is_int($options)) {
                // optionally allow an integer to be specified with quality, for shorter syntax
                $options = array('quality' => $options);
            } else if(is_bool($options)) {
                // optionally allow a boolean to be specified with upscaling toggle on/off
                $options = array('upscaling' => $options);
            } else {
                // unknown options type
                $options = array();
            }
        }

        $defaultOptions = array(
            'upscaling' => true,
            'cropping' => true,
            'quality' => 90,
            'hidpiQuality' => 40,
            'suffix' => array(), // can be array of suffixes or string of 1 suffix
            'forceNew' => false,  // force it to create new image even if already exists
            'hidpi' => false,
            'cleanFilename' => false, // clean filename of historial resize information
            );

        $this->error = '';
        $configOptions = $this->config->imageSizerOptions;
        if(!is_array($configOptions)) $configOptions = array();
        $options = array_merge($defaultOptions, $configOptions, $options);

        $width = (int) $width;
        $height = (int) $height;

        if(strpos($options['cropping'], 'x') === 0 && preg_match('/^x(\d+)[yx](\d+)/', $options['cropping'], $matches)) {
            $options['cropping'] = true;
            $options['cropExtra'] = array((int) $matches[1], (int) $matches[2], $width, $height);
            $crop = '';
        } else {
            $crop = \ImageSizer::croppingValueStr($options['cropping']);
        }

        $suffixStr = '';
        if(!empty($options['suffix'])) {
            $suffix = is_array($options['suffix']) ? $options['suffix'] : array($options['suffix']);
            sort($suffix);
            foreach($suffix as $key => $s) {
                $s = strtolower($this->wire('sanitizer')->fieldName($s));
                if(empty($s)) unset($suffix[$key]);
                    else $suffix[$key] = $s;
            }
            if(count($suffix)) $suffixStr = '-' . implode('-', $suffix);
        }

        if($options['hidpi']) {
            $suffixStr .= '-hidpi';
            if($options['hidpiQuality']) $options['quality'] = $options['hidpiQuality'];
        }

        //$basename = $this->pagefiles->cleanBasename($this->basename(), false, false, false);
        // cleanBasename($basename, $originalize = false, $allowDots = true, $translate = false)
        $basename = $this->name;       // i.e. myfile
        if($options['cleanFilename'] && strpos($basename, '.') !== false) {
            $basename = substr($basename, 0, strpos($basename, '.'));
        }
        $basename .= '.' . $width . 'x' . $height . $crop . $suffixStr . "." . $this->ext;    // i.e. myfile.100x100.jpg or myfile.100x100nw-suffix1-suffix2.jpg


        if ($this->conf->variationSubDir) {
            $variationSubDir = "{$this->conf->variationSubDir}/";
            $subPath = "{$this->dir}/{$this->conf->variationSubDir}";
        } else {
            $variationSubDir = '';
            $subPath = $this->dir;
        }


        if ($variationSubDir && !file_exists($subPath)) wireMkdir($subPath, true);
        wireChmod($subPath, true);

        $filenameFinal = "$subPath/{$basename}";


        $tmpDir = "{$this->dir}/tmp_" . str_replace('.', '_', $basename);
        if (!file_exists($tmpDir)) wireMkdir($tmpDir, true);
        wireChmod($tmpDir, true);



        $filenameUnvalidated = "$tmpDir/$basename";

        $exists = file_exists($filenameFinal);

        if(!$exists || $options['forceNew']) {
            if($exists && $options['forceNew']) @unlink($filenameFinal);
            if(file_exists($filenameUnvalidated)) @unlink($filenameUnvalidated);

            if(@copy($this->path, $filenameUnvalidated)) {

                try {
                    $sizer = new \ImageSizer($filenameUnvalidated);
                    $sizer->setOptions($options);
                    if($sizer->resize($width, $height) && @rename($filenameUnvalidated, $filenameFinal)) {
                        wireChmod($filenameFinal);
                    } else {
                        $this->error = "ImageSizer::resize($width, $height) failed for $filenameUnvalidated";
                    }
                } catch(\Exception $e) {

                    $this->error = $e->getMessage();
                }
            } else {
                $this->error = "Unable to copy {$this->path} => $filenameUnvalidated";
            }
        }

        $image = clone $this;

        // if desired, user can check for property of $image->error to see if an error occurred.
        // if an error occurred, that error property will be populated with details
        if($this->error) {

            // error condition: unlink copied file
            if(is_file($filenameFinal)) @unlink($filenameFinal);
            if(is_file($filenameUnvalidated)) @unlink($filenameUnvalidated);


            // write an invalid image so it's clear something failed
            // todo: maybe return a 1-pixel blank image instead?
            $data = "This is intentionally invalid image data.\n$this->error";
            if(file_put_contents($filenameFinal, $data) !== false) wireChmod($filenameFinal);

            // we also tell PW about it for logging and/or admin purposes
            $this->error($this->error);
        }

        if(is_dir($tmpDir)) wireRmdir($tmpDir, true);



        $image->filename = pathinfo($filenameFinal, PATHINFO_BASENAME);
        $image->variationSubDir = rtrim($variationSubDir, '/');

        return $image;
    }

    /**
     * Get a variation by name, which can be configured in `$config->blick->imgVariations`
     *
     * Variations example
     *
     * ```
     * array(
     *     'header' => array(
     *          'width' => 960,
     *          'height' => 360,
     *          'options' => array(
     *              'suffix' => 'header',
     *          ),
     *      ),
     *     'person' => array(
     *          'width' => 200,
     *          'height' => 200,
     *          'options' => array(
     *              'suffix' => 'person',
     *          ),
     *      ),
     * )
     * ```
     *
     * @param  string $name
     * @return Image
     */
    public function variant($name, $scale = 100)
    {
        $scale = $scale / 100;
        if (isset($this->conf->variations[$name])) {
            $variation = $this->conf->variations[$name];
            $width = isset($variation['width']) ? $variation['width'] : null;
            $height = isset($variation['height']) ? $variation['height'] : null;
            $options = isset($variation['options']) ? $variation['options'] : array();

            if (!is_null($width) && !is_null($height)) {
                return $this->size($width * $scale, $height * $scale, $options);
            } else if (!is_null($width)) {
                return $this->width($width * $scale, $options);
            } else if (!is_null($height)) {
                return $this->height($height * $scale, $options);
            } else {
                return $this;
            }
        }
        return $this;
    }
    /**
     * alias for variant()
     * @see variant()
     */
    public function getVariation($name, $scale = 100)
    {
        return $this->variant($name, $scale);
    }

    public function crop($x, $y, $width, $height, $options = array())
    {

        $x = (int) $x;
        $y = (int) $y;
        $width = (int) $width;
        $height = (int) $height;

        if(empty($options['suffix'])) {
            $options['suffix'] = array();
        } else if(!is_array($options['suffix'])) {
            $options['suffix'] = array($options['suffix']);
        }

        $options['suffix'][] = "cropx{$x}y{$y}";
        $options['cropExtra'] = array($x, $y, $width, $height);
        $options['cleanFilename'] = true;

        return $this->size($width, $height, $options);
    }

    public function width($n = 0, $options = array())
    {
        return $this->size($n, 0, $options);
    }

    /**
     * Multipurpose: return the height of the Pageimage OR return an image sized with a given height (and proportional width)
     *
     * If given a height, it'll return a new Pageimage object sized to that height.
     * If not given a height, it'll return the height of this Pageimage
     *
     * @param int $n Optional height
     * @param array|string|int|bool $options Optional options (see size function)
     * @return int|Pageimage
     *
     */
    public function height($n = 0, $options = array())
    {
        return $this->size(0, $n, $options);
    }
}
