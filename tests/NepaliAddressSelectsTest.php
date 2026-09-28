<?php

use Dev1191\FilamentNepaliAddress\Forms\Components\NepaliAddressSelects;
use Dev1191\FilamentNepaliAddress\Forms\Components\NepaliPostalCodeInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

it('creates a default group with 3 columns and default field names', function () {
    $selects = NepaliAddressSelects::make();

    expect($selects->getColumns('lg'))->toBe(3)
        ->and($selects->getProvinceFieldName())->toBe('province_id')
        ->and($selects->getDistrictFieldName())->toBe('district_id')
        ->and($selects->getLocalBodyFieldName())->toBe('municipality_id');

    $components = $selects->getChildComponents();
    expect($components)->toHaveCount(3);
    expect($components[0])->toBeInstanceOf(Select::class);
    expect($components[0]->getName())->toBe('province_id');
    expect($components[1])->toBeInstanceOf(Select::class);
    expect($components[1]->getName())->toBe('district_id');
    expect($components[2])->toBeInstanceOf(Select::class);
    expect($components[2]->getName())->toBe('municipality_id');
});

it('supports custom prefix for multiple addresses on the same form', function () {
    $shipping = NepaliAddressSelects::make('shipping_');

    expect($shipping->getPrefix())->toBe('shipping_')
        ->and($shipping->getProvinceFieldName())->toBe('shipping_province_id')
        ->and($shipping->getDistrictFieldName())->toBe('shipping_district_id')
        ->and($shipping->getLocalBodyFieldName())->toBe('shipping_municipality_id');

    $components = $shipping->getChildComponents();
    expect($components[0]->getName())->toBe('shipping_province_id');
    expect($components[1]->getName())->toBe('shipping_district_id');
    expect($components[2]->getName())->toBe('shipping_municipality_id');
});

it('supports overriding individual field names', function () {
    $selects = NepaliAddressSelects::make()
        ->provinceFieldName('state_code')
        ->districtFieldName('county_id')
        ->localBodyFieldName('city_id');

    expect($selects->getProvinceFieldName())->toBe('state_code')
        ->and($selects->getDistrictFieldName())->toBe('county_id')
        ->and($selects->getLocalBodyFieldName())->toBe('city_id');

    $components = $selects->getChildComponents();
    expect($components[0]->getName())->toBe('state_code');
    expect($components[1]->getName())->toBe('county_id');
    expect($components[2]->getName())->toBe('city_id');
});

it('can optionally include ward field', function () {
    $withNumericWard = NepaliAddressSelects::make()->withWard();
    $components = $withNumericWard->getChildComponents();
    expect($components)->toHaveCount(4);
    expect($components[3])->toBeInstanceOf(TextInput::class);
    expect($components[3]->getName())->toBe('ward_no');

    // Ward with custom options select
    $withSelectWard = NepaliAddressSelects::make()
        ->withWard(fieldName: 'custom_ward', options: [1 => 'Ward 1', 2 => 'Ward 2']);
    $selectComponents = $withSelectWard->getChildComponents();
    expect($selectComponents)->toHaveCount(4);
    expect($selectComponents[3])->toBeInstanceOf(Select::class);
    expect($selectComponents[3]->getName())->toBe('custom_ward');
});

it('can optionally include postal code input', function () {
    $withPostal = NepaliAddressSelects::make()->withPostalCode();
    $components = $withPostal->getChildComponents();
    expect($components)->toHaveCount(4);
    expect($components[3])->toBeInstanceOf(NepaliPostalCodeInput::class);
    expect($components[3]->getName())->toBe('postal_code');
});

it('provides standalone factory methods for individual fields', function () {
    $province = NepaliAddressSelects::makeProvinceSelect('custom_province');
    expect($province)->toBeInstanceOf(Select::class)
        ->and($province->getName())->toBe('custom_province')
        ->and($province->getOptions())->toHaveCount(7);

    $district = NepaliAddressSelects::makeDistrictSelect('custom_district', 'custom_province');
    expect($district)->toBeInstanceOf(Select::class)
        ->and($district->getName())->toBe('custom_district');

    $localBody = NepaliAddressSelects::makeLocalBodySelect('custom_body', 'custom_district');
    expect($localBody)->toBeInstanceOf(Select::class)
        ->and($localBody->getName())->toBe('custom_body');

    $postal = NepaliAddressSelects::makePostalCodeInput('custom_postal');
    expect($postal)->toBeInstanceOf(NepaliPostalCodeInput::class)
        ->and($postal->getName())->toBe('custom_postal');
});

it('populates cascading district and local body options based on selected parent', function () {
    $selects = NepaliAddressSelects::make();
    $components = $selects->getChildComponents();

    $provinceSelect = $components[0];
    $districtSelect = $components[1];
    $localBodySelect = $components[2];

    // Province options has 7 provinces
    $provinceOptions = $provinceSelect->evaluate(
        (new ReflectionProperty($provinceSelect, 'options'))->getValue($provinceSelect)
    );
    expect($provinceOptions)->toHaveCount(7)
        ->and($provinceOptions[3])->toBe('Bagmati Pradesh');

    // District options with no province selected
    $getMockEmpty = Mockery::mock(Get::class);
    $getMockEmpty->allows('__invoke')->with('province_id')->andReturn(null);

    $districtOptionsClosure = (new ReflectionProperty($districtSelect, 'options'))->getValue($districtSelect);
    $emptyDistrictOptions = $districtSelect->evaluate($districtOptionsClosure, [
        'get' => $getMockEmpty,
        Get::class => $getMockEmpty,
    ]);
    expect($emptyDistrictOptions)->toBe([]);

    // District options with Bagmati (province 3) selected
    $getMockBagmati = Mockery::mock(Get::class);
    $getMockBagmati->allows('__invoke')->with('province_id')->andReturn(3);

    $bagmatiDistrictOptions = $districtSelect->evaluate($districtOptionsClosure, [
        'get' => $getMockBagmati,
        Get::class => $getMockBagmati,
    ]);
    expect($bagmatiDistrictOptions)->toHaveKey(5) // Kathmandu
        ->and($bagmatiDistrictOptions[5])->toBe('Kathmandu');

    // Local body options with Kathmandu (district 5) selected
    $getMockKathmandu = Mockery::mock(Get::class);
    $getMockKathmandu->allows('__invoke')->with('district_id')->andReturn(5);

    $localBodyOptionsClosure = (new ReflectionProperty($localBodySelect, 'options'))->getValue($localBodySelect);
    $ktmLocalBodies = $localBodySelect->evaluate($localBodyOptionsClosure, [
        'get' => $getMockKathmandu,
        Get::class => $getMockKathmandu,
    ]);
    expect($ktmLocalBodies)->not->toBeEmpty();
});

it('clears dependent fields on state updated hooks', function () {
    $selects = NepaliAddressSelects::make();
    $components = $selects->getChildComponents();

    $provinceSelect = $components[0];
    $districtSelect = $components[1];

    $setCalls = [];
    $mockSet = Mockery::mock(Set::class);
    $mockSet->allows('__invoke')->andReturnUsing(function ($field, $value) use (&$setCalls) {
        $setCalls[$field] = $value;
    });

    // Test province afterStateUpdated hook
    $provinceHooks = (new ReflectionProperty($provinceSelect, 'afterStateUpdated'))->getValue($provinceSelect);
    $provinceSelect->evaluate($provinceHooks[0], [
        'set' => $mockSet,
        Set::class => $mockSet,
    ]);

    expect($setCalls)->toHaveKey('district_id', null)
        ->and($setCalls)->toHaveKey('municipality_id', null);

    // Test district afterStateUpdated hook
    $setCalls = [];
    $districtHooks = (new ReflectionProperty($districtSelect, 'afterStateUpdated'))->getValue($districtSelect);
    $districtSelect->evaluate($districtHooks[0], [
        'set' => $mockSet,
        Set::class => $mockSet,
    ]);

    expect($setCalls)->toHaveKey('municipality_id', null);
});
