<?php

namespace Dev1191\FilamentNepaliAddress\Rules;

use Closure;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class NepaliDistrictBelongsToProvince implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  (int|Closure|null)  $provinceId
     */
    public function __construct(
        protected int | Closure | null $provinceId = null,
        protected ?string $provinceAttribute = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $districtId = (int) $value;

        if (! AddressData::isValidDistrict($districtId)) {
            $fail(__('nepali-address::nepali-address.validation.invalid_district'));

            return;
        }

        $provinceId = $this->resolveProvinceId();

        if ($provinceId !== null && ! AddressData::districtBelongsToProvince($districtId, $provinceId)) {
            $fail(__('nepali-address::nepali-address.validation.district_belongs_to_province'));
        }
    }

    protected function resolveProvinceId(): ?int
    {
        if ($this->provinceId instanceof Closure) {
            $resolved = ($this->provinceId)();

            return filled($resolved) ? (int) $resolved : null;
        }

        if ($this->provinceId !== null) {
            return (int) $this->provinceId;
        }

        if ($this->provinceAttribute && isset($this->data[$this->provinceAttribute])) {
            $val = $this->data[$this->provinceAttribute];

            return filled($val) ? (int) $val : null;
        }

        return null;
    }
}
