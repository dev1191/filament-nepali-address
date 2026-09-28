<?php

use Dev1191\FilamentNepaliAddress\Filters\NepaliAddressFilter;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Model;

class TestFilterModel extends Model
{
    protected $table = 'test_filter_models';

    protected $guarded = [];
}

it('creates a combined table filter with province, district, and local body selects', function () {
    $filter = NepaliAddressFilter::make();

    expect($filter->getName())->toBe('nepali_address')
        ->and($filter->getLevel())->toBe('combined');

    $schema = $filter->buildFormSchema();
    expect($schema)->toHaveCount(3);
    expect($schema[0])->toBeInstanceOf(Select::class);
    expect($schema[0]->getName())->toBe('province_id');
    expect($schema[1])->toBeInstanceOf(Select::class);
    expect($schema[1]->getName())->toBe('district_id');
    expect($schema[2])->toBeInstanceOf(Select::class);
    expect($schema[2]->getName())->toBe('municipality_id');
});

it('supports custom prefix and ward field in filter', function () {
    $filter = NepaliAddressFilter::make('shipping_address')
        ->prefix('shipping_')
        ->withWard();

    expect($filter->getProvinceFieldName())->toBe('shipping_province_id')
        ->and($filter->getDistrictFieldName())->toBe('shipping_district_id')
        ->and($filter->getLocalBodyFieldName())->toBe('shipping_municipality_id')
        ->and($filter->getWardFieldName())->toBe('shipping_ward_no');

    $schema = $filter->buildFormSchema();
    expect($schema)->toHaveCount(4);
    expect($schema[3])->toBeInstanceOf(TextInput::class);
    expect($schema[3]->getName())->toBe('shipping_ward_no');
});

it('supports single-level filters for province, district, and local body', function () {
    $provinceFilter = NepaliAddressFilter::province();
    expect($provinceFilter->getLevel())->toBe('province');
    $pSchema = $provinceFilter->buildFormSchema();
    expect($pSchema)->toHaveCount(1)
        ->and($pSchema[0]->getName())->toBe('province_id');

    $districtFilter = NepaliAddressFilter::district('custom_district_filter', 'custom_district_id');
    expect($districtFilter->getLevel())->toBe('district');
    $dSchema = $districtFilter->buildFormSchema();
    expect($dSchema)->toHaveCount(1)
        ->and($dSchema[0]->getName())->toBe('custom_district_id');

    $localBodyFilter = NepaliAddressFilter::localBody();
    expect($localBodyFilter->getLevel())->toBe('local_body');
    $lSchema = $localBodyFilter->buildFormSchema();
    expect($lSchema)->toHaveCount(1)
        ->and($lSchema[0]->getName())->toBe('municipality_id');
});

it('applies query constraints according to selected filter values', function () {
    $filter = NepaliAddressFilter::make()->withWard();

    $query = TestFilterModel::query();
    $filter->applyAddressFilter($query, [
        'province_id' => 3,
        'district_id' => 27,
        'municipality_id' => 2701,
        'ward_no' => 5,
    ]);

    $sql = $query->toSql();
    expect($sql)->toContain('province_id')
        ->and($sql)->toContain('district_id')
        ->and($sql)->toContain('municipality_id')
        ->and($sql)->toContain('ward_no');

    $bindings = $query->getBindings();
    expect($bindings)->toContain(3, 27, 2701, 5);
});

it('builds human readable active filter indicators', function () {
    $provinces = AddressData::getProvinces();
    $districts = AddressData::getDistricts(3);
    $d = $districts[0];
    $bodies = AddressData::getLocalBodies((int) $d['district_id']);
    $b = $bodies[0];

    $filter = NepaliAddressFilter::make()->withWard();

    $indicators = $filter->buildIndicators([
        'province_id' => 3,
        'district_id' => (int) $d['district_id'],
        'municipality_id' => (int) $b['municipality_id'],
        'ward_no' => 3,
    ]);

    expect($indicators)->toHaveCount(4);
    expect($indicators[0])->toBeInstanceOf(Indicator::class);
    expect($indicators[0]->getLabel())->toContain('Bagmati Pradesh');
    expect($indicators[1]->getLabel())->toContain((string) $d['name']);
    expect($indicators[2]->getLabel())->toContain((string) $b['name']);
    expect($indicators[3]->getLabel())->toContain('3');
});

it('provides clean reset state', function () {
    $filter = NepaliAddressFilter::make('shipping')->prefix('shipping_');
    $reset = $filter->getResetState();

    expect($reset)->toBe([
        'shipping_province_id' => null,
        'shipping_district_id' => null,
        'shipping_municipality_id' => null,
        'shipping_ward_no' => null,
    ]);
});
