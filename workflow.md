# filament-nepali-address — Implementation Workflow

**Package:** `dev1191/filament-nepali-address`
**Namespace:** `Dev1191\FilamentNepaliAddress`
**Depends on:** `khanaldpk/nepali-address` (data layer), `filament/filament`

---

## Status

- [x] Scaffolded from `filamentphp/plugin-skeleton` (full skeleton — not forms-only or tables-only)
- [x] `configure.php` run — placeholders replaced, service provider renamed
- [ ] Everything below

---

## Phase 0 — Setup

- [x] `composer install`
- [x] `composer require khanaldpk/nepali-address` as a dependency in the plugin's own `composer.json` (not just the test app)
- [x] Set `"filament/filament": "^4.0|^5.0"` in `composer.json` — **Filament v4 and v5 only** (decided). No v3 support: v3 predates the Forms/Infolists "Schemas" unification and would need a separate compat layer that isn't worth it for this plugin.
- [x] Set up test suite and providers in `tests/TestCase.php`

---

## Phase 1 — Core data bridge

- [x] `src/Support/AddressData.php` — thin wrapper/facade around `Khanaldpk\NepaliAddress\NepaliAddress`, so the rest of the plugin depends on one internal class, not the upstream package directly (makes it easy to swap data sources later or cache results)
- [x] Add simple in-memory caching around `getProvinces()`, `getDistricts()`, `getLocalBodies()`, `getLocalBodyTypes()` calls (keyed by locale, avoiding repetitive disk JSON file parsing across request/filament life cycles)
- [x] Added lookup helpers (`findProvince`, `findDistrict`, `findLocalBody`, `getProvinceName`, `getDistrictName`, `getLocalBodyName`, `formatAddress`) and relationship consistency checks (`districtBelongsToProvince`, `localBodyBelongsToDistrict`)
- [x] Unit test suite in `tests/AddressDataTest.php` passing 100%
- [x] Decide + document: **Store `province_id`, `district_id`, `municipality_id` as raw integers on the model.**
  - **Rationale:** 
    - Standard Laravel database columns (`$table->unsignedInteger('province_id')->nullable()`) allow direct indexing and performant SQL queries.
    - Resolving names dynamically at display time (via `AddressData::get...Name()` or `NepaliAddressColumn`) allows instant localization support (`en` vs `ne`) without modifying the database.
    - Zero hydration/serialization overhead compared to value objects/custom casts.
    - Flexible for multiple address instances on a single model (e.g. `billing_province_id`, `shipping_province_id`).

---

## Phase 2 — Form field: `NepaliAddressSelects`

- [x] `src/Forms/Components/NepaliAddressSelects.php`
  - [x] Province select (`live()`, resets district + local body on change)
  - [x] District select (options depend on `Get $get` for province, `disabled()` until province chosen)
  - [x] Local body select (options depend on district, `disabled()` until district chosen)
  - [x] Accept a `$prefix` param so the field can be reused twice on one form (e.g. `billing_` / `shipping_`)
  - [x] Accept a `$columns` param (default 3) so consumers can control layout
  - [x] Preload & Searchable enabled by default for smooth UX across 77 districts and 753 municipalities
  - [x] Standalone factory helpers (`makeProvinceSelect`, `makeDistrictSelect`, `makeLocalBodySelect`, `makePostalCodeInput`) for custom layouts
- [x] Add **relationship-consistency validation rules**:
  - [x] `NepaliDistrictBelongsToProvince` rule (supports Closure and `DataAwareRule`)
  - [x] `NepaliLocalBodyBelongsToDistrict` rule (supports Closure and `DataAwareRule`)
  - [x] Auto-wired into `NepaliAddressSelects` with English and Nepali (`ne`) error messages
- [x] Postal code field:
  - [x] `NepaliPostalCodeInput` component using `Khanaldpk\NepaliAddress\Rules\NepalPostalCode`
  - [x] Can be enabled directly via `->withPostalCode()` on `NepaliAddressSelects`
  - [x] Documented that postal code validates against official Nepal postal codes, but is not tied to local body ID since upstream postal code data is city/region-based rather than municipality-ID based
- [x] Ward support:
  - [x] Enabled via `->withWard(bool $condition = true, ?string $fieldName = null, array|Closure|null $options = null, int $maxWards = 35)`
  - [x] Defaults to numeric input (`1` to `35`) or custom Select dropdown if `$options` passed
  - [x] Documented upstream limitation (no official ward data bundled) and flexible developer override options
- [x] Unit & feature tests passing in `tests/NepaliAddressSelectsTest.php` and `tests/NepaliAddressValidationTest.php`

---

## Phase 3 — Table column: `NepaliAddressColumn`

- [x] `src/Columns/NepaliAddressColumn.php`
- [x] Resolves stored `province_id`/`district_id`/`municipality_id` back to display names via `AddressData`
- [x] Support both "single combined column" (e.g. "Kathmandu, Bagmati Pradesh") and "one column per level" usage
  - [x] Level modes: `asProvince()`, `asDistrict()`, `asLocalBody()`, `asWard()`, `asCombined()`
  - [x] Shorthand static factories: `NepaliAddressColumn::province()`, `NepaliAddressColumn::district()`, `NepaliAddressColumn::localBody()`, `NepaliAddressColumn::ward()`, `NepaliAddressColumn::combined()`
  - [x] Configurable address separator (`->addressSeparator(...)`), prefix (`->addressPrefix(...)`), and ward inclusion (`->withWard(...)`)
- [x] Added `->searchable()` support searching by resolved name (in English and Nepali) instead of raw integer IDs
  - [x] Resolves user search text into matching IDs via in-memory `AddressData::searchProvinceIds()`, `searchDistrictIds()`, and `searchLocalBodyIds()`
  - [x] Applies high-performance indexed `WHERE ... IN (...)` SQL queries
  - [x] Tested with unit test suite in `tests/NepaliAddressColumnTest.php` passing 100%

## Phase 4 — Table filter: `NepaliAddressFilter`

- [x] `src/Filters/NepaliAddressFilter.php`
- [x] Cascading dependent-select pattern adapted to Filament's Filter API:
  - [x] Forward cascading: Province -> District -> Local Body
  - [x] Backward auto-population: Selecting a district auto-sets its province; selecting a local body auto-sets its district and province
  - [x] `live()` dynamic options evaluation in Filament's table filter schema
- [x] Configurable prefixes for multi-address models (e.g. `billing_`) and optional ward filtering (`->withWard()`)
- [x] Shorthand single-level filters and factories: `NepaliAddressFilter::province()`, `NepaliAddressFilter::district()`, `NepaliAddressFilter::localBody()`
- [x] Human-readable active filter badges/indicators (`Indicator::make(...)->removeField(...)`)
- [x] Clean reset state handling
- [x] Unit & query constraint test suite in `tests/NepaliAddressFilterTest.php` passing 100%

## Phase 5 — Infolist entry (nice-to-have)

- [x] `src/Infolists/Components/NepaliAddressEntry.php` — read-only display version for infolists, reusing the same name-resolution logic as the column
  - [x] Combined format (`asCombined()`) with configurable separator and prefix (`->addressPrefix(...)`, `->addressSeparator(...)`)
  - [x] Single-level entries: `asProvince()`, `asDistrict()`, `asLocalBody()`, `asWard()`
  - [x] Shorthand static factories: `NepaliAddressEntry::combined()`, `NepaliAddressEntry::province()`, `NepaliAddressEntry::district()`, `NepaliAddressEntry::localBody()`, `NepaliAddressEntry::ward()`
  - [x] Optional ward and postal code display (`->withWard()`, `->withPostalCode()`)
  - [x] Unit test suite in `tests/NepaliAddressEntryTest.php` passing 100%

---

## Phase 6 — Testing

- [x] Unit tests for `AddressData` wrapper (provinces, districts, local bodies, types, relations, formatting, search translation)
- [x] Feature test: form renders, cascading options populate correctly when province/district selected, state updated hooks clear dependent fields
- [x] Feature test: table filter narrows results correctly with combined and single-level filters
- [x] Feature test: relationship-consistency validation rejects a district that doesn't belong to the selected province (and local body to district)
- [x] `composer test` passes (37 tests, 227 assertions passing 100%)
- [x] `vendor/bin/pint` — code style clean
- [x] `vendor/bin/phpstan` (Larastan) — static analysis clean (0 errors)

---

## Phase 7 — Documentation

- [x] README: installation, quick usage example (form + column + filter), the ward limitation, the relationship-consistency note
- [x] Add a "Data source & limitations" section crediting `khanaldpk/nepali-address` and listing exactly what it doesn't cover (wards, cross-level validation) so buyers aren't surprised
- [x] Documented all component configurations, smart search, and bilingual localization
- [x] CHANGELOG entry for v1.0.0

---

## Phase 8 — Publish

- [ ] Push to GitHub under `dev1191/filament-nepali-address` (`git push origin 5.x --tags`)
- [x] Tag `v1.0.0`
- [ ] Submit to [Packagist](https://packagist.org/packages/submit) (`dev1191/filament-nepali-address`)
- [ ] Submit to the [Filament plugin directory](https://filamentphp.com/plugins)
- [ ] Cross-link from existing `dev1191` repositories where relevant

---

## Open decisions to make before Phase 2

1. **Ward support** — ship without it, or bundle/accept custom ward data? Affects the field's public API shape.
2. **Storage shape** — raw ID columns on the model vs. a dedicated `NepaliAddress` value object/cast?
3. ~~Filament version target~~ — **Resolved: v4 + v5 only** (`^4.0|^5.0`). No v3 support.
4. **Postal code**: bundle it into the address field group, or ship as a fully separate optional field? (Currently leaning: separate, since it's not tied to local-body ID in the source data.)
