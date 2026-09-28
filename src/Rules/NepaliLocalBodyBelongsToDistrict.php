<?php

namespace Dev1191\FilamentNepaliAddress\Rules;

use Closure;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class NepaliLocalBodyBelongsToDistrict implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  (int|Closure|null)  $districtId
     */
    public function __construct(
        protected int | Closure | null $districtId = null,
        protected ?string $districtAttribute = null
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

        $municipalityId = (int) $value;

        if (! AddressData::isValidLocalBody($municipalityId)) {
            $fail(__('nepali-address::nepali-address.validation.invalid_local_body'));

            return;
        }

        $districtId = $this->resolveDistrictId();

        if ($districtId !== null && ! AddressData::localBodyBelongsToDistrict($municipalityId, $districtId)) {
            $fail(__('nepali-address::nepali-address.validation.local_body_belongs_to_district'));
        }
    }

    protected function resolveDistrictId(): ?int
    {
        if ($this->districtId instanceof Closure) {
            $resolved = ($this->districtId)();

            return filled($resolved) ? (int) $resolved : null;
        }

        if ($this->districtId !== null) {
            return (int) $this->districtId;
        }

        if ($this->districtAttribute && isset($this->data[$this->districtAttribute])) {
            $val = $this->data[$this->districtAttribute];

            return filled($val) ? (int) $val : null;
        }

        return null;
    }
}
