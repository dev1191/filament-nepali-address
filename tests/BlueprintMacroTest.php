<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

it('creates standard nepali address columns using blueprint macro', function () {
    Schema::create('test_addresses', function (Blueprint $table) {
        $table->id();
        $table->nepaliAddress();
    });

    expect(Schema::hasColumns('test_addresses', [
        'province_id',
        'district_id',
        'municipality_id',
        'ward_no',
        'postal_code',
    ]))->toBeTrue();
});

it('creates prefixed nepali address columns using blueprint macro', function () {
    Schema::create('test_prefixed_addresses', function (Blueprint $table) {
        $table->id();
        $table->nepaliAddress('billing_');
        $table->nepaliAddress('shipping_', withWard: false, withPostalCode: false);
    });

    expect(Schema::hasColumns('test_prefixed_addresses', [
        'billing_province_id',
        'billing_district_id',
        'billing_municipality_id',
        'billing_ward_no',
        'billing_postal_code',
    ]))->toBeTrue();

    expect(Schema::hasColumns('test_prefixed_addresses', [
        'shipping_province_id',
        'shipping_district_id',
        'shipping_municipality_id',
    ]))->toBeTrue();

    expect(Schema::hasColumn('test_prefixed_addresses', 'shipping_ward_no'))->toBeFalse()
        ->and(Schema::hasColumn('test_prefixed_addresses', 'shipping_postal_code'))->toBeFalse();
});

it('drops nepali address columns using blueprint macro', function () {
    Schema::create('test_drop_addresses', function (Blueprint $table) {
        $table->id();
        $table->nepaliAddress('work_');
    });

    expect(Schema::hasColumn('test_drop_addresses', 'work_province_id'))->toBeTrue();

    Schema::table('test_drop_addresses', function (Blueprint $table) {
        $table->dropNepaliAddress('work_');
    });

    expect(Schema::hasColumn('test_drop_addresses', 'work_province_id'))->toBeFalse()
        ->and(Schema::hasColumn('test_drop_addresses', 'work_district_id'))->toBeFalse()
        ->and(Schema::hasColumn('test_drop_addresses', 'work_municipality_id'))->toBeFalse();
});
