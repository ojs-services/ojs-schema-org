# Changelog

## 1.0.3.2 (2026-10-09)

- Add upgrade guide for OJS 3.3 to 3.4/3.5 migration in README

## 1.0.3.1 (2026-10-09)

- Add FormValidatorPost and FormValidatorCSRF to settings form
- Fix settings.tpl ROUTE_COMPONENT to use class constant (PKP_STRICT_MODE safe)
- Narrow version.xml to tested OJS versions only (3.4.0.3–3.4.0.10, 3.5.0.3–3.5.0.5)

## 1.0.3.0 (2026-10-08)

- Port to OJS 3.4/3.5 with PSR-4 namespaces and Hook::add()
- Use doiObject (OJS 3.4/3.5 primary) with pub-id::doi fallback
- Use Application::ROUTE_PAGE instead of global constant
- Use publication locale instead of UI locale for all metadata fields
- Use publisher institution field with journal name fallback
- Strip HTML from abstract before output
- Flatten nested keyword arrays
- Encode JSON with HEX_TAG and HEX_AMP to prevent script injection
- Add duplicate JSON-LD guard
- Skip output on galley views
- Add @id and mainEntityOfPage fields
- Return early when application is under maintenance
- Validate schema type input server-side
