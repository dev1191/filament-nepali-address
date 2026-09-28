<?php

namespace Dev1191\FilamentNepaliAddress\Infolists\Components;

use Closure;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;

class NepaliAddressEntry extends TextEntry
{
    protected string $level = 'combined';

    protected ?string $addressPrefix = null;

    protected ?string $provinceFieldName = null;

    protected ?string $districtFieldName = null;

    protected ?string $localBodyFieldName = null;

    protected ?string $wardFieldName = null;

    protected ?string $postalCodeFieldName = null;

    protected string $addressSeparator = ', ';

    protected bool $hasWard = false;

    protected bool $hasPostalCode = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatStateUsing(function (mixed $state, ?Model $record): ?string {
            if ($this->level === 'combined') {
                if ($record === null) {
                    return null;
                }

                return $this->formatCombinedRecord($record);
            }

            if ($state === null || $state === '') {
                return null;
            }

            return match ($this->level) {
                'province' => AddressData::getProvinceName((int) $state),
                'district' => AddressData::getDistrictName((int) $state),
                'local_body' => AddressData::getLocalBodyName((int) $state),
                'ward' => __('nepali-address::nepali-address.ward') . ' ' . $state,
                default => (string) $state,
            };
        });
    }

    public function getRecord(bool $withContainerRecord = true): Model | array | null
    {
        if (! isset($this->container)) {
            $model = $this->evaluate($this->model);

            if (($model instanceof Model) || is_array($model)) {
                return $model;
            }

            return null;
        }

        return parent::getRecord($withContainerRecord);
    }

    public function level(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function asProvince(): static
    {
        $this->level = 'province';
        $this->label(__('nepali-address::nepali-address.province'));

        return $this;
    }

    public function asDistrict(): static
    {
        $this->level = 'district';
        $this->label(__('nepali-address::nepali-address.district'));

        return $this;
    }

    public function asLocalBody(): static
    {
        $this->level = 'local_body';
        $this->label(__('nepali-address::nepali-address.local_body'));

        return $this;
    }

    public function asWard(): static
    {
        $this->level = 'ward';
        $this->label(__('nepali-address::nepali-address.ward'));

        return $this;
    }

    public function asCombined(): static
    {
        $this->level = 'combined';

        return $this;
    }

    public function addressPrefix(?string $prefix): static
    {
        $this->addressPrefix = $prefix;

        return $this;
    }

    public function getAddressPrefix(): ?string
    {
        return $this->addressPrefix;
    }

    public function provinceFieldName(string $name): static
    {
        $this->provinceFieldName = $name;

        return $this;
    }

    public function getProvinceFieldName(): string
    {
        return $this->provinceFieldName ?? ($this->addressPrefix . 'province_id');
    }

    public function districtFieldName(string $name): static
    {
        $this->districtFieldName = $name;

        return $this;
    }

    public function getDistrictFieldName(): string
    {
        return $this->districtFieldName ?? ($this->addressPrefix . 'district_id');
    }

    public function localBodyFieldName(string $name): static
    {
        $this->localBodyFieldName = $name;

        return $this;
    }

    public function getLocalBodyFieldName(): string
    {
        return $this->localBodyFieldName ?? ($this->addressPrefix . 'municipality_id');
    }

    public function wardFieldName(string $name): static
    {
        $this->wardFieldName = $name;

        return $this;
    }

    public function getWardFieldName(): string
    {
        return $this->wardFieldName ?? ($this->addressPrefix . 'ward_no');
    }

    public function postalCodeFieldName(string $name): static
    {
        $this->postalCodeFieldName = $name;

        return $this;
    }

    public function getPostalCodeFieldName(): string
    {
        return $this->postalCodeFieldName ?? ($this->addressPrefix . 'postal_code');
    }

    public function addressSeparator(string $separator): static
    {
        $this->addressSeparator = $separator;

        return $this;
    }

    public function getAddressSeparator(): string
    {
        return $this->addressSeparator;
    }

    public function separator(string | Closure | null $separator = ','): static
    {
        if (is_string($separator)) {
            $this->addressSeparator = $separator;
        }

        return $this;
    }

    public function withWard(bool $condition = true, ?string $fieldName = null): static
    {
        $this->hasWard = $condition;

        if ($fieldName !== null) {
            $this->wardFieldName = $fieldName;
        }

        return $this;
    }

    public function hasWard(): bool
    {
        return $this->hasWard;
    }

    public function withPostalCode(bool $condition = true, ?string $fieldName = null): static
    {
        $this->hasPostalCode = $condition;

        if ($fieldName !== null) {
            $this->postalCodeFieldName = $fieldName;
        }

        return $this;
    }

    public function hasPostalCode(): bool
    {
        return $this->hasPostalCode;
    }

    /**
     * Format a combined address for an Eloquent record.
     */
    public function formatCombinedRecord(Model $record): string
    {
        $parts = [];

        $localBodyVal = $record->getAttribute($this->getLocalBodyFieldName());
        $localBodyName = filled($localBodyVal) ? AddressData::getLocalBodyName((int) $localBodyVal) : null;

        if ($this->hasWard()) {
            $wardVal = $record->getAttribute($this->getWardFieldName());
            if (filled($wardVal)) {
                $localBodyName = filled($localBodyName)
                    ? "{$localBodyName}-{$wardVal}"
                    : (__('nepali-address::nepali-address.ward') . ' ' . $wardVal);
            }
        }

        if (filled($localBodyName)) {
            $parts[] = $localBodyName;
        }

        $districtVal = $record->getAttribute($this->getDistrictFieldName());
        if (filled($districtVal)) {
            $districtName = AddressData::getDistrictName((int) $districtVal);
            if (filled($districtName)) {
                $parts[] = $districtName;
            }
        }

        $provinceVal = $record->getAttribute($this->getProvinceFieldName());
        if (filled($provinceVal)) {
            $provinceName = AddressData::getProvinceName((int) $provinceVal);
            if (filled($provinceName)) {
                $parts[] = $provinceName;
            }
        }

        if ($this->hasPostalCode()) {
            $postalVal = $record->getAttribute($this->getPostalCodeFieldName());
            if (filled($postalVal)) {
                $parts[] = (string) $postalVal;
            }
        }

        return implode($this->addressSeparator, $parts);
    }

    /**
     * Shorthand factory for Province infolist entry.
     */
    public static function province(?string $name = 'province_id'): static
    {
        return static::make($name ?? 'province_id')->asProvince();
    }

    /**
     * Shorthand factory for District infolist entry.
     */
    public static function district(?string $name = 'district_id'): static
    {
        return static::make($name ?? 'district_id')->asDistrict();
    }

    /**
     * Shorthand factory for Local Body infolist entry.
     */
    public static function localBody(?string $name = 'municipality_id'): static
    {
        return static::make($name ?? 'municipality_id')->asLocalBody();
    }

    /**
     * Shorthand factory for Ward infolist entry.
     */
    public static function ward(?string $name = 'ward_no'): static
    {
        return static::make($name ?? 'ward_no')->asWard();
    }

    /**
     * Shorthand factory for Combined Address infolist entry.
     */
    public static function combined(?string $name = 'address', ?string $prefix = null): static
    {
        $static = static::make($name ?? 'address')->asCombined();

        if ($prefix !== null) {
            $static->addressPrefix($prefix);
        }

        return $static;
    }
}
