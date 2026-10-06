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
 * Compatible with OJS 3.3.x
 */

if (!function_exists('import')) {
    return;
}
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
     */
    public function callbackDisplay($hookName, $args) {
        $templateMgr = $args[0];
        $template     = $args[1] ?? '';

        if (!$this->_isArticlePage($template)) {
            return false;
        }

        // Skip galley views — OJS 3.3 reuses article.tpl for inline galley viewer
        $galley = $templateMgr->getTemplateVars('galley');
        if ($galley) {
            return false;
        }

        $request = \Application::get()->getRequest();
        $context = $request->getContext();
        if (!$context) {
            return false;
        }

        $article     = $templateMgr->getTemplateVars('article');
        $publication = $templateMgr->getTemplateVars('publication') ?: ($article ? $article->getCurrentPublication() : null);
        $issue       = $templateMgr->getTemplateVars('issue');

        if (!$article || !$publication) {
            return false;
        }

        // Avoid duplicate ld+json from theme or another plugin
        $additionalHead = $templateMgr->getTemplateVars('additionalHeadData') ?? '';
        if (strpos($additionalHead, 'application/ld+json') !== false) {
            return false;
        }

        $jsonLd = $this->_buildJsonLd($article, $publication, $issue, $context, $request);
        if (!$jsonLd) {
            return false;
        }

        $encoded = json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT);
        $scriptTag = '<script type="application/ld+json">' . $encoded . '</script>';
        $templateMgr->addHeader('schemaOrgJsonLd', $scriptTag);

        return false;
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function _isArticlePage($template) {
        return strpos($template, 'article.tpl') !== false
            && strpos($template, 'galley') === false;
    }

    /**
     * Build the Schema.org JSON-LD array from OJS metadata.
     */
    private function _buildJsonLd($article, $publication, $issue, $context, $request) {
        // Publication locale = content language (not UI locale)
        $locale = $publication->getData('locale');
        if (!$locale) {
            $locale = $context->getData('primaryLocale');
        }

        $schemaType = $this->getSetting($context->getId(), 'schemaType') ?: 'ScholarlyArticle';

        // Title
        $title = $publication->getData('title', $locale);
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

        // Abstract — decode HTML entities, strip tags, normalize whitespace
        $abstract = $publication->getData('abstract', $locale);
        if (!$abstract) {
            $abstract = $publication->getLocalizedData('abstract');
        }
        if ($abstract) {
            $clean = html_entity_decode(strip_tags($abstract), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $clean = preg_replace('/\s+/', ' ', trim($clean));
            if ($clean !== '') {
                $jsonLd['description'] = $clean;
            }
        }

        // Authors
        $authors = $this->_getAuthors($publication, $locale);
        if (!empty($authors)) {
            $jsonLd['author'] = $authors;
        }

        // Date published — normalize to YYYY-MM-DD
        $datePublished = $publication->getData('datePublished');
        if ($datePublished) {
            $jsonLd['datePublished'] = substr($datePublished, 0, 10);
        }

        // DOI — multiple access patterns for OJS 3.3 / 3.4
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

        // URL + @id + mainEntityOfPage
        $dispatcher = $request->getDispatcher();
        $url = $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), 'article', 'view', [$article->getBestId()]);
        if ($url) {
            $jsonLd['url'] = $url;
            $jsonLd['@id'] = $url;
            $jsonLd['mainEntityOfPage'] = [
                '@type' => 'WebPage',
                '@id'   => $url,
            ];
        }

        // Publisher — prefer publisherInstitution, fall back to journal name
        $publisherName = $context->getData('publisherInstitution');
        if (!$publisherName) {
            $publisherName = $context->getLocalizedName();
        }
        if ($publisherName) {
            $jsonLd['publisher'] = [
                '@type' => 'Organization',
                'name'  => $publisherName,
            ];
        }

        // isPartOf — journal + issue/volume
        $isPartOf = $this->_buildIsPartOf($context, $issue, $publication);
        if ($isPartOf) {
            $jsonLd['isPartOf'] = $isPartOf;
        }

        // Keywords — flatten nested arrays, output as schema.org array
        $keywords = $publication->getData('keywords', $locale);
        if (!$keywords) {
            $keywords = $publication->getLocalizedData('keywords');
        }
        if (!empty($keywords) && is_array($keywords)) {
            $flat = [];
            array_walk_recursive($keywords, function ($v) use (&$flat) {
                if (is_string($v) && $v !== '') {
                    $flat[] = $v;
                }
            });
            if (!empty($flat)) {
                $jsonLd['keywords'] = $flat;
            }
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
     */
    private function _getAuthors($publication, $locale) {
        $authors = [];

        if (method_exists($publication, 'getData') && $publication->getData('authors')) {
            $authorObjects = $publication->getData('authors');
        } else {
            return $authors;
        }

        foreach ($authorObjects as $author) {
            $authorData = [
                '@type' => 'Person',
            ];

            // Use publication locale for author names
            $givenName = $author->getData('givenName', $locale);
            if (!$givenName) {
                $givenName = $author->getLocalizedGivenName();
            }
            $familyName = $author->getData('familyName', $locale);
            if (!$familyName) {
                $familyName = $author->getLocalizedFamilyName();
            }

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

            // Affiliation — use publication locale, fall back to any locale
            $affiliation = $author->getData('affiliation', $locale);
            if (!$affiliation) {
                $affiliation = $author->getLocalizedData('affiliation');
            }
            if ($affiliation && is_string($affiliation)) {
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
            $issueData['datePublished'] = substr($datePublished, 0, 10);
        }

        return $issueData;
    }
}
