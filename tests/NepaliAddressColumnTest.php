<?php

use Dev1191\FilamentNepaliAddress\Columns\NepaliAddressColumn;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TestAddressModel extends Model
{
    protected $guarded = [];
}

it('formats a combined address for an eloquent record', function () {
    $provinces = AddressData::getProvinces();
    $bagmati = AddressData::findProvince(3);
    $districts = AddressData::getDistricts(3);
    $district = $districts[0];
    $bodies = AddressData::getLocalBodies((int) $district['district_id']);
    $body = $bodies[0];

    $record = new TestAddressModel([
        'province_id' => 3,
        'district_id' => (int) $district['district_id'],
        'municipality_id' => (int) $body['municipality_id'],
    ]);

    $column = NepaliAddressColumn::make('address');
    $formatted = $column->formatCombinedRecord($record);

    expect($formatted)->toContain(
        (string) $body['name'],
        (string) $district['name'],
        (string) $bagmati['name']
    );
});

it('supports custom prefix and separator in combined address column', function () {
    $record = new TestAddressModel([
        'billing_province_id' => 3,
        'billing_district_id' => 27,
        'billing_municipality_id' => 27001,
    ]);

    $column = NepaliAddressColumn::make('billing_address')
        ->addressPrefix('billing_')
        ->addressSeparator(' / ');

    expect($column->getAddressPrefix())->toBe('billing_')
        ->and($column->getAddressSeparator())->toBe(' / ')
        ->and($column->getProvinceFieldName())->toBe('billing_province_id')
        ->and($column->getDistrictFieldName())->toBe('billing_district_id')
        ->and($column->getLocalBodyFieldName())->toBe('billing_municipality_id');

    $formatted = $column->formatCombinedRecord($record);
    expect($formatted)->toContain(' / ');
});

it('supports withWard in combined address column', function () {
    $bodies = AddressData::getLocalBodies();
    $body = $bodies[0];
    $districtId = (int) $body['district_id'];

    $record = new TestAddressModel([
        'province_id' => 3,
        'district_id' => $districtId,
        'municipality_id' => (int) $body['municipality_id'],
        'ward_no' => 4,
    ]);

    $column = NepaliAddressColumn::make('address')->withWard();
    $formatted = $column->formatCombinedRecord($record);

    expect($formatted)->toContain('-4');
});

it('formats individual level columns for province, district, local body and ward', function () {
    $provinceCol = NepaliAddressColumn::province('province_id');
    expect($provinceCol->getLevel())->toBe('province')
        ->and($provinceCol->formatState(3))->toBe('Bagmati Pradesh');

    $districtCol = NepaliAddressColumn::district('district_id');
    $bagmatiDistricts = AddressData::getDistricts(3);
    $d = $bagmatiDistricts[0];
    expect($districtCol->getLevel())->toBe('district')
        ->and($districtCol->formatState((int) $d['district_id']))->toBe((string) $d['name']);

    $bodyCol = NepaliAddressColumn::localBody('municipality_id');
    $bodies = AddressData::getLocalBodies((int) $d['district_id']);
    $b = $bodies[0];
    expect($bodyCol->getLevel())->toBe('local_body')
        ->and($bodyCol->formatState((int) $b['municipality_id']))->toBe((string) $b['name']);

    $wardCol = NepaliAddressColumn::make('ward_no')->asWard();
    expect($wardCol->getLevel())->toBe('ward')
        ->and($wardCol->formatState(7))->toContain('7');
});

it('generates optimized search queries by translating search terms to matching IDs', function () {
    // 1. Search combined column
    $column = NepaliAddressColumn::make('address')->searchable();
    expect($column->isSearchable())->toBeTrue();

    $query = TestAddressModel::query();
    $reflected = new ReflectionMethod($column, 'applyAddressSearch');
    $reflected->setAccessible(true);

    /** @var Builder $resultQuery */
    $resultQuery = $reflected->invoke($column, $query, 'Bagmati');
    $sql = $resultQuery->toSql();

    // Query should include whereIn for province_id with matching Bagmati province ID (3)
    expect($sql)->toContain('province_id');

    // 2. Search district level column
    $districtCol = NepaliAddressColumn::district('district_id')->searchable();
    $dQuery = TestAddressModel::query();
    $dResult = $reflected->invoke($districtCol, $dQuery, 'Kathmandu');
    expect($dResult->toSql())->toContain('district_id');

    // 3. Search non-existent term
    $noneQuery = TestAddressModel::query();
    $noneResult = $reflected->invoke($column, $noneQuery, 'NonExistentPlaceXYZ');
    expect($noneResult->toSql())->toContain('1 = 0');
});
