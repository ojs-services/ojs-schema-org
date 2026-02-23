<?php

/**
 * @file SchemaOrgPlugin.php
 *
 * @class SchemaOrgPlugin
 * @brief Plugin to add Schema.org structured data (JSON-LD) to OJS article pages.
 *
 * Outputs ScholarlyArticle or MedicalScholarlyArticle JSON-LD in the <head>
 * section of article landing pages. Does not interfere with Google Scholar
 * meta tags (citation_*).
 *
 * Compatible with OJS 3.3.x, 3.4.x, 3.5.x
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class SchemaOrgPlugin extends GenericPlugin {

    /**
     * @copydoc Plugin::register()
     */
    public function register($category, $path, $mainContextId = null) {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && $this->getEnabled($mainContextId)) {
            HookRegistry::register('TemplateManager::display', [$this, 'callbackDisplay']);
        }
        return $success;
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName() {
        return __('plugins.generic.schemaOrg.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription() {
        return __('plugins.generic.schemaOrg.description');
    }

    // ------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs) {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }
        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
        $linkAction = new \LinkAction(
            'settings',
            new \AjaxModal(
                $router->url($request, null, null, 'manage', null, [
                    'verb' => 'settings',
                    'plugin' => $this->getName(),
                    'category' => 'generic',
                ]),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );
        array_unshift($actions, $linkAction);
        return $actions;
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request) {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $this->import('SchemaOrgSettingsForm');
                $form = new \SchemaOrgSettingsForm($this, $request->getContext()->getId());
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new \JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }
                return new \JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }

    // ------------------------------------------------------------------
    // Hook callback
    // ------------------------------------------------------------------

    /**
     * Hook callback: inject JSON-LD into the <head> of article pages.
     *
     * Listens to TemplateManager::display so we can detect whether the
     * current page is an article landing page before injecting markup.
     *
     * @param string $hookName
     * @param array  $args
     * @return bool
     */
    public function callbackDisplay($hookName, $args) {
        $templateMgr = $args[0];
        $template     = $args[1] ?? '';

        // Only act on the article detail page template
        if (!$this->_isArticlePage($template)) {
            return false;
        }

        $request = \Application::get()->getRequest();
        $context = $request->getContext();
        if (!$context) {
            return false;
        }

        // Retrieve published submission from template variables
        $article     = $templateMgr->getTemplateVars('article');
        $publication = $templateMgr->getTemplateVars('publication') ?: ($article ? $article->getCurrentPublication() : null);
        $issue       = $templateMgr->getTemplateVars('issue');

        if (!$article || !$publication) {
            return false;
        }

        $jsonLd = $this->_buildJsonLd($article, $publication, $issue, $context, $request);
        if (!$jsonLd) {
            return false;
        }

        $scriptTag = '<script type="application/ld+json">' . json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
        $templateMgr->addHeader('schemaOrgJsonLd', $scriptTag);

        return false;
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    /**
     * Determine whether the current template corresponds to an article page.
     *
     * Covers template names across OJS 3.3 – 3.5.
     *
     * @param string $template
     * @return bool
     */
    private function _isArticlePage($template) {
        // Match article detail page templates across OJS 3.3 – 3.5 and custom themes.
        // Exclude galley views (e.g. PDF viewer).
        return strpos($template, 'article.tpl') !== false
            && strpos($template, 'galley') === false;
    }

    /**
     * Build the Schema.org JSON-LD array from OJS metadata.
     *
     * Uses only the objects already loaded by OJS (no extra DB queries).
     *
     * @param object $article     Submission object
     * @param object $publication Publication object
     * @param object $issue       Issue object (may be null)
     * @param object $context     Journal context
     * @param object $request     PKP Request
     * @return array|null
     */
    private function _buildJsonLd($article, $publication, $issue, $context, $request) {
        // AppLocale was deprecated in OJS 3.4; fall back gracefully.
        if (class_exists('\\APP\\i18n\\Locale')) {
            $locale = \APP\i18n\Locale::getLocale();
        } else {
            $locale = \AppLocale::getLocale();
        }

        // Schema type from plugin settings
        $schemaType = $this->getSetting($context->getId(), 'schemaType') ?: 'ScholarlyArticle';

        // Headline / title
        $title = $publication->getLocalizedTitle($locale);
        if (!$title) {
            $title = $publication->getLocalizedTitle();
        }
        if (!$title) {
            return null;
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type'    => $schemaType,
            'headline' => $title,
        ];

        // Abstract / description
        $abstract = $publication->getLocalizedData('abstract', $locale);
        if (!$abstract) {
            $abstract = $publication->getLocalizedData('abstract');
        }
        if ($abstract) {
            $jsonLd['description'] = strip_tags($abstract);
        }

        // Authors
        $authors = $this->_getAuthors($publication);
        if (!empty($authors)) {
            $jsonLd['author'] = $authors;
        }

        // Date published
        $datePublished = $publication->getData('datePublished');
        if ($datePublished) {
            $jsonLd['datePublished'] = $datePublished;
        }

        // DOI – try multiple access patterns for OJS 3.3 / 3.4 / 3.5
        $doi = $publication->getData('pub-id::doi');
        if (!$doi && method_exists($publication, 'getStoredPubId')) {
            $doi = $publication->getStoredPubId('doi');
        }
        if (!$doi) {
            $doi = $publication->getData('doiObject') ? $publication->getData('doiObject')->getData('doi') : null;
        }
        if ($doi) {
            $jsonLd['identifier'] = [
                '@type'        => 'PropertyValue',
                'propertyID'   => 'DOI',
                'value'        => $doi,
            ];
            $jsonLd['sameAs'] = 'https://doi.org/' . $doi;
        }

        // URL
        $dispatcher = $request->getDispatcher();
        $url = $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), 'article', 'view', [$article->getBestId()]);
        if ($url) {
            $jsonLd['url'] = $url;
        }

        // Publisher (journal)
        $publisherName = $context->getLocalizedName();
        if ($publisherName) {
            $jsonLd['publisher'] = [
                '@type' => 'Organization',
                'name'  => $publisherName,
            ];
        }

        // isPartOf – journal + issue/volume
        $isPartOf = $this->_buildIsPartOf($context, $issue, $publication);
        if ($isPartOf) {
            $jsonLd['isPartOf'] = $isPartOf;
        }

        // Keywords
        $keywords = $publication->getLocalizedData('keywords', $locale);
        if (!$keywords) {
            $keywords = $publication->getLocalizedData('keywords');
        }
        if (!empty($keywords)) {
            $jsonLd['keywords'] = implode(', ', $keywords);
        }

        // Page numbers
        $pages = $publication->getData('pages');
        if ($pages) {
            $jsonLd['pagination'] = $pages;
        }

        // Language
        $language = $publication->getData('locale');
        if ($language) {
            $jsonLd['inLanguage'] = str_replace('_', '-', $language);
        }

        // License / rights
        $licenseUrl = $publication->getData('licenseUrl');
        if (!$licenseUrl) {
            $licenseUrl = $context->getData('licenseUrl');
        }
        if ($licenseUrl) {
            $jsonLd['license'] = $licenseUrl;
        }

        return $jsonLd;
    }

    /**
     * Extract author information from a Publication object.
     *
     * @param object $publication
     * @return array
     */
    private function _getAuthors($publication) {
        $authors = [];

        // OJS 3.4+ uses getAuthors() on the publication
        if (method_exists($publication, 'getData') && $publication->getData('authors')) {
            $authorObjects = $publication->getData('authors');
        } else {
            return $authors;
        }

        foreach ($authorObjects as $author) {
            $authorData = [
                '@type' => 'Person',
            ];

            $givenName  = $author->getLocalizedGivenName();
            $familyName = $author->getLocalizedFamilyName();

            if ($givenName) {
                $authorData['givenName'] = $givenName;
            }
            if ($familyName) {
                $authorData['familyName'] = $familyName;
            }

            $fullName = trim(($givenName ?: '') . ' ' . ($familyName ?: ''));
            if ($fullName) {
                $authorData['name'] = $fullName;
            }

            // Affiliation
            $affiliation = $author->getLocalizedData('affiliation');
            if ($affiliation) {
                $authorData['affiliation'] = [
                    '@type' => 'Organization',
                    'name'  => $affiliation,
                ];
            }

            // ORCID
            $orcid = $author->getData('orcid');
            if ($orcid) {
                $authorData['sameAs'] = $orcid;
            }

            if (!empty($authorData['name'])) {
                $authors[] = $authorData;
            }
        }

        return $authors;
    }

    /**
     * Build the isPartOf structure (Journal > Issue).
     *
     * @param object      $context
     * @param object|null $issue
     * @param object      $publication
     * @return array|null
     */
    private function _buildIsPartOf($context, $issue, $publication) {
        $journalData = [
            '@type' => 'Periodical',
            'name'  => $context->getLocalizedName(),
        ];

        // ISSN
        $printIssn  = $context->getData('printIssn');
        $onlineIssn = $context->getData('onlineIssn');
        if ($onlineIssn) {
            $journalData['issn'] = $onlineIssn;
        } elseif ($printIssn) {
            $journalData['issn'] = $printIssn;
        }

        if (!$issue) {
            return $journalData;
        }

        $issueData = [
            '@type'    => 'PublicationIssue',
            'isPartOf' => $journalData,
        ];

        $issueNumber = $issue->getData('number');
        if ($issueNumber) {
            $issueData['issueNumber'] = $issueNumber;
        }

        $volume = $issue->getData('volume');
        if ($volume) {
            $volumeData = [
                '@type'        => 'PublicationVolume',
                'volumeNumber' => $volume,
                'isPartOf'     => $journalData,
            ];
            $issueData['isPartOf'] = $volumeData;
        }

        $datePublished = $issue->getData('datePublished');
        if ($datePublished) {
            $issueData['datePublished'] = $datePublished;
        }

        return $issueData;
    }
}
