# SEO / AI / AEO / GEO Audit — Checklist

**Source:** `memory_bank/SOV-SUMMIT-SEO-AI-AEO-GEO-Audit.pdf` (audit of https://sov-summit.com, 17 Nov 2026, 43 URLs checked).
**Score at audit time:** 5.7 / 10. Fixing the P0 items should bring it to about 7.5.

How to use: tick `[x]` when a task is done **and** checked on the live site.

---

## Done so far

- [x] Google Tag Manager `GTM-N5825MP2` added to `resources/views/layouts/site.blade.php` (head script + body noscript). Live and checked.
- [x] Test added: `tests/Feature/GoogleTagManagerTest.php`.
- [x] Phone number changed to `+41 71 539 23 00` (footer, contact, about FAQ + schema, legal notice, `config/invoice.php`).
- [ ] Check if `+41 71 539 23 00` has WhatsApp. If not, change `wa.me/41715392300` links to `tel:+41715392300`.
- [ ] Update `INVOICE_COMPANY_PHONE` in the server `.env` (if set).

---

## P0 — Critical (do first)

### 1. Fix broken JSON-LD (`@context` bug)
Blade reads `"@context"` as the Laravel `@context` directive and prints raw PHP. Schema is invalid on 15 pages.

- [x] Change `"@context"` → `"@@context"` (or build schema as a PHP array + `json_encode`) in:
  - [x] `resources/views/pages/home.blade.php`
  - [x] `resources/views/pages/about.blade.php`
  - [x] `resources/views/pages/contact.blade.php`
  - [x] `resources/views/pages/events.blade.php`
  - [x] `resources/views/pages/insights.blade.php`
  - [x] `resources/views/pages/products.blade.php`
  - [x] `resources/views/pages/faq.blade.php`
  - [x] `resources/views/pages/blog/index.blade.php`
  - [x] `resources/views/pages/services/index.blade.php`
  - [x] `resources/views/pages/legal/notice.blade.php`
  - [x] `resources/views/pages/legal/privacy.blade.php`
  - [x] `resources/views/pages/legal/terms.blade.php`
  - [x] `resources/views/pages/legal/cookie.blade.php`
  - [x] `resources/views/errors/404.blade.php`
  - [x] `resources/views/pages/special-offers-show.blade.php` (found later)
- [ ] Better: move the shared Organization schema into one partial (e.g. `partials/schema-organization.blade.php`).
- [x] Add a feature test: `tests/Feature/JsonLdSchemaTest.php` (run it locally).
- [ ] Check Home, About, FAQ in Google Rich Results Test + validator.schema.org.

### 2. Fix `APP_URL` on the live server
Schema on events, services and blog shows `https://dev.sov-summit.com`.

- [ ] Server `.env`: `APP_URL=https://sov-summit.com`
- [ ] Server `.env`: `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Run `php artisan optimize:clear && php artisan config:cache`
- [ ] Check live: no `dev.sov-summit.com` in page source.

### 3. Protect the dev site
- [ ] Password-protect `dev.sov-summit.com` or send `X-Robots-Tag: noindex` from nginx.

### 4. Remove duplicate Management Training page
Decision: keep `/management-training` (it is a top-level menu item and `Service::publicUrl()` already uses it).

- [x] 301 redirect `/services/management-training` → `/management-training` (`ServiceController::show`).
- [x] Menu, footer and home links already use `/management-training` (no change needed).
- [x] Sitemap lists it once, as `/management-training` (services now use `publicUrl()`).
- [x] Tests: `SeoTest` (redirect + sitemap).

---

## P1 — High

### Sitemap & URLs
- [x] Add special-offer pages to `SitemapController` (from the LocalRydes API, up to 500; sitemap still works if the API is down).
- [x] Readable special-offer URLs: `/special-offers/{uuid}/{title-slug}` (`App\Support\SpecialOfferUrl`). Old UUID-only URLs and wrong slugs 301 to the new URL.
- [x] `lastmod` for listing pages: `/`, `/events`, `/services`, `/blog` (from the newest published record). Pure static pages have no lastmod on purpose.
- [x] Tests: `SeoTest`, `SpecialOfferTest`. New `ServiceFactory`.

### Brand entity (for AI search)
- [ ] One shared Organization schema with `sameAs`, `telephone` (+41 71 539 23 00), `contactPoint`, `foundingDate`, `areaServed`, `logo` as ImageObject, `@id`.
- [ ] Create / claim: LinkedIn company page, Google Business Profile (Zug), Crunchbase, Wikidata.
- [ ] Add social links to the footer.
- [ ] Add `public/llms.txt` (company summary, services + URLs, key event pages, contact).

### Titles & descriptions
- [ ] `/events` title (19 chars) → e.g. "Executive Events & Delegation Programmes | SOV SUMMIT"
- [ ] `/blog` title (20 chars) → add keyword
- [ ] `/products` title "Solutions | SOV SUMMIT" → name the apps
- [ ] `/special-offers` title (27 chars) + description (101 chars) → longer
- [ ] `/events/sovereign-executive-forum` title (103 chars) → about 60 chars
- [ ] Cut descriptions over 160 chars: Home (216), Davos (270), Doha (253), ADFW (273)

### H1s
- [ ] `/about` "People. Ideas. Impact." → topic H1 (keep slogan as sub-line)
- [ ] `/services` "Planning. Connecting. Delivering." → topic H1
- [ ] `/services/media-coverage` "Capture the moments that matter" → topic H1
- [ ] `/insights` "Insights" → more specific H1

---

## P2 — Medium

### Schema details
- [ ] Event schema: add `endDate`, full `PostalAddress` with `addressCountry`, remove `performer` or add `offers`.
- [ ] Past/private case studies (Alpine Dinner, Private Family Journey, Media Coordination) → `Article` / `CreativeWork`, not `Event`.
- [ ] Davos / Doha / ADFW: consider `Service` + `about: Event` (you are not the event organiser).
- [ ] Service schema: `serviceType` in normal case (not ALL CAPS).
- [ ] BlogPosting: real author (name + bio page) or author = Organization; fix `wordCount`.
- [ ] Special offers: `Product`/`Service` + `Offer` (price, currency, validFrom/validThrough).
- [ ] Breadcrumb URLs: remove trailing slash (`/about/` → `/about`) to match canonicals.

### Content
- [ ] Fix H1 typo on `/special-offers`: "journeys,ready" → "journeys, ready".
- [ ] Expand service pages to 700–1,200 words each, with an answer-first intro and 5–8 FAQs:
  - [ ] events-productions (104 words)
  - [ ] management-training (127)
  - [ ] conference-planning (187)
  - [ ] travel-experiences (190)
  - [ ] delegation-management (194)
  - [ ] media-coverage (194)
  - [ ] security-coordination (205)
  - [ ] planning-coordination (291) + add FAQ block
- [ ] Blog post "Coordinating a Cross-Border Executive Forum" (100 words) → write the full article.
- [ ] Merge `/blog` and `/insights` into one hub, 301 the other.
- [ ] About/Home: team (names, roles, LinkedIn), founding year, numbers (events, delegations, countries), named testimonials.
- [ ] Contact page: phone, address, map.
- [ ] Publish 2 posts per month. Ideas:
  - [ ] How to organise a delegation for WEF Davos 2027
  - [ ] Hotels and meeting venues near the Davos Congress Centre
  - [ ] Zurich Airport to Davos: car, train or helicopter?
  - [ ] Government delegation visit to Switzerland — protocol checklist
  - [ ] Executive protection in Switzerland: how it works
  - [ ] MWC Barcelona 2027 executive chauffeur guide
  - [ ] Cost guide: 3-day executive forum in Zug/Zurich

### Technical
- [ ] Cache the LocalRydes API call on `/special-offers` (now 1.3 s).
- [ ] nginx: add `Strict-Transport-Security` and a basic CSP.
- [ ] Public page cache (now `Cache-Control: no-cache, private`).
- [ ] Cookie consent: Google Consent Mode, or GTM tags fire only after consent.

---

## P3 — Low

- [ ] Self-host OG images (now on Unsplash and on the `sob-summit` bucket folder — fix the typo for new uploads).
- [ ] Remove the canonical tag from the 404 page.
- [ ] Add `<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">` to all public pages.
- [ ] robots.txt: optional explicit `Allow` for GPTBot, OAI-SearchBot, PerplexityBot, ClaudeBot, Google-Extended.

---

## After all fixes

- [ ] Re-run the audit (all pages) and compare with the 5.7 score.
- [ ] Submit sitemap in Google Search Console and Bing Webmaster Tools.
- [ ] Check Core Web Vitals and indexing in Search Console after 2–4 weeks.
