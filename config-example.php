<?php

/**
 * put this in your /site/config.php and modifiy it to your liking
 */

$config->blick = array(
    'jsPath'              => $config->paths->templates . 'scripts',
    'jsUrl'               => $config->urls->templates . 'scripts',
    'jsMarkup'            => '<script src="{url}" type="text/javascript" charset="utf-8"></script>',
    'jsDefault'           => 'markup',
    'jsVersioning'        => false,
    'jsVersioningFormat'  => '?v={version}',
    'jsMin'               => false,
    'jsMinFormat'         => "{file}.min.{ext}",

    'cssPath'             => $config->paths->templates . 'styles',
    'cssUrl'              => $config->urls->templates . 'styles',
    'cssMarkup'           => '<link href="{url}" rel="stylesheet" type="text/css">',
    'cssDefault'          => 'markup',
    'cssVersioning'       => false,
    'cssVersioningFormat' => '?v={version}',
    'cssMin'              => false,
    'cssMinFormat'        => "{file}.min.{ext}",

    'imgPath'             => $config->paths->templates . 'img',
    'imgUrl'              => $config->urls->templates . 'img',
    'imgMarkup'           => '<img src="{url}" alt="{0}">',
    'imgDefault'          => 'markup',
    'imgVariations'       => array(),
    'imgVariationSubDir'  => '',
    'imgVersioning'       => false,
    'imgVersioningFormat' => '',
    'imgMin'              => false,
    'imgMinFormat'        => '',

    'appendNewLine'       => true,

    'autoloadAs' => 'blick'
);