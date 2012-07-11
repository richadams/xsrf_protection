<?php
if (!defined("__IN_SYMPHONY__")) die("You cannot directly access this file.");

// Dependencies
require_once(EXTENSIONS . "/xsrf_protection/XSRF.class.php");

class extension_xsrf_protection extends Extension
{
    // Ctor
    public function __construct()
    {
        parent::__construct();

        // Validate request passes XSRF checks if extension is enabled.
        $status = Symphony::ExtensionManager()->fetchStatus(array("handle" => "xsrf_protection"));
        if (in_array(EXTENSION_ENABLED, $status) || in_array(EXTENSION_REQUIRES_UPDATE, $status))
        {
            XSRF::validateRequest();
        }
    }

    // Event hooks for this extension
    public function getSubscribedDelegates()
    {
        return array(
            array(
                "page"     => "/backend/",
                "delegate" => "AdminPagePreGenerate",
                "callback" => "appendTokensToForms"
            ),
        );
    }

    // This modifies any forms on the page to add a XSRF token to it.
    public function appendTokensToForms($context)
    {
        if (isset($context["oPage"]))
        {
            $context["oPage"]->Form->prependChild(XSRF::formToken());
        }
    }
}
