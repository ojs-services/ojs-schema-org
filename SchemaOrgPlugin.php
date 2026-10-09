<?php

/**
 * @file plugins/generic/schemaOrg/SchemaOrgPlugin.php
 *
 * @class SchemaOrgPlugin
 *
 * @brief Plugin to add Schema.org structured data (JSON-LD) to OJS article pages.
 *
 * Compatible with OJS 3.4.x and 3.5.x
 */

namespace APP\plugins\generic\schemaOrg;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class SchemaOrgPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return true;
        }
        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('TemplateManager::display', [$this, 'callbackDisplay']);
        }
        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.schemaOrg.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.schemaOrg.description');
    }

    // ------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------

    public function getActions($request, $actionArgs)
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }
        $router = $request->getRouter();
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
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

    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $form = new SchemaOrgSettingsForm($this, $request->getContext()->getId());
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }
                return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }

    // ------------------------------------------------------------------
    // Hook callback
    // ------------------------------------------------------------------

    public function callbackDisplay(string $hookName, array $args): bool
    {
        $templateMgr = $args[0];
        $template = $args[1] ?? '';

        if (!$this->_isArticlePage($template)) {
            return false;
        }

        $galley = $templateMgr->getTemplateVars('galley');
        if ($galley) {
            return false;
        }

        $request = Application::get()->getRequest();
        $context = $request->getContext();
        if (!$context) {
            return false;
        }

        $article = $templateMgr->getTemplateVars('article');
        $publication = $templateMgr->getTemplateVars('publication')
            ?: ($article ? $article->getCurrentPublication() : null);
        $issue = $templateMgr->getTemplateVars('issue');

        if (!$article || !$publication) {
            return false;
        }

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

    private function _isArticlePage(string $template): bool
    {
        return strpos($template, 'article.tpl') !== false
            && strpos($template, 'galley') === false;
    }

    private function _buildJsonLd($article, $publication, $issue, $context, $request): ?array
    {
        $locale = $publication->getData('locale');
        if (!$locale) {
            $locale = $context->getData('primaryLocale');
        }

        $schemaType = $this->getSetting($context->getId(), 'schemaType') ?: 'ScholarlyArticle';

        $title = $publication->getData('title', $locale);
        if (!$title) {
            $title = $publication->getLocalizedTitle();
        }
        if (!$title) {
            return null;
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'headline' => $title,
        ];

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

        $authors = $this->_getAuthors($publication, $locale);
        if (!empty($authors)) {
            $jsonLd['author'] = $authors;
        }

        $datePublished = $publication->getData('datePublished');
        if ($datePublished) {
            $jsonLd['datePublished'] = substr($datePublished, 0, 10);
        }

        // DOI
        $doi = null;
        $doiObject = $publication->getData('doiObject');
        if ($doiObject) {
            $doi = $doiObject->getData('doi');
        }
        if (!$doi) {
            $doi = $publication->getData('pub-id::doi');
        }
        if ($doi) {
            $jsonLd['identifier'] = [
                '@type' => 'PropertyValue',
                'propertyID' => 'DOI',
                'value' => $doi,
            ];
            $jsonLd['sameAs'] = 'https://doi.org/' . $doi;
        }

        // URL + @id + mainEntityOfPage
        $dispatcher = $request->getDispatcher();
        $url = $dispatcher->url($request, Application::ROUTE_PAGE, $context->getPath(), 'article', 'view', [$article->getBestId()]);
        if ($url) {
            $jsonLd['url'] = $url;
            $jsonLd['@id'] = $url;
            $jsonLd['mainEntityOfPage'] = [
                '@type' => 'WebPage',
                '@id' => $url,
            ];
        }

        $publisherName = $context->getData('publisherInstitution');
        if (!$publisherName) {
            $publisherName = $context->getLocalizedName();
        }
        if ($publisherName) {
            $jsonLd['publisher'] = [
                '@type' => 'Organization',
                'name' => $publisherName,
            ];
        }

        $isPartOf = $this->_buildIsPartOf($context, $issue, $publication);
        if ($isPartOf) {
            $jsonLd['isPartOf'] = $isPartOf;
        }

        // Keywords
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

        $pages = $publication->getData('pages');
        if ($pages) {
            $jsonLd['pagination'] = $pages;
        }

        $language = $publication->getData('locale');
        if ($language) {
            $jsonLd['inLanguage'] = str_replace('_', '-', $language);
        }

        $licenseUrl = $publication->getData('licenseUrl');
        if (!$licenseUrl) {
            $licenseUrl = $context->getData('licenseUrl');
        }
        if ($licenseUrl) {
            $jsonLd['license'] = $licenseUrl;
        }

        return $jsonLd;
    }

    private function _getAuthors($publication, string $locale): array
    {
        $authors = [];

        $authorObjects = $publication->getData('authors');
        if (empty($authorObjects)) {
            return $authors;
        }

        foreach ($authorObjects as $author) {
            $authorData = [
                '@type' => 'Person',
            ];

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

            $affiliation = $author->getData('affiliation', $locale);
            if (!$affiliation) {
                $affiliation = $author->getLocalizedData('affiliation');
            }
            if ($affiliation && is_string($affiliation)) {
                $authorData['affiliation'] = [
                    '@type' => 'Organization',
                    'name' => $affiliation,
                ];
            }

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

    private function _buildIsPartOf($context, $issue, $publication): ?array
    {
        $journalData = [
            '@type' => 'Periodical',
            'name' => $context->getLocalizedName(),
        ];

        $printIssn = $context->getData('printIssn');
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
            '@type' => 'PublicationIssue',
            'isPartOf' => $journalData,
        ];

        $issueNumber = $issue->getData('number');
        if ($issueNumber) {
            $issueData['issueNumber'] = $issueNumber;
        }

        $volume = $issue->getData('volume');
        if ($volume) {
            $volumeData = [
                '@type' => 'PublicationVolume',
                'volumeNumber' => $volume,
                'isPartOf' => $journalData,
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

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\schemaOrg\SchemaOrgPlugin', '\SchemaOrgPlugin');
}
