# Scholarly Article Schema Eklentisi

**Sürüm:** 1.0.3.4
**Yazar:** Kerim Sarıgül (OJS Services)
**Uyumluluk:** OJS 3.3.0.x
**Lisans:** GPL v3

## Genel Bakış

Scholarly Article Schema eklentisi, makale sayfalarına Schema.org yapılandırılmış veri (ScholarlyArticle veya MedicalScholarlyArticle) JSON-LD formatında ekler. Semantik indekslemeyi, arama motoru anlamasını ve akademik içerik keşfini iyileştirir; mevcut Google Scholar meta etiketleriyle tam uyumlu çalışır.

## Hangi Paketi İndirmeli

| OJS Sürümü | Dal | Paket |
|---|---|---|
| 3.3.x | `ojs-3.3` | `schemaOrg-ojs3.3-<sürüm>.tar.gz` |
| 3.4.x, 3.5.x | `ojs-3.4-3.5` | `schemaOrg-ojs3.4-3.5-<sürüm>.tar.gz` |

## Temel Özellikler

- **Schema.org JSON-LD Çıktısı**: Her yayımlanmış makale için otomatik yapılandırılmış veri üretir
- **Çift Şema Desteği**: ScholarlyArticle (genel) veya MedicalScholarlyArticle (tıp dergileri) seçilebilir
- **Zengin Üstveri**: Yazarlar, kurumlar, ORCID, DOI, anahtar sözcükler, özetler, yayın tarihleri ve daha fazlası
- **Google Scholar Uyumlu**: Mevcut citation meta etiketleriyle çakışma olmadan çalışır
- **Sıfır Performans Etkisi**: Ek veritabanı sorgusu yok; zaten yüklenmiş OJS nesneleri kullanılır
- **Tema Bağımsız**: Tüm OJS temalarıyla uyumlu

## Kurulum

### Yöntem 1: Eklenti Galerisi (Önerilen)
1. Dergi Yöneticisi olarak giriş yapın
2. **Ayarlar > Web Sitesi > Eklentiler > Eklenti Galerisi** bölümüne gidin
3. "Scholarly Article Schema" arayın
4. **Kur** düğmesine tıklayın

### Yöntem 2: Elle Yükleme
1. Son `schemaOrg-ojs3.3-<sürüm>.tar.gz` dosyasını indirin
2. Dergi Yöneticisi olarak giriş yapın
3. **Ayarlar > Web Sitesi > Eklentiler > Yeni Eklenti Yükle** bölümüne gidin
4. tar.gz dosyasını yükleyin
5. **Kaydet** düğmesine tıklayın

### Yöntem 3: Sunucu Kurulumu
```bash
cd plugins/generic
tar -xzf schemaOrg-ojs3.3-1.0.3.4.tar.gz
chown -R www-data:www-data schemaOrg
chmod -R 755 schemaOrg
```

## Yapılandırma

1. **Ayarlar > Web Sitesi > Eklentiler > Yüklü Eklentiler** bölümüne gidin
2. **Scholarly Article Schema Plugin** eklentisini etkinleştirin
3. **Ayarlar** (dişli simgesi) düğmesine tıklayın
4. Şema Türünü seçin:
   - **ScholarlyArticle** (varsayılan): Genel akademik dergiler
   - **MedicalScholarlyArticle**: Yalnızca tıp ve sağlık bilimleri dergileri
5. **Tamam** düğmesine tıklayın

## JSON-LD'ye Dahil Edilen Alanlar

### Temel Alanlar
- **@type**: ScholarlyArticle veya MedicalScholarlyArticle
- **headline**: Makale başlığı (yayın dili)
- **description**: Özet (HTML temizlenmiş, yayın dili)
- **url**: Makale sayfası URL'si
- **datePublished**: Yayın tarihi (ISO 8601)
- **dateModified**: Son değişiklik tarihi (ISO 8601)

### Yazar Bilgileri
- **author**: Person nesneleri dizisi:
  - name, givenName, familyName
  - affiliation (Organization)
  - sameAs (ORCID URL)

### Tanımlayıcılar
- **identifier**: DOI (PropertyValue olarak)
- **sameAs**: DOI URL'si (https://doi.org/...)

### Yayın Bağlamı
- **publisher**: Yayıncı kurum; yoksa dergi adı
- **isPartOf**: İç içe yapı: Periodical > PublicationVolume > PublicationIssue
- **pagination**: Sayfa numaraları
- **keywords**: Anahtar sözcükler (yayın dili)
- **inLanguage**: Yayın dili
- **license**: Lisans URL'si

## Google Scholar Uyumluluğu

Bu eklenti Google Scholar indekslemesini **etkilemez**:

- Ayrı JSON-LD formatı kullanır (meta etiket değil)
- `additionalHeadData` / `addHeader` yöntemiyle eklenir
- Google Scholar `citation_*` meta etiketlerini okumaya devam eder
- Schema.org verisi ek anlamsal bağlam sağlar

## Destek ve Geliştirme

- **Geliştirici**: Kerim Sarıgül
- **Web Sitesi**: https://ojs-services.com
- **GitHub**: https://github.com/ojs-services/ojs-schema-org
- **E-posta**: info@ojs-services.com

## Lisans

GPL v3 — Ayrıntılar için LICENSE dosyasına bakın
