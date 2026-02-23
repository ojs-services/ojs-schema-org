<?php

/**
 * @file SchemaOrgSettingsForm.php
 *
 * @class SchemaOrgSettingsForm
 * @brief Settings form for the Schema.org plugin.
 *
 * Provides a radio-button selector for the Schema.org @type:
 *   - ScholarlyArticle  (default)
 *   - MedicalScholarlyArticle
 *
 * Settings are stored per journal (context-level).
 */

import('lib.pkp.classes.form.Form');

class SchemaOrgSettingsForm extends \Form {

    /** @var SchemaOrgPlugin */
    public $plugin;

    /** @var int */
    public $contextId;

    /**
     * Constructor.
     *
     * @param SchemaOrgPlugin $plugin
     * @param int             $contextId
     */
    public function __construct($plugin, $contextId) {
        $this->plugin    = $plugin;
        $this->contextId = $contextId;
        parent::__construct($plugin->getTemplateResource('settings.tpl'));
    }

    /**
     * Initialize form data from stored settings.
     */
    public function initData() {
        $schemaType = $this->plugin->getSetting($this->contextId, 'schemaType');
        $this->setData('schemaType', $schemaType ?: 'ScholarlyArticle');
    }

    /**
     * Read user input.
     */
    public function readInputData() {
        $this->readUserVars(['schemaType']);
    }

    /**
     * Validate form data.
     *
     * @return bool
     */
    public function validate($callHooks = true) {
        $schemaType = $this->getData('schemaType');
        $allowed = ['ScholarlyArticle', 'MedicalScholarlyArticle'];
        if (!in_array($schemaType, $allowed, true)) {
            $this->setData('schemaType', 'ScholarlyArticle');
        }
        return parent::validate($callHooks);
    }

    /**
     * Save settings.
     */
    public function execute(...$functionArgs) {
        $this->plugin->updateSetting(
            $this->contextId,
            'schemaType',
            $this->getData('schemaType')
        );
        parent::execute(...$functionArgs);
    }

    /**
     * Assign template variables for the settings form.
     *
     * @param PKPRequest $request
     * @param string     $template
     * @param bool       $display
     * @return string
     */
    public function fetch($request, $template = null, $display = false) {
        $templateMgr = \TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());
        $templateMgr->assign('schemaType', $this->getData('schemaType'));
        return parent::fetch($request, $template, $display);
    }
}
