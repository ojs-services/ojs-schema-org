# Release Notes — v1.0.3.0

**Date:** October 4, 2026

## Changes

### Security

- **`</script>` injection fix:** Replaced `JSON_UNESCAPED_SLASHES` with `JSON_HEX_TAG | JSON_HEX_AMP` to prevent abstract text containing `</script>` from breaking the page.

### Correctness

- **Locale source:** Switched from UI locale (`AppLocale::getLocale()`) to publication locale (`$publication->getData('locale')`). All localized fields (title, abstract, keywords, author names, affiliations) now consistently use the publication's content language, falling back to the journal's primary locale.
- **Abstract encoding:** Added `html_entity_decode()` before `strip_tags()` so HTML entities like `&amp;` are properly decoded. Added whitespace normalization.
- **Keywords "Array" bug:** Replaced `implode(', ', $keywords)` with `array_walk_recursive` flattening + array output. Prevents "Array" string on nested keyword structures (OJS 3.5.0.4+ data format) and outputs a proper schema.org array.
- **Publisher field:** Uses `$context->getData('publisherInstitution')` when available; falls back to journal name. Previously always used journal name, which was redundant with the `isPartOf` Periodical.
- **Date normalization:** `datePublished` is trimmed to `YYYY-MM-DD` (both article and issue dates) so the time portion from the database doesn't leak into the output.

### Schema.org Completeness

- **`@id`:** Added, set to the canonical article URL.
- **`mainEntityOfPage`:** Added as `WebPage` with `@id` pointing to the canonical URL. Recommended by Google's Article structured data guidelines.

### Robustness

- **Galley view detection:** Added `$templateMgr->getTemplateVars('galley')` check in addition to the template name check. In OJS 3.3 the galley viewer reuses `article.tpl`, so the name-based check alone was insufficient.
- **Duplicate ld+json guard:** Checks `additionalHeadData` for existing `application/ld+json` before injecting, preventing duplicate blocks when a theme or another plugin already outputs structured data.
- **OJS 3.5 protection:** All entry points bail silently when `function_exists('import')` is false. The plugin will not crash on OJS 3.5 even if present in the plugins directory.
- **Affiliation type guard:** Added `is_string()` check on affiliation data to prevent type errors if the field returns a non-string value.

### Compatibility

- **Removed untested OJS 3.4.x and 3.5.x from `version.xml`.** The code has fallback paths for 3.4 but these are not production tested. 3.5 requires a separate plugin version.
- **Rewrote `COMPATIBILITY.md`** to reflect actual test evidence rather than assumed compatibility.

## Upgrade from 1.0.2.x

Drop-in replacement. No settings migration needed.

---

## Previous Versions

### v1.0.2.1 (2026-02-23)
- Minor compatibility update

### v1.0.2.0 (2026-02-07)
- Branding update, documentation, bilingual locale support

### v1.0.1.0
- Initial release with ScholarlyArticle/MedicalScholarlyArticle support
