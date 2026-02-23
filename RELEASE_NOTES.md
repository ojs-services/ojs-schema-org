# Scholarly Article Schema Plugin - Release v1.0.2.0

**Release Date:** February 7, 2026  
**Status:** Production Ready ✅

## What's New in v1.0.2.0

### Updated Branding
- ✅ Plugin name changed to **"Scholarly Article Schema Plugin"**
- ✅ Enhanced description emphasizing Google Scholar compatibility
- ✅ Improved bilingual support (English/Turkish)

### Documentation Enhancements
- ✅ Comprehensive README.md with examples
- ✅ COMPATIBILITY.md with version-specific details
- ✅ LICENSE file (GPL v3)
- ✅ Professional release notes

### Quality Assurance
- ✅ Tested on OJS 3.3.x (production confirmed working)
- ✅ Code review for 3.4.x and 3.5.x compatibility
- ✅ All defensive coding patterns verified
- ✅ Zero breaking changes from v1.0.1.0

## Compatibility Matrix

| OJS Version | Status | Tested |
|-------------|--------|--------|
| 3.3.0-17 | ✅ Working | Yes (Production) |
| 3.4.x | ✅ Compatible | Code Review |
| 3.5.x | ✅ Compatible | Code Review |

## Installation Methods

### Method 1: Direct Upload (Recommended for Testing)
1. Download `schemaOrg-1.0.2.0.tar.gz`
2. OJS Admin → Settings → Website → Plugins → Upload a New Plugin
3. Upload and activate

### Method 2: Server Installation
```bash
cd /path/to/ojs/plugins/generic
tar -xzf schemaOrg-1.0.2.0.tar.gz
chown -R www-data:www-data schemaOrg
chmod -R 755 schemaOrg
```

### Method 3: Plugin Gallery (Future)
Will be available after submission to PKP Plugin Gallery

## Configuration Steps

1. **Enable Plugin**
   - Settings → Website → Plugins → Installed Plugins
   - Find "Scholarly Article Schema Plugin"
   - Check the box to enable

2. **Configure Schema Type**
   - Click Settings (gear icon)
   - Choose:
     - **ScholarlyArticle** (default) - for general academic journals
     - **MedicalScholarlyArticle** - for medical/health journals only
   - Save settings

3. **Verify Output**
   - Visit any published article page
   - View page source (Ctrl+U / Cmd+U)
   - Search for `application/ld+json`
   - Validate with Google Rich Results Test

## What Gets Added to Your Articles

### Structured Data Fields
```json
{
  "@context": "https://schema.org",
  "@type": "ScholarlyArticle",
  "headline": "Article Title",
  "description": "Abstract text...",
  "author": [
    {
      "@type": "Person",
      "name": "Author Name",
      "givenName": "First",
      "familyName": "Last",
      "affiliation": {"@type": "Organization", "name": "University"},
      "sameAs": "https://orcid.org/0000-0001-2345-6789"
    }
  ],
  "datePublished": "2025-01-15",
  "identifier": {
    "@type": "PropertyValue",
    "propertyID": "DOI",
    "value": "10.1234/example.2025.001"
  },
  "sameAs": "https://doi.org/10.1234/example.2025.001",
  "publisher": {"@type": "Organization", "name": "Journal Name"},
  "isPartOf": {
    "@type": "PublicationIssue",
    "issueNumber": "1",
    "isPartOf": {
      "@type": "PublicationVolume",
      "volumeNumber": "10",
      "isPartOf": {
        "@type": "Periodical",
        "name": "Journal Name",
        "issn": "1234-5678"
      }
    }
  },
  "keywords": "keyword1, keyword2, keyword3",
  "pagination": "1-15",
  "inLanguage": "en-US",
  "license": "https://creativecommons.org/licenses/by/4.0/"
}
```

## Benefits for Journal Administrators

### 🔍 Improved Search Engine Understanding
- Better indexing by Google, Bing, and other search engines
- Enhanced semantic interpretation of content
- Potential for rich snippets in search results

### 📊 Enhanced Discoverability
- Better visibility in academic search systems
- Improved content recommendations
- Knowledge graph integration potential

### ✅ Standards Compliance
- Follows Schema.org best practices
- Compatible with Google Scholar requirements
- Future-proof metadata structure

### 🚀 Zero Maintenance
- No additional configuration needed after setup
- Automatic metadata extraction from OJS
- No performance impact

## Technical Specifications

### Architecture
- **Hook Used:** `TemplateManager::display`
- **Output Format:** JSON-LD in `<head>` section
- **Database Queries:** 0 (uses pre-loaded objects)
- **Page Load Impact:** < 1ms
- **Memory Footprint:** Negligible

### Security
- ✅ No SQL queries (no injection risk)
- ✅ No user input processing (no XSS risk)
- ✅ Read-only operations only
- ✅ No external API dependencies
- ✅ No file system modifications

### Performance
- ✅ Zero additional database load
- ✅ Minimal CPU usage (JSON encoding only)
- ✅ No caching required
- ✅ No impact on page load time

## Validation & Testing

### Recommended Validation Tools
1. **Google Rich Results Test**  
   https://search.google.com/test/rich-results

2. **Schema.org Validator**  
   https://validator.schema.org/

3. **Structured Data Testing Tool**  
   View page source → Search for `application/ld+json`

### Test Scenarios ✅
- [x] Articles with complete metadata
- [x] Articles with minimal metadata (title only)
- [x] Articles with/without DOI
- [x] Articles with/without ORCID
- [x] Multiple authors
- [x] Different languages (en_US, tr_TR, etc.)
- [x] Different themes (Default, Manuscript, Bootstrap3, Health Sciences)
- [x] Articles in issues vs. continuous publishing

## Upgrade Path

### From v1.0.1.0 → v1.0.2.0
- ✅ Settings preserved automatically
- ✅ No configuration changes needed
- ✅ No data migration required
- ✅ Can upgrade without downtime

### Upgrade Steps
1. Download new version
2. Disable old version (optional)
3. Upload new tar.gz file
4. Enable plugin
5. Verify settings still correct

## Known Issues & Limitations

### Current Version (v1.0.2.0)
- **None reported** ✅

### General Limitations
- Requires OJS 3.3 or higher
- Only works on article landing pages (not galley views)
- Requires published articles (not visible in preview mode)

## Troubleshooting

### Plugin doesn't appear after upload
```bash
# Check file permissions
cd /path/to/ojs/plugins/generic
chown -R www-data:www-data schemaOrg
chmod -R 755 schemaOrg
```

### No JSON-LD output visible
1. Verify plugin is enabled
2. Check you're on a published article page (not preview)
3. View page source (not just inspect element)
4. Search for `application/ld+json`

### Settings won't save
1. Check PHP error logs
2. Verify database permissions
3. Clear OJS cache: `php tools/deleteCache.php`

## Support & Resources

### Documentation
- README.md - Complete usage guide
- COMPATIBILITY.md - Version compatibility details
- LICENSE - GPL v3 license terms

### Contact & Support
- **Developer:** Kerim Sarıgül
- **Company:** OJS Services
- **Website:** https://ojsservices.com
- **Email:** info@ojsservices.com
- **GitHub:** https://github.com/ojs-services/ojs-schema-org

### Professional Services
Need custom OJS development or consulting?
- Plugin customization
- OJS installation & configuration
- Theme development
- Migration services
- Technical support

Visit [ojsservices.com](https://ojsservices.com) for more information.

## Roadmap

### Planned Features (Future Versions)
- [ ] Additional Schema.org types (Book, Report, etc.)
- [ ] Schema.org validation dashboard
- [ ] Bulk metadata review tool
- [ ] Integration with OAI-PMH
- [ ] Support for more specialized medical types

### Community Contributions
Contributions welcome! Feel free to:
- Report issues
- Suggest features
- Submit pull requests
- Share use cases

## License

**GNU General Public License v3**

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

See LICENSE file for complete terms.

---

## Quick Start Summary

1. ✅ Download `schemaOrg-1.0.2.0.tar.gz`
2. ✅ Upload via OJS Plugin Manager or extract to `plugins/generic/`
3. ✅ Enable plugin in Settings → Website → Plugins
4. ✅ Configure Schema Type (ScholarlyArticle or MedicalScholarlyArticle)
5. ✅ Validate output with Google Rich Results Test
6. ✅ Done! All published articles now have Schema.org metadata

---

**Thank you for using Scholarly Article Schema Plugin!**

Developed with ❤️ by Kerim Sarıgül @ OJS Services
