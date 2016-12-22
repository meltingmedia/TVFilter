<?php

/**
 * A service to prevent saving @EVAL bindings for Revo TVs values
 *
 * To be used in a plugin invoked on event OnMODXInit (to allow modifying the posted data to the connector)
 * and event OnBeforeDocFormSave to optionally display some error message to the user
 */
class TVFilter
{
    /**
     * @var modX
     */
    protected $modx;
    /**
     * @var bool
     */
    protected $matches = false;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
    }

    /**
     * When modX is initialized, let's try to figure if the request is for a create/update resource processor
     *
     * @see modX::initialize
     */
    public function OnMODXInit()
    {
        // Most likely not a request for us
        if (!$this->isResourceSave()) {
            return;
        }

        $this->sanitize();
    }

    /**
     * Before trying to save the resource, check if we found some @EVAL stuff
     */
    public function OnBeforeDocFormSave()
    {
        if (!$this->matches) {
            return;
        }

        // @TODO lexicon + option to disable the warning in case the content has been filtered already (@EVAL removed)
        $msg = 'You are not allowed to use @EVAL bindings';
        $this->modx->event->output($msg);
    }

    /**
     * Analyzes the request parameters ($_POST) and try to do whatever it takes
     */
    protected function sanitize()
    {
        //$this->modx->getService('error', 'error.modError');
        $this->matches = false;

        foreach ($_POST as $k => $v) {
            if (!is_string($v)) {
                continue;
            }
            // @TODO only filter on keys being "tv{int}"
            if ($this->isEval($v)) {
                // @TODO Allow @EVAL only if default value
//                $id = str_replace('tv', '', $k);
//                /** @var modTemplateVar $tv */
//                $tv = $this->modx->getObject('modTemplateVar', ['id' => $id]);
//                if ($tv && $tv->get('default_text') === $v) {
//                    continue;
//                }

                $this->matches = true;
                // @TODO make filtering an option
                $_POST[$k] = $this->filter($v);
                $this->modx->log(modX::LOG_LEVEL_INFO, __METHOD__ . " Replacing '{$v}' with '{$_POST[$k]}' for TV {$k}");
            }
        }

        if ($this->matches) {
            // @TODO offer to lock/disable user + notify admin
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                __METHOD__ . " user {$this->modx->user->username} tried to use an @EVAL binding in a TV value"
            );

//            $this->disableUser();
//            $this->notifyAdmin();
        }
    }

    /**
     * Disable the currently logged user
     *
     * @TODO temporally block the user instead ?
     */
    protected function disableUser()
    {
        $user = $this->modx->user;
        $user->set('active', false);
        $saved = $user->save();

//        $this->modx->invokeEvent('OnUserDeactivate', [
//            'id' => $user->id,
//            'user' => &$user,
//            'mode' => modSystemEvent::MODE_UPD,
//        ]);

        return $saved;
    }

    /**
     * @TODO
     * Send a notification to the configured email(s)
     */
    protected function notifyAdmin()
    {
        //$mail = $this->modx->getService('mail', 'modMail');
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    protected function isEval($value)
    {
        return strpos($value, '@EVAL ') === 0;
    }

    /**
     * Strip the first 6 characters ("@EVAL ") from the given string
     *
     * @param string $value
     *
     * @return string
     */
    protected function filter($value)
    {
        return substr($value, 6);
    }

    /**
     * Check if the request is for some particular connectors we should not mess with (they appear to hold the "action" parameter)
     *
     * @return bool
     */
    protected function isSpecialConnector()
    {
        return in_array(
            $_SERVER['DOCUMENT_URI'],
            [
                MODX_CONNECTORS_URL . 'lang.js.php',
                MODX_CONNECTORS_URL . 'modx.config.js.php',
            ]
        );
    }

    /**
     * Check whether or not the current request is a create/update resource processor one
     *
     * @return bool
     */
    protected function isResourceSave()
    {
        if ($this->modx->context->key !== 'mgr' || empty($_POST)) {
            return false;
        }

        if ($this->isSpecialConnector()) {
            return false;
        }

        return in_array($_POST['action'], ['resource/update', 'resource/create']);
    }
}
