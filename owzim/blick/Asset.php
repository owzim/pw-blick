<?php

/**
 * Class definition of Asset
 *
 * @author Christian (owzim) Raunitschka <git@raunitschka.de>
 * @copyright Copyright (c) 2015, Christian Raunitschka
 *
 * @version 0.4.1
 *
 * @filesource
 *
 * @property string $default
 * @property string $markup
 * @property string $version
 * @property string $path
 * @property string $attrsStr
 * @property string $url
 */

namespace owzim\Blick;

class Asset extends \WireData
{
    /**
     *
     */
    const TYPE_IMG = 'img';

    /**
     *
     */
    const TYPE_JS  = 'js';

    /**
     *
     */
    const TYPE_CSS = 'css';

    /**
     * Regex for determining if a given file name is a remote url
     *
     * @see http://regexr.com/3a5d0
     */
    const REMOTE_REGEX = '/^(https?\:)?\/\//';

    /**
     * Regex for determining if a given file name is an absolute url
     */
    const ABSOLUTE_REGEX = '/^\/.*/';

    /**
     * @var string $_fullName
     */
    protected $_fullName = null;

    /**
     * array holding all the attributes
     * @var array
     */
    protected $_attrs = array();

    /**
     * @var string $type
     */
    protected $type = null;

    /**
     * @var object $conf
     */
    protected $conf = null;

    /**
     * @var array $args
     */
    protected $args = null;

    /**
     * @var string $pathPrefix
     */
    protected $pathPrefix = '';

    /**
     * @var string $urlPrefix
     */
    protected $urlPrefix = '';

    /**
     * @var string $filePrefix
     */
    protected $filePrefix = '';

    /**
     * Constructor
     *
     */
    public function __construct($fullName, $type, $conf, $args = array())
    {
        $this->type = $type;
        $this->conf = $conf;
        $this->args = $args;
        $this->fullName = $fullName;
        $this->default = $conf->default;
        $this->nl = isset($conf->appendNewLine) && $conf->appendNewLine === true ? "\n" : '';
    }

    /**
     * Return a value depending on what's set to `$this->default`, which is used as
     * the key
     *
     * @return string
     */
    public function __toString()
    {
        return isset($this->{$this->default})
            ? $this->{$this->default}
            : $this->markup;
    }

    /**
     * __get
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        if ($name && method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            return parent::__get($name);
        }
    }

    /**
     * __isset
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * __set
     *
     * @param  string $name
     * @param  mixed $value
     */
    public function __set($name, $value) {
        $methodName = 'set' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($value);
        } else {
            return parent::__set($name, $value);
        }
    }

    /**
     * setFullName
     *
     * @param string $fullName Examples:
     *                         '/some/abs/url/main.js'
     *                         '/some/abs/url/main'
     *                         'rel/url/main.js'
     *                         'rel/url/main'
     *                         'main.js'
     *                         'main'
     * @see __set
     * @return string
     */
    public function setFullName($fullName)
    {
        $this->_fullName = $fullName;

        $this->isRemote = preg_match(self::REMOTE_REGEX, $fullName) ? true : false;
        $this->isAbsolute = preg_match(self::ABSOLUTE_REGEX, $fullName) ? true : false;

        $pathPrefix = rtrim($this->conf->path, '/');
        $urlPrefix = rtrim($this->conf->url, '/');

        // either '.' or 'http://url'
        $filePrefix = pathinfo($fullName, PATHINFO_DIRNAME);

        // either '' or 'http://url'
        $filePrefix = $filePrefix === '.' ? '' : $filePrefix;

        if ($this->isAbsolute) {
            $filePrefix = ltrim($filePrefix, '/');
            $pathPrefix = $this->config->paths->root . $filePrefix;
            $urlPrefix = $this->config->urls->root . $filePrefix;
            $filePrefix = '';
        }

        $this->filename = pathinfo($fullName, PATHINFO_BASENAME);
        $this->pathPrefix = $pathPrefix;
        $this->urlPrefix = $urlPrefix;
        $this->filePrefix = $filePrefix;
    }

    /**
     * getPath
     *
     * @see __get
     * @return string
     */
    protected function getPath()
    {
        if (!$this->isRemote) {
            $filePrefix = $this->filePrefix ? "{$this->filePrefix}/" : '';
            $variationSubDir = $this->variationSubDir ? "{$this->variationSubDir}/" : '';
            return "{$this->pathPrefix}/{$filePrefix}{$variationSubDir}{$this->filename}";
        } else {
            return '';
        }
    }

    /**
     * setFilename
     *
     * @param string $filename
     * @see __set
     * @return string
     */
    protected function setFilename($filename)
    {
        // either 'js' or ''
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        // 'index'
        $name = pathinfo($filename, PATHINFO_FILENAME);

        if ($this->type !== self::TYPE_IMG && $ext !== $this->type) {
            $ext = $this->type;
            $name = pathinfo($filename, PATHINFO_BASENAME);
        }

        $this->ext = $ext;
        $this->name = $name;
    }

    /**
     * getDir
     *
     * @see __get
     * @return string
     */
    protected function getDir()
    {
        $filePrefix = $this->filePrefix ? "/{$this->filePrefix}" : '';
        $variationSubDir = $this->variationSubDir ? "/{$this->variationSubDir}" : '';
        return "{$this->pathPrefix}{$filePrefix}{$variationSubDir}";
    }

    /**
     * getUrl
     *
     * @see __get
     * @return string
     */
    protected function getUrl()
    {
        if (!$this->isRemote) {
            $filePrefix = $this->filePrefix ? "{$this->filePrefix}/" : '';
            $variationSubDir = $this->variationSubDir ? "{$this->variationSubDir}/" : '';
            return "{$this->urlPrefix}/{$filePrefix}{$variationSubDir}{$this->filename}{$this->param}";
        } else {
            return $this->_fullName;
        }

    }

    /**
     * getFilename
     *
     * @see __get
     * @return string
     */
    protected function getFilename()
    {

        if ($this->conf->min) {
            $formatted = utils\str::format($this->conf->minFormat, array(
                'file' => $this->name,
                'ext' => $this->ext,
            ));
            $file = "{$this->dir}/$formatted";

            if (file_exists($file)) {
                return "$formatted";
            }
        }

        return "{$this->name}.{$this->ext}";
    }

    /**
     * getParam
     *
     * @see __get
     * @return string
     */
    protected function getParam()
    {
        if (!$this->conf->versioning) return '';
        return utils\str::format($this->conf->versioningFormat, array(
            'version' => $this->version
        ));
    }
    
    /**
     * getContent
     *
     * @see __get
     * @return string
     */
    protected function getContent()
    {
        return file_exists($this->path) ? file_get_contents($this->path) : '';
    }

    /**
     * getMarkup
     *
     * @see __get
     * @return string
     */
    protected function getMarkup()
    {
        $markup = utils\str::format($this->conf->markup, array(
            'url' => $this->url,
            'path' => $this->path,
            'param' => $this->param,
            'version' => $this->version,
            'attrs' => $this->attrStr,
        ));
        $markup = utils\str::format($markup, $this->args);
        return "{$markup}{$this->nl}";
    }

    /**
     * get or set an attribute
     *
     * @param  string $key
     * @param  string $value
     * @return null|Asset
     */
    public function attr($key, $value = null)
    {
        if (is_null($value)) return isset($this->_attrs[$key]) ? $this->_attrs[$key] : null;

        $keys = explode('|', $key);
        foreach ($keys as $subkey) {
            $this->_attrs[$subkey] = $value;
        }

        return $this;
    }

    /**
     * get or set attributes
     *
     * @param  array $attrs
     * @param  string $value
     * @return array|Asset
     */
    public function attrs($attrs = null)
    {
        if (is_null($attrs) || !is_array($attrs)) return $this->_attrs;
        foreach ($attrs as $key => $value) {
            $this->attr($key, $value);
        }
        return $this;
    }

    /**
     * get the attributes string
     *
     * also mapped to $asset->attrStr
     *
     * @param  string $quote
     * @return string
     */
    public function getAttrStr($quote = '"')
    {
        $rtn = array();
        foreach ($this->_attrs as $key => $value) {
            $rtn[] = "$key={$quote}{$value}{$quote}";
        }
        return implode(' ', $rtn);
    }

    /**
     * Get the modfied unix timestamp of `$filePath`
     *
     * @param  string $filePath
     * @return int
     */
    protected function getVersion()
    {
        return file_exists($this->path) ? filemtime($this->path) : '';
    }
}
