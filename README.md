# Blick

---

#### for ProcessWire 2.5.11

## Setup

Just put the module in you modules directory and install it via admin.

## Intro

This module might come in handy if you like to keep your templates clean and free of unreadable and unmaintainable string concatenations and even free of any logic. It also comes with some handy features besides just embedding `JS`, `CSS` and `image` assets, see below.

**Yikes!**

```php
<link href="<?php echo $config->urls->templates . 'styles/foo.css'; ?>">
<link href="<?php echo $config->urls->templates . 'styles/bar.css'; ?>">
<script src="<?php echo $config->urls->templates . 'scripts/foo.js'; ?>"></script>
<script src="<?php echo $config->urls->templates . 'scripts/bar.js'; ?>"></script>
<img src="<?php echo $config->urls->templates . 'images/sky-scraper.jpg'; ?>" alt="Some huge building">
<img src="<?php echo $config->urls->templates . 'images/owzim.jpg'; ?>" alt="Handsome!">
```

**Way cleaner**

```php
<?php echo $asset->css('foo'); ?>
<?php echo $asset->js('foo'); ?>
<?php echo $asset->img('sky-scraper.jpg', 'Some huge building'); ?>
```
or with short syntax
```php
<?= $asset->css('bar') ?>
<?= $asset->js('bar') ?>
<?= $asset->img('owzim.jpg', 'Handsome!') ?>
```

**And prettier if you're using Twig**

```twig
{{ asset.css('foo') }}
{{ asset.css('bar') }}
{{ asset.js('foo') }}
{{ asset.js('bar') }}
{{ asset.img('sky-scraper.jpg', 'Some huge building') }}
{{ asset.img('owzim.jpg', 'Handsome!') }}
```

## Usage

### JS example

Let's use the `js` method an its configuration as an example, and assume we have the following files located in `/site/templates/scripts`

```
- index.js
- index.min.js
- main.js
```

```php
$config->blick = array(
    'jsPath'             => $config->paths->templates . 'scripts',
    'jsUrl'              => $config->urls->templates . 'scripts',
    'jsMarkup'           => '<script src="{url}"></script>',
    'jsDefault'          => 'markup',
    'jsVersioning'       => true,
    'jsVersioningFormat' => '?v={version}',
    'jsMin'              => true,
    'jsMinFormat'        => "{file}.min.{ext}",
);

```

```php
$asset = $modules->get('Blick');

$asset->js('index')->url;
// returns /site/templates/scripts/index.min.js?v=1426170460935
// 'min' and version parameter added, which was fetched from the file modified date

$asset->js('main')->url;
// returns /site/templates/scripts/main.js?v=1426170460935
// without 'min', because there is no main.min.js

$asset->js('main');
// returns <script src="/site/templates/scripts/main.js"></script>
// because 'jsDefault' is set to 'markup'
// you can also access it explicitly via $asset->js('main')->markup

$asset->js('http://code.jquery.com/jquery-2.1.3.js');
// returns <script src="http://code.jquery.com/jquery-2.1.3.js"></script>
// nothing is modified here, because it's a remote url

```

* You can use the file name with or without extension.
* Adding a version parameter only takes place, if `jsVersioning` is set to `true`, it's a local file and it exists.
* Modifying the file name to include **min** only takes place, if `jsMin` is set to `true`, it's a local file and it exists.

The same applies for the `$asset->css('file')` method:

```php
$config->blick = array(
    'cssPath' => $config->paths->templates . 'styles',
    'cssUrl'  => $config->urls->templates . 'styles',
    // and so on ...
);
```

### IMG example

the `img` method lets you include images, crop and resize them, without them having to be a page image.

```php
$config->blick = array(
    'imgPath'             => $config->paths->templates . 'images',
    'imgUrl'              => $config->urls->templates . 'images',
    'imgMarkup'           => '<img {attrs} src="{url}" alt="{0}">',
    'imgDefault'          => 'markup',
    'imgVariationSubDir'  => 'variations',
);
```

```php
$asset = $modules->get('Blick');

$asset->img('sky-scraper.jpg')->url;
// returns /site/templates/images/sky-scraper.jpg

$asset->img('sky-scraper.jpg', 'Some huge building');
// returns <img src="/site/templates/images/sky-scraper.jpg" alt="Some huge building">
// any arguments following the filename are passed as an array
// in this case the alt value is the 0th argument, so {0} get's replaced
// you can set as many arguments as you want in 'imgMarkup'

$asset->img('sky-scraper.jpg')->size(100, 100)->url;
// returns /site/templates/images/variations/sky-scraper.100x100.jpg
// the resized image is put into a subdir 'variations' as configured in 'imgVariationSubDir'
// if 'imgVariationSubDir' is left empty, the variation will be put in the same directory

$asset->img('sky-scraper.jpg', 'Some huge building')->attr('title', 'Some huge building');
// returns <img title="Some huge building" src="/site/templates/images/sky-scraper.jpg" alt="Some huge building">
// the resized image is put into a subdir 'variations' as configured in 'imgVariationSubDir'
// if 'imgVariationSubDir' is left empty, the variation will be put in the same directory
```


You can also setup predefined variation settings in `imgVariations`

```php
$config->blick = array(
    'imgVariations'  => array(
        'header' => array(
             'width' => 960,
             'height' => 360,
             'options' => array(
                 'suffix' => 'header',
             ),
         ),
        'person' => array(
             // and so on ...
         ),
    ),
);
```
And call it like so:

```php
$asset->img('sky-scraper.jpg')->getVariation('header')->url;
// returns /site/templates/images/variations/sky-scraper.960x360-header.jpg
```

#### Attributes example

Since version `0.4.0` you don't need to create arbitrary variable placeholders, if you want to use attributes only. Now you can use the `{attrs}` placeholder and set the attributes via `$asset->attr('name', 'value')`. The name argument can also be multiple names, split by a pipe `|`.

```php
$config->blick = array(
    // ...
    'imgMarkup' => '<img {attrs} src="{url}">',
    // ...
);

$asset->img('sky-scraper.jpg')->attr('alt|title', 'Some huge building');
// returns <img alt="Some huge building" title="Some huge building" src="/site/templates/images/sky-scraper.jpg" >
```

### Using files that are not in the configured directory

If you want to include files, that are neither in the configured directory nor in one of its sub directores, just use an absolute path (actually, relative to your `/site` directory.

```php
$asset->js($config->urls->SomeModule . 'scripts/file-in-root');
```


### Autoload the module

If you don't want to include the module manually via

```php
$assets = $modules->get('Blick');
```

you can set it to be autoloaded under a custom name:

```php
$config->blick['autoloadAs'] = 'fiddle';
```

Now it becomes automatically available in your templates under the name `fiddle`

```php
$fiddle->css('foo');
$fiddle->js('foo');
$fiddle->img('baz.png', 'qux');
```
Please note, that, if you're using the  **TemplateTwigReplace**.module you will have to add your chosen autoload name to the `Auto-import fuel` list on the module's config page.

See `config-example.php` for all configurable settings.

### Change Log

* **0.4.0** add possibility to get/set and render attributes (see section **Attributes example**)
* **0.3.0** add `$asset->variant('name')` alias for `$asset->getVariation('name')`
* **0.2.0** fixes and internal refactorings
* **0.1.0** initial version