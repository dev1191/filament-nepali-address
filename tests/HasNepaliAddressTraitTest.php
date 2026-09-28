<?php

use Dev1191\FilamentNepaliAddress\Traits\HasNepaliAddress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestUserModel extends Model
{
    use HasNepaliAddress;

    protected $table = 'test_user_models';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('test_user_models', function (Blueprint $table) {
        $table->id();
        $table->nepaliAddress();
        $table->nepaliAddress('billing_');
    });
});

it('provides accessors for address names and formatted string', function () {
    $user = TestUserModel::create([
        'province_id' => 3,     // Bagmati Pradesh
        'district_id' => 5,     // Kathmandu
        'municipality_id' => 5, // Kathmandu Metropolitan City
        'ward_no' => 4,
    ]);

    expect($user->province_name)->toBe('Bagmati Pradesh')
        ->and($user->district_name)->toBe('Kathmandu')
        ->and($user->municipality_name)->toBe('Kathmandu')
        ->and($user->local_body_name)->toBe('Kathmandu')
        ->and($user->nepali_address)->toBe('Kathmandu-4, Kathmandu, Bagmati Pradesh')
        ->and($user->full_nepali_address)->toBe('Kathmandu-4, Kathmandu, Bagmati Pradesh');
});

it('supports custom address prefix with model methods', function () {
    $user = TestUserModel::create([
        'billing_province_id' => 3,
        'billing_district_id' => 5,
        'billing_municipality_id' => 5,
        'billing_ward_no' => 10,
    ]);

    expect($user->getProvinceName('billing_'))->toBe('Bagmati Pradesh')
        ->and($user->getDistrictName('billing_'))->toBe('Kathmandu')
        ->and($user->getLocalBodyName('billing_'))->toBe('Kathmandu')
        ->and($user->getNepaliAddress('billing_'))->toBe('Kathmandu-10, Kathmandu, Bagmati Pradesh');
});

it('provides query scopes for filtering and searching', function () {
    TestUserModel::create([
        'province_id' => 3,
        'district_id' => 5,
        'municipality_id' => 5,
        'ward_no' => 1,
    ]);

    TestUserModel::create([
        'province_id' => 1,
        'district_id' => 4,
        'municipality_id' => 260,
        'ward_no' => 2,
    ]);

    expect(TestUserModel::whereProvince(3)->count())->toBe(1)
        ->and(TestUserModel::whereDistrict(5)->count())->toBe(1)
        ->and(TestUserModel::whereLocalBody(5)->count())->toBe(1)
        ->and(TestUserModel::whereWard(1)->count())->toBe(1)
        ->and(TestUserModel::whereNepaliAddress('Kathmandu')->count())->toBe(1)
        ->and(TestUserModel::whereNepaliAddress('NonExistentPlace')->count())->toBe(0);
});
