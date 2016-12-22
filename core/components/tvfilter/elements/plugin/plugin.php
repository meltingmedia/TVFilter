<?php
/**
 * A plugin to prevent saving @EVAL values in TVs
 *
 * @event OnMODXInit
 * @event OnBeforeDocFormSave
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var modPlugin $this
 *
 * @see modPlugin::process
 */

$path = $modx->getOption('tvfilter.core_path', null, MODX_CORE_PATH. 'components/tvfilter/');
/** @var TVFilter $service */
$service = $modx->getService('tvfilter', 'TVFilter', $path);

if (method_exists($service, $modx->event->name)) {
    return $service->{$modx->event->name}();
}

return;
