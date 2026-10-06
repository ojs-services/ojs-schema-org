# OJS Version Compatibility

**Plugin:** Scholarly Article Schema Plugin  
**Version:** 1.0.3.0  
**Date:** October 4, 2026

## Supported Versions

| OJS Version | Status | Evidence |
|-------------|--------|----------|
| 3.3.x | Tested | Production verified on OJS 3.3.0-17 |
| 3.4.x | Not declared | Code has 3.4 fallbacks but not production tested |
| 3.5.x | Not compatible | Requires separate branch (`import()` removed in 3.5) |

## OJS 3.3.x Details

Production tested. The plugin uses:

- `import()` for class loading
- `HookRegistry::register` for template hooks
- `AppLocale::getLocale()` (available, but not used for structured data — plugin uses `$publication->getData('locale')` instead)
- `$publication->getStoredPubId('doi')` for DOI retrieval
- `TemplateManager::display` hook (stable since OJS 2.x)

## OJS 3.4.x Notes

The code contains fallback paths for 3.4 changes (new Locale class, DOI object structure), but these paths have **not been production tested**. The plugin is not declared compatible with 3.4.x in `version.xml`.

## OJS 3.5.x Notes

OJS 3.5 replaces `import()` with PSR-4 autoloading and moves classes to namespaced equivalents (e.g. `\PKP\plugins\GenericPlugin`). This plugin version **cannot run on 3.5** — it silently returns `null` from `index.php` when `import()` is absent.

A separate 3.5-compatible version would need:
- PSR-4 class structure
- `\PKP\plugins\GenericPlugin` base class
- Updated settings form using 3.5 Form API

## Protection Against Accidental Installation

All entry points (`index.php`, `SchemaOrgPlugin.inc.php`, `SchemaOrgSettingsForm.inc.php`) check `function_exists('import')` and bail silently if absent. The plugin will not crash OJS 3.5 even if manually placed in the plugins directory.

---

**Last Updated:** October 4, 2026
