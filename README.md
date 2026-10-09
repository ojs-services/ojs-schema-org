# Scholarly Article Schema Plugin

**Version:** 1.0.3.1
**Author:** Kerim Sarıgül (OJS Services)
**Compatibility:** OJS 3.3.0.0–3.3.0.22
**License:** GPL v3

## Overview

The Scholarly Article Schema Plugin adds Schema.org structured data (ScholarlyArticle or MedicalScholarlyArticle) to article landing pages using JSON-LD format. This improves semantic indexing, search engine understanding, and academic content discovery while remaining fully compatible with existing Google Scholar meta tags.

## Which Package to Download

| OJS Version | Branch | Package |
|---|---|---|
| 3.3.x | `ojs-3.3` | `schemaOrg-ojs3.3-<version>.tar.gz` |
| 3.4.x, 3.5.x | `ojs-3.4-3.5` | `schemaOrg-ojs3.4-3.5-<version>.tar.gz` |

## Key Features

- **Schema.org JSON-LD Output**: Automatically generates structured data for every published article
- **Dual Schema Support**: Choose between ScholarlyArticle (general) or MedicalScholarlyArticle (medical journals)
- **Rich Metadata**: Includes authors, affiliations, ORCID, DOI, keywords, abstracts, publication dates, and more
- **Google Scholar Compatible**: Works alongside existing citation meta tags without conflicts
- **Zero Performance Impact**: No additional database queries; uses already-loaded OJS objects
- **Cross-Theme Compatible**: Works with all OJS themes

## Installation

### Method 1: Plugin Gallery (Recommended)
1. Login as Journal Manager
2. Navigate to **Settings > Website > Plugins > Plugin Gallery**
3. Search for "Scholarly Article Schema"
4. Click **Install**

### Method 2: Manual Upload
1. Download the latest `schemaOrg-ojs3.3-<version>.tar.gz` from releases
2. Login as Journal Manager
3. Navigate to **Settings > Website > Plugins > Upload a New Plugin**
4. Upload the tar.gz file
5. Click **Save**

### Method 3: Server Installation
```bash
cd plugins/generic
tar -xzf schemaOrg-ojs3.3-1.0.3.1.tar.gz
chown -R www-data:www-data schemaOrg
chmod -R 755 schemaOrg
```

## Configuration

1. Navigate to **Settings > Website > Plugins > Installed Plugins**
2. Find **Scholarly Article Schema Plugin** and enable it
3. Click **Settings** (gear icon)
4. Select your Schema Type:
   - **ScholarlyArticle** (default): For general academic journals
   - **MedicalScholarlyArticle**: For medical and health science journals only
5. Click **OK** to save

## What Gets Included

The plugin automatically extracts and includes the following metadata when available:

### Core Fields
- **@type**: ScholarlyArticle or MedicalScholarlyArticle
- **headline**: Article title (publication locale)
- **description**: Abstract (HTML stripped, publication locale)
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
- **publisher**: Publisher institution if set; otherwise journal name
- **isPartOf**: Nested structure of Periodical > PublicationVolume > PublicationIssue
- **pagination**: Page numbers
- **keywords**: Array of keywords (publication locale)
- **inLanguage**: Publication language
- **license**: License URL

### Journal Information
- **issn**: Online or print ISSN
- **volumeNumber**: Volume number
- **issueNumber**: Issue number

## Google Scholar Compatibility

This plugin **does not interfere** with Google Scholar indexing:

- Uses separate JSON-LD format (not meta tags)
- Inserted via `additionalHeadData` / `addHeader`
- Google Scholar continues to read `citation_*` meta tags
- Schema.org data provides additional semantic context

## Technical Details

- **Hook Used**: `TemplateManager::display`
- **Performance**: Zero additional database queries
- **Output Format**: JSON-LD in `<script type="application/ld+json">`
- **Injection Point**: `<head>` section of article pages only
- **Template Detection**: Matches `article.tpl` across all themes
- **Encoding**: UTF-8 with JSON_UNESCAPED_UNICODE, JSON_HEX_TAG, JSON_HEX_AMP

## Testing & Validation

After installation, validate your Schema.org output:

1. **Google Rich Results Test**
   https://search.google.com/test/rich-results

2. **Schema.org Validator**
   https://validator.schema.org/

3. **View Source**
   Right-click article page > View Page Source > Search for `application/ld+json`

## Support & Development

- **Developer**: Kerim Sarıgül
- **Website**: https://ojsservices.com
- **GitHub**: https://github.com/ojs-services/ojs-schema-org
- **Email**: info@ojsservices.com

## License

GPL v3 - See LICENSE file for details
