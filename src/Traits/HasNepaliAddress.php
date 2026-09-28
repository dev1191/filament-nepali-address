<?php

namespace Dev1191\FilamentNepaliAddress\Traits;

use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Illuminate\Database\Eloquent\Builder;

trait HasNepaliAddress
{
    /**
     * Get the formatted Nepali address string for the model.
     */
    public function getNepaliAddress(
        ?string $prefix = null,
        bool $withWard = true,
        string $separator = ', '
    ): string {
        $p = $prefix ?? '';

        $provinceId = $this->getAttribute($p . 'province_id');
        $districtId = $this->getAttribute($p . 'district_id');
        $municipalityId = $this->getAttribute($p . 'municipality_id');
        $wardNo = $withWard ? $this->getAttribute($p . 'ward_no') : null;

        return AddressData::formatAddress(
            provinceId: $provinceId !== null ? (int) $provinceId : null,
            districtId: $districtId !== null ? (int) $districtId : null,
            municipalityId: $municipalityId !== null ? (int) $municipalityId : null,
            wardNo: filled($wardNo) ? $wardNo : null,
            separator: $separator,
        );
    }

    /**
     * Get the province name for the model.
     */
    public function getProvinceName(?string $prefix = null): ?string
    {
        $val = $this->getAttribute(($prefix ?? '') . 'province_id');

        return filled($val) ? AddressData::getProvinceName((int) $val) : null;
    }

    /**
     * Get the district name for the model.
     */
    public function getDistrictName(?string $prefix = null): ?string
    {
        $val = $this->getAttribute(($prefix ?? '') . 'district_id');

        return filled($val) ? AddressData::getDistrictName((int) $val) : null;
    }

    /**
     * Get the municipality / local body name for the model.
     */
    public function getLocalBodyName(?string $prefix = null): ?string
    {
        $val = $this->getAttribute(($prefix ?? '') . 'municipality_id');

        return filled($val) ? AddressData::getLocalBodyName((int) $val) : null;
    }

    /**
     * Accessor for default address: $model->nepali_address
     */
    public function getNepaliAddressAttribute(): string
    {
        return $this->getNepaliAddress();
    }

    /**
     * Accessor for full address: $model->full_nepali_address
     */
    public function getFullNepaliAddressAttribute(): string
    {
        return $this->getNepaliAddress();
    }

    /**
     * Accessor for province name: $model->province_name
     */
    public function getProvinceNameAttribute(): ?string
    {
        return $this->getProvinceName();
    }

    /**
     * Accessor for district name: $model->district_name
     */
    public function getDistrictNameAttribute(): ?string
    {
        return $this->getDistrictName();
    }

    /**
     * Accessor for municipality name: $model->municipality_name
     */
    public function getMunicipalityNameAttribute(): ?string
    {
        return $this->getLocalBodyName();
    }

    /**
     * Accessor for local body name: $model->local_body_name
     */
    public function getLocalBodyNameAttribute(): ?string
    {
        return $this->getLocalBodyName();
    }

    /**
     * Scope query to a specific province.
     */
    public function scopeWhereProvince(Builder $query, int $provinceId, ?string $prefix = null): Builder
    {
        return $query->where(($prefix ?? '') . 'province_id', $provinceId);
    }

    /**
     * Scope query to a specific district.
     */
    public function scopeWhereDistrict(Builder $query, int $districtId, ?string $prefix = null): Builder
    {
        return $query->where(($prefix ?? '') . 'district_id', $districtId);
    }

    /**
     * Scope query to a specific municipality / local body.
     */
    public function scopeWhereLocalBody(Builder $query, int $municipalityId, ?string $prefix = null): Builder
    {
        return $query->where(($prefix ?? '') . 'municipality_id', $municipalityId);
    }

    /**
     * Scope query to a specific ward.
     */
    public function scopeWhereWard(Builder $query, int | string $wardNo, ?string $prefix = null): Builder
    {
        return $query->where(($prefix ?? '') . 'ward_no', $wardNo);
    }

    /**
     * Scope query by searching Nepali address terms (English or Nepali Devanagari).
     */
    public function scopeWhereNepaliAddress(Builder $query, string $search, ?string $prefix = null): Builder
    {
        $p = $prefix ?? '';
        $matchedProvinceIds = AddressData::searchProvinceIds($search);
        $matchedDistrictIds = AddressData::searchDistrictIds($search);
        $matchedLocalBodyIds = AddressData::searchLocalBodyIds($search);

        return $query->where(function (Builder $q) use ($p, $matchedProvinceIds, $matchedDistrictIds, $matchedLocalBodyIds): void {
            $hasCondition = false;

            if (! empty($matchedLocalBodyIds)) {
                $q->whereIn($p . 'municipality_id', $matchedLocalBodyIds);
                $hasCondition = true;
            }

            if (! empty($matchedDistrictIds)) {
                $method = $hasCondition ? 'orWhereIn' : 'whereIn';
                $q->{$method}($p . 'district_id', $matchedDistrictIds);
                $hasCondition = true;
            }

            if (! empty($matchedProvinceIds)) {
                $method = $hasCondition ? 'orWhereIn' : 'whereIn';
                $q->{$method}($p . 'province_id', $matchedProvinceIds);
                $hasCondition = true;
            }

            if (! $hasCondition) {
                $q->whereRaw('1 = 0');
            }
        });
    }
}
