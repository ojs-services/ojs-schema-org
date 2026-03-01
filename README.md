# Scholarly Article Schema Plugin

**Version:** 1.0.2.1
**Author:** Kerim Sarıgül (OJS Services)  
**Compatibility:** OJS 3.3.x, 3.4.x, 3.5.x  
**License:** GPL v3

## Overview

The Scholarly Article Schema Plugin adds Schema.org structured data (ScholarlyArticle or MedicalScholarlyArticle) to article landing pages using JSON-LD format. This improves semantic indexing, search engine understanding, and academic content discovery while remaining fully compatible with existing Google Scholar meta tags.

## Key Features

- **Schema.org JSON-LD Output**: Automatically generates structured data for every published article
- **Dual Schema Support**: Choose between ScholarlyArticle (general) or MedicalScholarlyArticle (medical journals)
- **Rich Metadata**: Includes authors, affiliations, ORCID, DOI, keywords, abstracts, publication dates, and more
- **Google Scholar Compatible**: Works alongside existing citation meta tags without conflicts
- **Version Compatibility**: Supports OJS 3.3, 3.4, and 3.5 with unified codebase
- **Zero Performance Impact**: No additional database queries; uses already-loaded OJS objects
- **Cross-Theme Compatible**: Works with all OJS themes

## What is Schema.org?

Schema.org is a collaborative vocabulary that helps search engines and other systems better understand web content. By adding Schema.org structured data to your article pages, you enable:

- Better search engine understanding and indexing
- Enhanced display in search results (rich snippets)
- Improved discoverability in academic search systems
- Semantic web integration and knowledge graph connectivity

## Installation

### Method 1: Plugin Gallery (Recommended)
1. Login as Journal Manager
2. Navigate to **Settings → Website → Plugins → Plugin Gallery**
3. Search for "Scholarly Article Schema"
4. Click **Install**

### Method 2: Manual Upload
1. Download the latest `schemaOrg-x.x.x.x.tar.gz` from releases
2. Login as Journal Manager
3. Navigate to **Settings → Website → Plugins → Upload a New Plugin**
4. Upload the tar.gz file
5. Click **Save**

### Method 3: Server Installation
```bash
cd plugins/generic
tar -xzf schemaOrg-1.0.2.0.tar.gz
chown -R www-data:www-data schemaOrg
chmod -R 755 schemaOrg
```

## Configuration

1. Navigate to **Settings → Website → Plugins → Installed Plugins**
2. Find **Scholarly Article Schema Plugin** and enable it
3. Click **Settings** (gear icon)
4. Select your Schema Type:
   - **ScholarlyArticle** (default): For general academic journals
   - **MedicalScholarlyArticle**: For medical and health science journals only
5. Click **OK** to save

## What Gets Included

The plugin automatically extracts and includes the following metadata when available:

### Core Fields
- **@context**: https://schema.org
- **@type**: ScholarlyArticle or MedicalScholarlyArticle
- **headline**: Article title
- **description**: Abstract (HTML stripped)
- **url**: Article landing page URL
- **datePublished**: Publication date

### Author Information
- **author**: Array of Person objects with:
  - name, givenName, familyName
  - affiliation (Organization)
  - sameAs (ORCID URL)

### Identifiers
- **identifier**: DOI as PropertyValue
- **sameAs**: DOI URL (https://doi.org/...)

### Publication Context
- **publisher**: Journal name (Organization)
- **isPartOf**: Nested structure of Periodical → PublicationVolume → PublicationIssue
- **pagination**: Page numbers
- **keywords**: Comma-separated keywords
- **inLanguage**: Publication language (e.g., en-US)
- **license**: License URL

### Journal Information
- **issn**: Online or print ISSN
- **volumeNumber**: Volume number
- **issueNumber**: Issue number
- **datePublished**: Issue publication date

## Example Output

```json
{
  "@context": "https://schema.org",
  "@type": "ScholarlyArticle",
  "headline": "Machine Learning Applications in Medical Diagnosis",
  "description": "This study explores the application of machine learning...",
  "author": [
    {
      "@type": "Person",
      "name": "Jane Smith",
      "givenName": "Jane",
      "familyName": "Smith",
      "affiliation": {
        "@type": "Organization",
        "name": "University of Example"
      },
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
  "url": "https://journal.example.org/article/view/123",
  "publisher": {
    "@type": "Organization",
    "name": "Example Medical Journal"
  },
  "isPartOf": {
    "@type": "PublicationIssue",
    "issueNumber": "1",
    "datePublished": "2025-01-01",
    "isPartOf": {
      "@type": "PublicationVolume",
      "volumeNumber": "10",
      "isPartOf": {
        "@type": "Periodical",
        "name": "Example Medical Journal",
        "issn": "1234-5678"
      }
    }
  },
  "keywords": "machine learning, medical diagnosis, artificial intelligence",
  "pagination": "1-15",
  "inLanguage": "en-US",
  "license": "https://creativecommons.org/licenses/by/4.0/"
}
```

## Compatibility Details

### OJS 3.3.x
- Fully tested and working
- Uses traditional AppLocale methods
- Supports all standard metadata fields

### OJS 3.4.x
- Fully compatible with new Locale class
- Supports updated DOI object structure
- Works with all themes

### OJS 3.5.x
- Forward compatible
- Tested with latest API changes
- Seamless upgrade path from 3.3/3.4

## Google Scholar Compatibility

This plugin **does not interfere** with Google Scholar indexing. It works alongside existing meta tags:

- Uses separate JSON-LD format (not meta tags)
- Inserted via different template hook
- Google Scholar continues to read citation_* meta tags
- Schema.org data provides additional semantic context

## Technical Details

- **Hook Used**: `TemplateManager::display`
- **Performance**: Zero additional database queries
- **Output Format**: JSON-LD in `<script type="application/ld+json">`
- **Injection Point**: `<head>` section of article pages only
- **Template Detection**: Matches `article.tpl` across all themes
- **Encoding**: UTF-8 with proper escaping (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)

## Troubleshooting

### Plugin doesn't appear in Plugin Gallery
- Check your OJS version (3.3+ required)
- Verify internet connectivity for Plugin Gallery access
- Try manual upload method

### No JSON-LD output visible
- Ensure plugin is enabled
- Check that you're viewing a published article page
- View page source and search for `application/ld+json`
- Verify article has required metadata (title at minimum)

### Settings not saving
- Check file permissions: `chmod -R 755 schemaOrg`
- Verify PHP error logs
- Clear OJS cache

### Validation errors
- Use Google's [Rich Results Test](https://search.google.com/test/rich-results)
- Use [Schema.org Validator](https://validator.schema.org/)
- Check that required fields (headline, author, datePublished) are present

## Testing & Validation

After installation, validate your Schema.org output:

1. **Google Rich Results Test**  
   https://search.google.com/test/rich-results  
   Enter your article URL to see how Google interprets the data

2. **Schema.org Validator**  
   https://validator.schema.org/  
   Paste your article URL or JSON-LD code

3. **View Source**  
   Right-click article page → View Page Source → Search for `application/ld+json`

## Support & Development

- **Developer**: Kerim Sarıgül
- **Website**: https://ojs-services.com
- **GitHub**: https://github.com/ojs-services/ojs-schema-org
- **Email**: info@ojs-services.com

## License

GPL v3 - See LICENSE file for details

## Changelog

### Version 1.0.2.0 (2026-02-07)
- Updated plugin display name to "Scholarly Article Schema Plugin"
- Enhanced description for better clarity
- Improved bilingual support (English/Turkish)
- Production-ready release

### Version 1.0.1.0 (2026-02-06)
- Initial public release
- Support for OJS 3.3.x, 3.4.x, 3.5.x
- ScholarlyArticle and MedicalScholarlyArticle types
- Comprehensive metadata extraction
- Zero performance impact implementation

## Credits

Developed by **Kerim Sarıgül** of **OJS Services** - Professional OJS development, consulting, and plugin creation.

---

**Need custom OJS development?** Visit [ojsservices.com](https://ojs-services.com) for professional OJS consulting, plugin development, and technical support.
