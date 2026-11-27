# Changelog

## 1.0.3.4 (2026-11-27)

- Fix contact domain (ojs-services.com)
- Use getStoredPubId('doi') as primary DOI access method

## 1.0.3.3 (2026-11-27)

- Add English fallback for missing locale files (getLocaleFilename)
- Add README_TR.md (Turkish)
- Use wildcard compatibility block (3.3.0.x)

## 1.0.3.2 (2026-11-14)

- Output datePublished as ISO 8601 datetime with timezone (date('c'))
- Add dateModified from publication lastModified field
- Skip date fields when value is empty or invalid

## 1.0.3.1 (2026-10-09)

- Add FormValidatorPost and FormValidatorCSRF to settings form
- Narrow version.xml to tested OJS versions only

## 1.0.3.0 (2026-10-08)

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

## 1.0.2.0 (2026-02-07)

- Updated plugin display name
- Improved bilingual support (English/Turkish)

## 1.0.1.0 (2026-02-06)

- Initial public release
- ScholarlyArticle and MedicalScholarlyArticle support
- Comprehensive metadata extraction
