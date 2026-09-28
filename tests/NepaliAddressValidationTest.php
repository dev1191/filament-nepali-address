<?php

use Dev1191\FilamentNepaliAddress\Rules\NepaliDistrictBelongsToProvince;
use Dev1191\FilamentNepaliAddress\Rules\NepaliLocalBodyBelongsToDistrict;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Illuminate\Support\Facades\Validator;
use Khanaldpk\NepaliAddress\Rules\NepalPostalCode;

it('validates that district belongs to province using callback or static id', function () {
    // Province 3 (Bagmati), Kathmandu is in Bagmati
    $bagmatiDistricts = AddressData::getDistricts(3);
    $kathmanduId = (int) $bagmatiDistricts[0]['district_id'];

    // Koshi districts
    $koshiDistricts = AddressData::getDistricts(1);
    $koshiDistrictId = (int) $koshiDistricts[0]['district_id'];

    // 1. Matching province passes
    $validator = Validator::make(
        ['district_id' => $kathmanduId],
        ['district_id' => [new NepaliDistrictBelongsToProvince(provinceId: 3)]]
    );
    expect($validator->passes())->toBeTrue();

    // 2. Mismatched province fails
    $validatorMismatched = Validator::make(
        ['district_id' => $koshiDistrictId],
        ['district_id' => [new NepaliDistrictBelongsToProvince(provinceId: 3)]]
    );
    expect($validatorMismatched->fails())->toBeTrue();
    expect($validatorMismatched->errors()->first('district_id'))
        ->toBe(__('nepali-address::nepali-address.validation.district_belongs_to_province'));

    // 3. Invalid district ID fails
    $validatorInvalid = Validator::make(
        ['district_id' => 99999],
        ['district_id' => [new NepaliDistrictBelongsToProvince(provinceId: 3)]]
    );
    expect($validatorInvalid->fails())->toBeTrue();
    expect($validatorInvalid->errors()->first('district_id'))
        ->toBe(__('nepali-address::nepali-address.validation.invalid_district'));
});

it('validates district with DataAwareRule reading province from input data', function () {
    $bagmatiDistricts = AddressData::getDistricts(3);
    $districtId = (int) $bagmatiDistricts[0]['district_id'];

    $validator = Validator::make(
        [
            'province_id' => 3,
            'district_id' => $districtId,
        ],
        [
            'district_id' => [new NepaliDistrictBelongsToProvince(provinceAttribute: 'province_id')],
        ]
    );
    expect($validator->passes())->toBeTrue();

    // Mismatched
    $validatorFails = Validator::make(
        [
            'province_id' => 1,
            'district_id' => $districtId,
        ],
        [
            'district_id' => [new NepaliDistrictBelongsToProvince(provinceAttribute: 'province_id')],
        ]
    );
    expect($validatorFails->fails())->toBeTrue();
});

it('validates that local body belongs to district using callback or static id', function () {
    $bodies = AddressData::getLocalBodies(2); // Chitwan
    $chitwanBodyId = (int) $bodies[0]['municipality_id'];

    // 1. Matching district passes
    $validator = Validator::make(
        ['municipality_id' => $chitwanBodyId],
        ['municipality_id' => [new NepaliLocalBodyBelongsToDistrict(districtId: 2)]]
    );
    expect($validator->passes())->toBeTrue();

    // 2. Mismatched district fails
    $validatorMismatched = Validator::make(
        ['municipality_id' => $chitwanBodyId],
        ['municipality_id' => [new NepaliLocalBodyBelongsToDistrict(districtId: 27)]]
    );
    expect($validatorMismatched->fails())->toBeTrue();
    expect($validatorMismatched->errors()->first('municipality_id'))
        ->toBe(__('nepali-address::nepali-address.validation.local_body_belongs_to_district'));

    // 3. Invalid municipality ID fails
    $validatorInvalid = Validator::make(
        ['municipality_id' => 99999],
        ['municipality_id' => [new NepaliLocalBodyBelongsToDistrict(districtId: 2)]]
    );
    expect($validatorInvalid->fails())->toBeTrue();
    expect($validatorInvalid->errors()->first('municipality_id'))
        ->toBe(__('nepali-address::nepali-address.validation.invalid_local_body'));
});

it('validates postal code with NepalPostalCode rule', function () {
    // Valid Kathmandu postal code
    $validator = Validator::make(
        ['postal_code' => '44600'],
        ['postal_code' => [new NepalPostalCode]]
    );
    expect($validator->passes())->toBeTrue();

    // Invalid postal code
    $invalid = Validator::make(
        ['postal_code' => '99999'],
        ['postal_code' => [new NepalPostalCode]]
    );
    expect($invalid->fails())->toBeTrue();
});
