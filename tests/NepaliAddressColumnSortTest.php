<?php

use Dev1191\FilamentNepaliAddress\Columns\NepaliAddressColumn;
use Illuminate\Database\Eloquent\Model;

it('provides multi-column sort defaults for combined and single level columns', function () {
    $record = new class extends Model {};

    // Combined column
    $combined = NepaliAddressColumn::make('address');
    expect($combined->getDefaultSortColumns($record))->toBe([
        'province_id',
        'district_id',
        'municipality_id',
    ]);

    // Combined column with prefix
    $prefixedCombined = NepaliAddressColumn::make('shipping')->addressPrefix('shipping_');
    expect($prefixedCombined->getDefaultSortColumns($record))->toBe([
        'shipping_province_id',
        'shipping_district_id',
        'shipping_municipality_id',
    ]);

    // Single level columns
    $province = NepaliAddressColumn::province('province_id');
    expect($province->getDefaultSortColumns($record))->toBe(['province_id']);

    $district = NepaliAddressColumn::district('district_id');
    expect($district->getDefaultSortColumns($record))->toBe(['district_id']);

    $localBody = NepaliAddressColumn::localBody('municipality_id');
    expect($localBody->getDefaultSortColumns($record))->toBe(['municipality_id']);

    $ward = NepaliAddressColumn::ward('ward_no');
    expect($ward->getDefaultSortColumns($record))->toBe(['ward_no']);
});
