<?php

use Dev1191\FilamentNepaliAddress\Filters\NepaliAddressFilter;
use Dev1191\FilamentNepaliAddress\Forms\Components\NepaliAddressSelects;
use Filament\Schemas\Components\Utilities\Get;

it('restricts provinces and districts on form component with only and except', function () {
    // Only Province 3
    $selects = NepaliAddressSelects::make()
        ->onlyProvinces([3])
        ->onlyDistricts([5, 6]);

    $components = $selects->getChildComponents();
    $provinceSelect = $components[0];
    $districtSelect = $components[1];

    $provinceOptions = $provinceSelect->evaluate(
        (new ReflectionProperty($provinceSelect, 'options'))->getValue($provinceSelect)
    );
    expect($provinceOptions)->toHaveCount(1)
        ->and(array_keys($provinceOptions))->toBe([3]);

    $getMock = Mockery::mock(Get::class);
    $getMock->allows('__invoke')->with('province_id')->andReturn(3);

    $districtOptionsClosure = (new ReflectionProperty($districtSelect, 'options'))->getValue($districtSelect);
    $districtOptions = $districtSelect->evaluate($districtOptionsClosure, [
        'get' => $getMock,
        Get::class => $getMock,
    ]);

    // Only districts 5 (Kathmandu) and 6 (Lalitpur)
    expect($districtOptions)->toHaveCount(2)
        ->and(array_keys($districtOptions))->toBe([5, 6]);

    // Except province
    $exceptSelects = NepaliAddressSelects::make()
        ->exceptProvinces([1, 2, 4, 5, 6, 7]);

    $exceptComponents = $exceptSelects->getChildComponents();
    $exceptProvinceSelect = $exceptComponents[0];
    $exceptProvinceOptions = $exceptProvinceSelect->evaluate(
        (new ReflectionProperty($exceptProvinceSelect, 'options'))->getValue($exceptProvinceSelect)
    );
    expect($exceptProvinceOptions)->toHaveCount(1)
        ->and(array_keys($exceptProvinceOptions))->toBe([3]);
});

it('restricts provinces and districts on table filter with only and except', function () {
    $filter = NepaliAddressFilter::make()
        ->onlyProvinces([3])
        ->onlyDistricts([5]);

    $schema = $filter->buildFormSchema();
    $provinceSelect = $schema[0];
    $districtSelect = $schema[1];

    $provinceOptions = $provinceSelect->evaluate(
        (new ReflectionProperty($provinceSelect, 'options'))->getValue($provinceSelect)
    );
    expect($provinceOptions)->toHaveCount(1)
        ->and(array_keys($provinceOptions))->toBe([3]);

    $getMock = Mockery::mock(Get::class);
    $getMock->allows('__invoke')->with('province_id')->andReturn(3);

    $districtOptionsClosure = (new ReflectionProperty($districtSelect, 'options'))->getValue($districtSelect);
    $districtOptions = $districtSelect->evaluate($districtOptionsClosure, [
        'get' => $getMock,
        Get::class => $getMock,
    ]);

    expect($districtOptions)->toHaveCount(1)
        ->and(array_keys($districtOptions))->toBe([5]);
});
