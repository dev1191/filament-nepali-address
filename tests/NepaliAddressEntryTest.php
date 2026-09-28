<?php

use Dev1191\FilamentNepaliAddress\Infolists\Components\NepaliAddressEntry;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Illuminate\Database\Eloquent\Model;

class TestEntryModel extends Model
{
    protected $guarded = [];
}

it('formats a combined address for an eloquent record in infolist', function () {
    $provinces = AddressData::getProvinces();
    $bagmati = AddressData::findProvince(3);
    $districts = AddressData::getDistricts(3);
    $district = $districts[0];
    $bodies = AddressData::getLocalBodies((int) $district['district_id']);
    $body = $bodies[0];

    $record = new TestEntryModel([
        'province_id' => 3,
        'district_id' => (int) $district['district_id'],
        'municipality_id' => (int) $body['municipality_id'],
    ]);

    $entry = NepaliAddressEntry::make('address');
    $formatted = $entry->formatCombinedRecord($record);

    expect($formatted)->toContain(
        (string) $body['name'],
        (string) $district['name'],
        (string) $bagmati['name']
    );
});

it('supports custom address prefix and separator in infolist entry', function () {
    $record = new TestEntryModel([
        'billing_province_id' => 3,
        'billing_district_id' => 27,
        'billing_municipality_id' => 27001,
    ]);

    $entry = NepaliAddressEntry::make('billing_address')
        ->addressPrefix('billing_')
        ->addressSeparator(' | ');

    expect($entry->getAddressPrefix())->toBe('billing_')
        ->and($entry->getAddressSeparator())->toBe(' | ')
        ->and($entry->getProvinceFieldName())->toBe('billing_province_id')
        ->and($entry->getDistrictFieldName())->toBe('billing_district_id')
        ->and($entry->getLocalBodyFieldName())->toBe('billing_municipality_id');

    $formatted = $entry->formatCombinedRecord($record);
    expect($formatted)->toContain(' | ');
});

it('supports withWard in combined infolist entry', function () {
    $bodies = AddressData::getLocalBodies();
    $body = $bodies[0];
    $districtId = (int) $body['district_id'];

    $record = new TestEntryModel([
        'province_id' => 3,
        'district_id' => $districtId,
        'municipality_id' => (int) $body['municipality_id'],
        'ward_no' => 9,
    ]);

    $entry = NepaliAddressEntry::make('address')->withWard();
    $formatted = $entry->formatCombinedRecord($record);

    expect($formatted)->toContain('-9');
});

it('formats individual level infolist entries for province, district, local body and ward', function () {
    $provinceEntry = NepaliAddressEntry::province('province_id');
    expect($provinceEntry->getLevel())->toBe('province')
        ->and($provinceEntry->formatState(3))->toBe('Bagmati Pradesh');

    $districtEntry = NepaliAddressEntry::district('district_id');
    $districts = AddressData::getDistricts(3);
    $d = $districts[0];
    expect($districtEntry->getLevel())->toBe('district')
        ->and($districtEntry->formatState((int) $d['district_id']))->toBe((string) $d['name']);

    $bodyEntry = NepaliAddressEntry::localBody('municipality_id');
    $bodies = AddressData::getLocalBodies((int) $d['district_id']);
    $b = $bodies[0];
    expect($bodyEntry->getLevel())->toBe('local_body')
        ->and($bodyEntry->formatState((int) $b['municipality_id']))->toBe((string) $b['name']);

    $wardEntry = NepaliAddressEntry::make('ward_no')->asWard();
    expect($wardEntry->getLevel())->toBe('ward')
        ->and($wardEntry->formatState(12))->toContain('12');
});
