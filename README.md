# TV Filter

> TV Filter is a MODX Revolution component preventing content editors to save TVs with `@EVAL ` values (see https://docs.modx.com/revolution/2.x/making-sites-with-modx/customizing-content/template-variables/bindings/eval-binding)


## Requirements

* MODX Revolution (tested with 2.5.2 but should work with lower versions too)
* PHP 5.4+


## Manual Installation

* move the content of this repository in your MODX `core` folder
* create a plugin with the content from the `core/components/tvfilter/elements/plugin/plugin.php` file (you might make use of static elements), attached to `OnMODXInit` & `OnBeforeDocFormSave` events
* edit/create a resource, and try to save it with a TV having `EVAL echo 'test failed';` as value (you should be prevented to do so)


## Roadmap/ideas

* create a build script to ease the installation
* see `@TODO` notes in `core/components/tvfilter/tfilter.class.php`
