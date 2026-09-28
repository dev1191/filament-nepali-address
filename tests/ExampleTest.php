<?php

it('loads package configurations and translations correctly', function () {
    expect(config('nepali-address'))->toBeArray()
        ->and(__('nepali-address::nepali-address.province'))->toBe('Province')
        ->and(__('nepali-address::nepali-address.district'))->toBe('District')
        ->and(__('nepali-address::nepali-address.local_body'))->toBe('Municipality / Local Body');

    app()->setLocale('ne');
    expect(__('nepali-address::nepali-address.province'))->toBe('प्रदेश')
        ->and(__('nepali-address::nepali-address.district'))->toBe('जिल्ला')
        ->and(__('nepali-address::nepali-address.local_body'))->toBe('स्थानीय तह / पालिका');

    app()->setLocale('en');
});
