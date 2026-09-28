# Filament Nepali Address

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dev1191/filament-nepali-address.svg?style=flat-square)](https://packagist.org/packages/dev1191/filament-nepali-address)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dev1191/filament-nepali-address/tests.yml?branch=5.x&label=tests&style=flat-square)](https://github.com/dev1191/filament-nepali-address/actions?query=workflow%3Atests+branch%3A5.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dev1191/filament-nepali-address/fix-code-style.yml?branch=5.x&label=code%20style&style=flat-square)](https://github.com/dev1191/filament-nepali-address/actions?query=workflow%3Afix-code-style+branch%3A5.x)
[![Total Downloads](https://img.shields.io/packagist/dt/dev1191/filament-nepali-address.svg?style=flat-square)](https://packagist.org/packages/dev1191/filament-nepali-address)

A comprehensive, production-ready Nepali address plugin for **Filament (v4 and v5)**.

Provides cascading form selects, smart searchable table columns, cascading table filters, infolist entries, and relationship validation rules for Nepal's administrative divisions: **7 Provinces**, **77 Districts**, and **753 Local Bodies (Municipalities & Rural Municipalities)**.

---

## Features

- ⚡ **Filament v4 & v5 Ready**: Built exclusively for modern Filament schemas and panels.
- 🔗 **Cascading Form Selects**: Seamless Province ➔ District ➔ Municipality dependent selects with automatic dependent state reset and searchable preloaded options.
- 🗄️ **Optimized Storage**: Stores clean, normalized integer IDs (`province_id`, `district_id`, `municipality_id`) directly on Eloquent models.
- 🧬 **Eloquent Model Trait (`HasNepaliAddress`)**: Formatted address accessors (`nepali_address`, `province_name`, etc.), multi-address prefix support, and high-performance query scopes.
- 🏗️ **Migration Blueprint Macros**: One-line schema helpers `$table->nepaliAddress()` and `$table->dropNepaliAddress()` with prefix and optional field toggles.
- 🔍 **High-Performance Table Search**: Automatically translates user text search queries (in both English and Devanagari script) into fast database `whereIn(...)` ID queries instead of slow text matches.
- 🎯 **Cascading Table Filters**: Multi-level cascading filter with bidirectional auto-population (selecting a municipality auto-selects its district and province) and human-readable badges.
- 📄 **Infolist Entries**: Read-only display components for Filament infolists.
- 🛡️ **Built-in Relationship Validation**: Ensures submitted districts belong to the selected province and municipalities belong to the selected district.
- 🇳🇵 **Bilingual Localization**: Instant English and Nepali (`नेपाली`) language support for administrative names, labels, and validation errors.
- 📮 **Ward & Postal Code Extensions**: Optional ward number input and validated postal code support.

---

## Requirements

- **PHP**: `^8.2`
- **Laravel**: `^11.0`, `^12.0`, or `^13.0`
- **Filament**: `^4.0` or `^5.0`

---

## Installation

Install the package via Composer:

```bash
composer require dev1191/filament-nepali-address
```

Optionally publish the configuration file:

```bash
php artisan vendor:publish --tag="nepali-address-config"
```

Optionally publish the translation files (English and Nepali):

```bash
php artisan vendor:publish --tag="nepali-address-translations"
```

---

## Recommended Database Schema

You can easily add all necessary columns using the built-in `nepaliAddress()` Blueprint macro in your Laravel migrations:

```php
use Illuminate\Database\Schema\Blueprint;

Schema::table('users', function (Blueprint $table) {
    // Adds province_id, district_id, municipality_id, ward_no, and postal_code
    $table->nepaliAddress();

    // Or with custom prefix for multiple addresses (e.g. billing_ & shipping_):
    $table->nepaliAddress('billing_');
    $table->nepaliAddress('shipping_', withWard: false, withPostalCode: false);
});
```

To drop the address columns in rollback migrations:

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropNepaliAddress();
    $table->dropNepaliAddress('billing_');
});
```

---

## Model Setup (`User` Model)

Add the `HasNepaliAddress` trait and include the address columns in your Eloquent model (e.g. `App\Models\User`):

```php
namespace App\Models;

use Dev1191\FilamentNepaliAddress\Traits\HasNepaliAddress;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasNepaliAddress;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // Nepali address columns:
        'province_id',
        'district_id',
        'municipality_id',
        'ward_no',
        'postal_code',
    ];
}
```


---

## Usage

### 1. Form Component (`NepaliAddressSelects`)

Add cascading address selects into your Filament Form:

```php
use Dev1191\FilamentNepaliAddress\Forms\Components\NepaliAddressSelects;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Default 3-column cascading select (Province, District, Municipality)
            NepaliAddressSelects::make(),
        ]);
}
```

#### Multi-Address Support (Custom Prefixes)

If your model stores multiple addresses (e.g., `billing_` and `shipping_`), pass the prefix to `make()`:

```php
NepaliAddressSelects::make('billing_'),
NepaliAddressSelects::make('shipping_'),
```

This will automatically bind to:
- `billing_province_id`, `billing_district_id`, `billing_municipality_id`
- `shipping_province_id`, `shipping_district_id`, `shipping_municipality_id`

#### Including Ward & Postal Code

```php
NepaliAddressSelects::make()
    ->withWard() // Adds numeric 'ward_no' input
    ->withPostalCode() // Adds validated Nepal postal code input
```

You can also customize the ward input into a select dropdown:

```php
NepaliAddressSelects::make()
    ->withWard(
        fieldName: 'ward_no',
        options: array_combine(range(1, 15), range(1, 15))
    )
```

#### Individual Field Customizations & Requirements

```php
NepaliAddressSelects::make()
    ->required() // Marks province, district, and municipality as required
    ->provinceRequired()
    ->districtRequired()
    ->localBodyRequired()
    ->columns(3) // Customize grid layout
    ->relationshipValidation(true); // Enforces parent-child hierarchy validation rules
```

#### Territory Restrictions (Restricting Specific Provinces / Districts)

If your business or delivery coverage only serves specific regions (e.g. Kathmandu Valley or Bagmati Province only):

```php
NepaliAddressSelects::make()
    ->onlyProvinces([3]) // Only Bagmati Pradesh
    ->onlyDistricts([5, 6, 7]) // Only Kathmandu, Lalitpur, and Bhaktapur
    // or exclude specific areas:
    ->exceptProvinces([1, 2]);
```

#### Standalone Field Selects

If you prefer building each field individually rather than using the grouped component:

```php
NepaliAddressSelects::makeProvinceSelect('province_id'),
NepaliAddressSelects::makeDistrictSelect('district_id', provinceFieldName: 'province_id'),
NepaliAddressSelects::makeLocalBodySelect('municipality_id', districtFieldName: 'district_id'),
NepaliAddressSelects::makePostalCodeInput('postal_code'),
```

---

### 2. Table Column (`NepaliAddressColumn`)

Display formatted address strings in your Filament Table:

```php
use Dev1191\FilamentNepaliAddress\Columns\NepaliAddressColumn;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // Combined format: "Kathmandu-3, Kathmandu, Bagmati Pradesh"
            NepaliAddressColumn::make('address')
                ->searchable() // Smart search across local bodies, districts & provinces!
                ->sortable(),  // Multi-column sorting across province, district & municipality!

            // Combined with ward:
            NepaliAddressColumn::make('address')
                ->withWard(),

            // Multi-address prefix
            NepaliAddressColumn::make('shipping_address')
                ->prefix('shipping_')
                ->sortable(),
        ]);
}
```

#### Single-Level Table Columns

```php
NepaliAddressColumn::province('province_id')->sortable(),
NepaliAddressColumn::district('district_id')->sortable(),
NepaliAddressColumn::localBody('municipality_id')->sortable(),
NepaliAddressColumn::ward('ward_no')->sortable(),
```

#### How Smart Search Works

When a user searches `"Kathmandu"` or `"काठमाडौं"`, the column looks up the matching IDs in-memory and transforms the query into indexed integer constraints:

```sql
WHERE `municipality_id` IN (5, ...) OR `district_id` IN (5) OR `province_id` IN (3)
```

This avoids slow string matching and full-table scans.

---

### 3. Table Filter (`NepaliAddressFilter`)

Add a cascading address filter to your Filament Table:

```php
use Dev1191\FilamentNepaliAddress\Filters\NepaliAddressFilter;

public static function table(Table $table): Table
{
    return $table
        ->filters([
            // Cascading filter: Province -> District -> Municipality
            NepaliAddressFilter::make(),

            // With Ward filter
            NepaliAddressFilter::make()->withWard(),

            // Restrict filter to specific territory
            NepaliAddressFilter::make()
                ->onlyProvinces([3])
                ->onlyDistricts([5, 6, 7]),

            // Multi-address prefix
            NepaliAddressFilter::make('shipping_address')
                ->prefix('shipping_'),
        ]);
}
```

#### Single-Level Table Filters

```php
NepaliAddressFilter::province('province_id'),
NepaliAddressFilter::district('district_id'),
NepaliAddressFilter::localBody('municipality_id'),
```

---

### 4. Infolist Entry (`NepaliAddressEntry`)

Display addresses in read-only Filament Infolists:

```php
use Dev1191\FilamentNepaliAddress\Infolists\Components\NepaliAddressEntry;

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            // Combined address entry
            NepaliAddressEntry::make('address')
                ->withWard(),

            // Single administrative levels
            NepaliAddressEntry::province('province_id'),
            NepaliAddressEntry::district('district_id'),
            NepaliAddressEntry::localBody('municipality_id'),
        ]);
}
```

---

### 5. Eloquent Model Trait (`HasNepaliAddress`)

With the `HasNepaliAddress` trait added to your model (such as `User` or `Customer`), you get instant accessors, formatted address strings, and powerful query scopes:

```php
namespace App\Models;

use Dev1191\FilamentNepaliAddress\Traits\HasNepaliAddress;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasNepaliAddress;

    /**
     * Get the formatted address string.
     */
    public function getAddressAttribute(): string
    {
        return $this->getNepaliAddress();
    }
}
```

#### Dynamic Model Accessors

The trait provides convenient Eloquent attribute accessors that automatically resolve administrative IDs into human-readable names based on the active locale:

```php
$user = User::find(1);

// Automatic name resolution
$user->province_name;       // "Bagmati Pradesh" (or "बागमती प्रदेश")
$user->district_name;       // "Kathmandu" (or "काठमाडौं")
$user->municipality_name;   // "Kathmandu Metropolitan City" (or "काठमाडौं महानगरपालिका")
$user->local_body_name;     // Alias for municipality_name

// Formatted address strings
$user->address;             // "Kathmandu-4, Kathmandu, Bagmati Pradesh" (via getAddressAttribute)
$user->nepali_address;      // "Kathmandu-4, Kathmandu, Bagmati Pradesh"
$user->full_nepali_address; // Alias for nepali_address
```

#### Helper Methods & Multi-Address Prefix Support

When your model stores multiple addresses (e.g. `billing_` and `shipping_`), or when you need custom separators or to exclude ward numbers, call the helper methods directly:

```php
// Resolve names with custom prefix
$user->getProvinceName('billing_');     // "Bagmati Pradesh"
$user->getDistrictName('billing_');     // "Kathmandu"
$user->getLocalBodyName('billing_');    // "Kathmandu Metropolitan City"

// Format address with custom prefix, ward toggle, and separator
$user->getNepaliAddress(
    prefix: 'billing_',
    withWard: true,
    separator: ' - '
);
// "Kathmandu-4 - Kathmandu - Bagmati Pradesh"

// Address without ward
$user->getNepaliAddress('shipping_', withWard: false);
// "Kathmandu, Bagmati Pradesh"
```

#### Eloquent Query Scopes

Filter records by province, district, local body, ward, or search terms. All query scopes accept an optional `$prefix` argument for multi-address models:

```php
// Find all users in Bagmati Province (province_id = 3)
User::whereProvince(3)->get();
User::whereProvince(3, prefix: 'billing_')->get();

// Find all users in Kathmandu District (district_id = 5)
User::whereDistrict(5)->get();
User::whereDistrict(5, prefix: 'shipping_')->get();

// Find all users in a specific local body or ward
User::whereLocalBody(270)->get();
User::whereWard(4)->get();
User::whereWard(4, prefix: 'billing_')->get();

// Smart address search (English or Devanagari script)
User::whereNepaliAddress('Kathmandu')->get();
User::whereNepaliAddress('काठमाडौं')->get();
User::whereNepaliAddress('Pokhara', prefix: 'shipping_')->get();
```

> [!TIP]
> **How `whereNepaliAddress()` works**: It translates your search text into matching local body, district, and province IDs in-memory and queries the database using indexed integer `whereIn(...)` statements. If no locations match, it safely produces an empty result (`1 = 0`) without full-table scans.

---

### 6. Standalone Address Helper (`AddressData`)

You can access the static address helper anywhere in your application:

```php
use Dev1191\FilamentNepaliAddress\Support\AddressData;

// Get all provinces
$provinces = AddressData::getProvinces();

// Get districts in Bagmati (province_id = 3)
$districts = AddressData::getDistricts(3);

// Get local bodies in Kathmandu district (district_id = 5)
$localBodies = AddressData::getLocalBodies(5);

// Resolve names by ID
$name = AddressData::getProvinceName(3); // "Bagmati Pradesh"
$name = AddressData::getDistrictName(5);  // "Kathmandu"
$name = AddressData::getLocalBodyName(270); // "Kathmandu Metropolitan City"

// Format full address
$address = AddressData::formatAddress(
    provinceId: 3,
    districtId: 5,
    municipalityId: 270,
    wardNo: 4,
    separator: ', '
);
// "Kathmandu Metropolitan City-4, Kathmandu, Bagmati Pradesh"
```

---

## Localization (English & नेपाली)

The package automatically adapts to your application's active locale (`app()->getLocale()`).

To force a specific language for address names, publish `config/nepali-address.php` and set:

```php
return [
    /*
    | Options: 'en' for English, 'ne' for Devanagari Nepali
    */
    'lang' => 'ne',
];
```

When set to `'ne'`, administrative units and labels render natively:
- **Province**: प्रदेश (e.g., बागमती प्रदेश)
- **District**: जिल्ला (e.g., काठमाडौं)
- **Local Body**: स्थानीय तह / पालिका (e.g., काठमाडौं महानगरपालिका)
- **Ward**: वडा

---

## Data Source & Limitations

This plugin utilizes administrative data from [`khanaldpk/nepali-address`](https://github.com/khanaldpk/nepali-address):
- **Administrative Divisions**: 7 Provinces, 77 Districts, and 753 Local Bodies (Municipalities, Rural Municipalities, Sub-Metropolitan, Metropolitan cities).
- **Ward Numbers**: Nepal does not maintain official individual boundary IDs for wards in standard national open datasets. Ward numbers vary between 1 and 35+ per municipality. This package supports ward numbers as direct inputs or configurable selects (`->withWard()`).
- **Postal Codes**: In Nepal, postal codes correspond to specific post office branches and major towns rather than 1:1 municipality boundaries. This package provides a dedicated `NepaliPostalCodeInput` with official postal code format validation.

---

## Testing

```bash
composer test
```

Run code styling check:

```bash
composer test:lint
```

Run static analysis:

```bash
composer analyse
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

---

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

---

## Credits

- [Dev Raj Thapa](https://github.com/dev1191)
- [khanaldpk/nepali-address](https://github.com/khanaldpk/nepali-address) for the underlying dataset
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
