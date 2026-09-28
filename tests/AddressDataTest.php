<?php

use Dev1191\FilamentNepaliAddress\Support\AddressData;

beforeEach(function () {
    AddressData::flushCache();
    config(['nepali-address.lang' => 'en']);
});

it('can fetch all provinces', function () {
    $provinces = AddressData::getProvinces();

    expect($provinces)->toBeArray()
        ->and(count($provinces))->toBe(7)
        ->and($provinces[0])->toHaveKeys(['province_id', 'name', 'nepali_name']);
});

it('can fetch all districts or filter by province id', function () {
    $allDistricts = AddressData::getDistricts();
    expect($allDistricts)->toBeArray()
        ->and(count($allDistricts))->toBe(77);

    // Province 3 is Bagmati Pradesh (has 13 districts)
    $bagmatiDistricts = AddressData::getDistricts(3);
    expect($bagmatiDistricts)->toBeArray()
        ->and(count($bagmatiDistricts))->toBe(13);

    foreach ($bagmatiDistricts as $district) {
        expect((int) $district['province_id'])->toBe(3);
    }
});

it('can fetch all local bodies or filter by district id', function () {
    $allLocalBodies = AddressData::getLocalBodies();
    expect($allLocalBodies)->toBeArray()
        ->and(count($allLocalBodies))->toBe(753);

    // Filter by district (e.g. Chitwan = 2)
    $chitwanBodies = AddressData::getLocalBodies(2);
    expect($chitwanBodies)->toBeArray()
        ->and(count($chitwanBodies))->toBeGreaterThan(0);

    foreach ($chitwanBodies as $body) {
        expect((int) $body['district_id'])->toBe(2);
    }
});

it('provides options arrays for select fields', function () {
    $provinceOptions = AddressData::getProvinceOptions();
    expect($provinceOptions)->toBeArray()
        ->and(count($provinceOptions))->toBe(7)
        ->and(array_keys($provinceOptions))->toContain(1, 2, 3, 4, 5, 6, 7);

    $districtOptions = AddressData::getDistrictOptions(3);
    expect($districtOptions)->toBeArray()
        ->and(count($districtOptions))->toBe(13);

    $localBodyOptions = AddressData::getLocalBodyOptions(2);
    expect($localBodyOptions)->toBeArray()
        ->and(count($localBodyOptions))->toBeGreaterThan(0);
});

it('finds records and resolves names by id', function () {
    $province = AddressData::findProvince(3);
    expect($province)->not->toBeNull()
        ->and((int) $province['province_id'])->toBe(3);

    expect(AddressData::getProvinceName(3))->toBe('Bagmati Pradesh');
    expect(AddressData::getProvinceName(9999))->toBeNull();

    // Check district (e.g. Kathmandu)
    $kathmandu = AddressData::findDistrict(27);
    if ($kathmandu) {
        expect(AddressData::getDistrictName(27))->toBe($kathmandu['name']);
    }

    expect(AddressData::findDistrict(null))->toBeNull();
    expect(AddressData::findLocalBody(null))->toBeNull();
});

it('formats address into a human readable string', function () {
    // Pick an existing local body
    $bodies = AddressData::getLocalBodies();
    $firstBody = $bodies[0];
    $municipalityId = (int) $firstBody['municipality_id'];
    $districtId = (int) $firstBody['district_id'];
    $district = AddressData::findDistrict($districtId);
    $provinceId = (int) $district['province_id'];

    $formatted = AddressData::formatAddress(
        provinceId: $provinceId,
        districtId: $districtId,
        municipalityId: $municipalityId,
    );

    expect($formatted)->toBeString()
        ->and($formatted)->toContain(
            (string) $firstBody['name'],
            (string) $district['name'],
            (string) AddressData::getProvinceName($provinceId),
        );
});

it('validates relationship consistency between province, district and local body', function () {
    // All 13 districts in Bagmati (province 3)
    $bagmatiDistricts = AddressData::getDistricts(3);
    $firstDistrict = $bagmatiDistricts[0];
    $districtId = (int) $firstDistrict['district_id'];

    expect(AddressData::districtBelongsToProvince($districtId, 3))->toBeTrue();
    expect(AddressData::districtBelongsToProvince($districtId, 1))->toBeFalse();

    // Local bodies in that district
    $localBodies = AddressData::getLocalBodies($districtId);
    $firstBody = $localBodies[0];
    $municipalityId = (int) $firstBody['municipality_id'];

    expect(AddressData::localBodyBelongsToDistrict($municipalityId, $districtId))->toBeTrue();
    expect(AddressData::localBodyBelongsToDistrict($municipalityId, 9999))->toBeFalse();

    expect(AddressData::isValidProvince(3))->toBeTrue();
    expect(AddressData::isValidProvince(999))->toBeFalse();

    expect(AddressData::isValidDistrict($districtId, 3))->toBeTrue();
    expect(AddressData::isValidDistrict($districtId, 1))->toBeFalse();

    expect(AddressData::isValidLocalBody($municipalityId, $districtId))->toBeTrue();
    expect(AddressData::isValidLocalBody($municipalityId, 9999))->toBeFalse();
});

it('respects localized nepali language configuration', function () {
    config(['nepali-address.lang' => 'ne']);
    AddressData::flushCache();

    $provinces = AddressData::getProvinces();
    expect($provinces[0]['name'])->toBe($provinces[0]['nepali_name']);

    $name = AddressData::getProvinceName(3);
    expect($name)->toBe('बागमती प्रदेश');
});
