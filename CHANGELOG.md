# Changelog

All notable changes to `filament-nepali-address` will be documented in this file.

## 1.0.0 - 2026-09-28

Initial release of `filament-nepali-address`, a complete Nepali address suite for Filament (v4 & v5).

### Features
- **Core Data Bridge (`AddressData`)**: In-memory static caching of 7 provinces, 77 districts, and 753 local bodies (metropolitan, sub-metropolitan, municipality, rural municipality) based on `khanaldpk/nepali-address`.
- **Cascading Form Component (`NepaliAddressSelects`)**: Dynamic 3-level cascading select fields with `live()` reactivity, automatic child field resets, searchable and preloaded options, custom prefixing (e.g. `billing_`, `shipping_`), optional ward field (`->withWard()`), and optional postal code field (`->withPostalCode()`).
- **Dedicated Form Field & Validation**: Standalone factory methods (`makeProvinceSelect`, `makeDistrictSelect`, `makeLocalBodySelect`, `makePostalCodeInput`) and strict relationship validation rules (`NepaliDistrictBelongsToProvince`, `NepaliLocalBodyBelongsToDistrict`, `NepalPostalCode`).
- **Table Column (`NepaliAddressColumn`)**: Formats combined address records or single-level attributes. Translates search terms (English or Nepali Devanagari) directly into indexed ID queries (`whereIn`) for maximum database performance.
- **Table Filter (`NepaliAddressFilter`)**: Cascading table filter with bidirectional auto-population (selecting municipality auto-populates district and province), custom badge indicators, and single-level filter shortcuts.
- **Infolist Entry (`NepaliAddressEntry`)**: Read-only display entry for Filament Infolists supporting combined addresses or individual administrative levels.
- **Localization**: Full English (`en`) and Nepali (`ne`) language support for all labels, placeholders, validation error messages, and administrative names.
