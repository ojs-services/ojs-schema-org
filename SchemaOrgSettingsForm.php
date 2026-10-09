<?php

/**
 * @file plugins/generic/schemaOrg/SchemaOrgSettingsForm.php
 *
 * @class SchemaOrgSettingsForm
 *
 * @brief Settings form for the Schema.org plugin.
 */

namespace APP\plugins\generic\schemaOrg;

use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\form\validation\FormValidatorPost;
use PKP\form\validation\FormValidatorCSRF;

class SchemaOrgSettingsForm extends Form
{
    /** @var SchemaOrgPlugin */
    public $plugin;

    /** @var int */
    public $contextId;

    public function __construct($plugin, $contextId)
    {
        $this->plugin = $plugin;
        $this->contextId = $contextId;
        parent::__construct($plugin->getTemplateResource('settings.tpl'));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    public function initData()
    {
        $schemaType = $this->plugin->getSetting($this->contextId, 'schemaType');
        $this->setData('schemaType', $schemaType ?: 'ScholarlyArticle');
    }

    public function readInputData()
    {
        $this->readUserVars(['schemaType']);
    }

    public function validate($callHooks = true)
    {
        $schemaType = $this->getData('schemaType');
        $allowed = ['ScholarlyArticle', 'MedicalScholarlyArticle'];
        if (!in_array($schemaType, $allowed, true)) {
            $this->setData('schemaType', 'ScholarlyArticle');
        }
        return parent::validate($callHooks);
    }

    public function execute(...$functionArgs)
    {
        $this->plugin->updateSetting(
            $this->contextId,
            'schemaType',
            $this->getData('schemaType')
        );
        parent::execute(...$functionArgs);
    }

    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());
        $templateMgr->assign('schemaType', $this->getData('schemaType'));
        return parent::fetch($request, $template, $display);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\schemaOrg\SchemaOrgSettingsForm', '\SchemaOrgSettingsForm');
}
