<?php

namespace Dev1191\FilamentNepaliAddress\Filters;

use Closure;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class NepaliAddressFilter extends BaseFilter
{
    protected string $level = 'combined';

    protected ?string $prefix = null;

    protected ?string $provinceFieldName = null;

    protected ?string $districtFieldName = null;

    protected ?string $localBodyFieldName = null;

    protected ?string $wardFieldName = null;

    protected string | Closure | null $provinceLabel = null;

    protected string | Closure | null $districtLabel = null;

    protected string | Closure | null $localBodyLabel = null;

    protected string | Closure | null $wardLabel = null;

    protected bool $isSearchable = true;

    protected bool $isPreload = true;

    protected bool $hasWard = false;

    /**
     * @var array<int|string, string>|Closure|null
     */
    protected array | Closure | null $wardOptions = null;

    protected int $maxWards = 35;

    public static function getDefaultName(): ?string
    {
        return 'nepali_address';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema(fn (): array => $this->buildFormSchema());

        $this->query(function (Builder $query, array $data): Builder {
            return $this->applyAddressFilter($query, $data);
        });

        $this->indicateUsing(function (array $data): array {
            return $this->buildIndicators($data);
        });
    }

    public function prefix(?string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function provinceFieldName(string $name): static
    {
        $this->provinceFieldName = $name;

        return $this;
    }

    public function getProvinceFieldName(): string
    {
        return $this->provinceFieldName ?? ($this->prefix . 'province_id');
    }

    public function districtFieldName(string $name): static
    {
        $this->districtFieldName = $name;

        return $this;
    }

    public function getDistrictFieldName(): string
    {
        return $this->districtFieldName ?? ($this->prefix . 'district_id');
    }

    public function localBodyFieldName(string $name): static
    {
        $this->localBodyFieldName = $name;

        return $this;
    }

    public function getLocalBodyFieldName(): string
    {
        return $this->localBodyFieldName ?? ($this->prefix . 'municipality_id');
    }

    public function wardFieldName(string $name): static
    {
        $this->wardFieldName = $name;

        return $this;
    }

    public function getWardFieldName(): string
    {
        return $this->wardFieldName ?? ($this->prefix . 'ward_no');
    }

    public function provinceLabel(string | Closure | null $label): static
    {
        $this->provinceLabel = $label;

        return $this;
    }

    public function getProvinceLabel(): string
    {
        if ($this->provinceLabel !== null) {
            return (string) $this->evaluate($this->provinceLabel);
        }

        return __('nepali-address::nepali-address.province');
    }

    public function districtLabel(string | Closure | null $label): static
    {
        $this->districtLabel = $label;

        return $this;
    }

    public function getDistrictLabel(): string
    {
        if ($this->districtLabel !== null) {
            return (string) $this->evaluate($this->districtLabel);
        }

        return __('nepali-address::nepali-address.district');
    }

    public function localBodyLabel(string | Closure | null $label): static
    {
        $this->localBodyLabel = $label;

        return $this;
    }

    public function getLocalBodyLabel(): string
    {
        if ($this->localBodyLabel !== null) {
            return (string) $this->evaluate($this->localBodyLabel);
        }

        return __('nepali-address::nepali-address.local_body');
    }

    public function wardLabel(string | Closure | null $label): static
    {
        $this->wardLabel = $label;

        return $this;
    }

    public function getWardLabel(): string
    {
        if ($this->wardLabel !== null) {
            return (string) $this->evaluate($this->wardLabel);
        }

        return __('nepali-address::nepali-address.ward');
    }

    public function searchable(bool $searchable = true): static
    {
        $this->isSearchable = $searchable;

        return $this;
    }

    public function preload(bool $preload = true): static
    {
        $this->isPreload = $preload;

        return $this;
    }

    public function onlyProvince(): static
    {
        $this->level = 'province';

        return $this;
    }

    public function onlyDistrict(): static
    {
        $this->level = 'district';

        return $this;
    }

    public function onlyLocalBody(): static
    {
        $this->level = 'local_body';

        return $this;
    }

    public function combined(): static
    {
        $this->level = 'combined';

        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param  array<int|string, string>|Closure|null  $options
     */
    public function withWard(
        bool $condition = true,
        ?string $fieldName = null,
        array | Closure | null $options = null,
        int $maxWards = 35
    ): static {
        $this->hasWard = $condition;

        if ($fieldName !== null) {
            $this->wardFieldName = $fieldName;
        }

        $this->wardOptions = $options;
        $this->maxWards = $maxWards;

        return $this;
    }

    public function hasWard(): bool
    {
        return $this->hasWard;
    }

    /**
     * @return array<Component>
     */
    public function buildFormSchema(): array
    {
        $provinceFieldName = $this->getProvinceFieldName();
        $districtFieldName = $this->getDistrictFieldName();
        $localBodyFieldName = $this->getLocalBodyFieldName();

        $provinceSelect = Select::make($provinceFieldName)
            ->label($this->getProvinceLabel())
            ->placeholder(__('nepali-address::nepali-address.select_province'))
            ->options(fn (): array => AddressData::getProvinceOptions())
            ->searchable($this->isSearchable)
            ->preload($this->isPreload)
            ->live()
            ->afterStateUpdated(function (Set $set) use ($districtFieldName, $localBodyFieldName): void {
                $set($districtFieldName, null);
                $set($localBodyFieldName, null);
            });

        $districtSelect = Select::make($districtFieldName)
            ->label($this->getDistrictLabel())
            ->placeholder(__('nepali-address::nepali-address.select_district'))
            ->options(function (Get $get) use ($provinceFieldName): array {
                $provinceId = $get($provinceFieldName);
                if (filled($provinceId)) {
                    return AddressData::getDistrictOptions((int) $provinceId);
                }

                return AddressData::getDistrictOptions();
            })
            ->searchable($this->isSearchable)
            ->preload($this->isPreload)
            ->live()
            ->afterStateUpdated(function (Set $set, mixed $state) use ($provinceFieldName, $localBodyFieldName): void {
                $set($localBodyFieldName, null);
                if (filled($state)) {
                    $district = AddressData::findDistrict((int) $state);
                    if ($district && isset($district['province_id'])) {
                        $set($provinceFieldName, (int) $district['province_id']);
                    }
                }
            });

        $localBodySelect = Select::make($localBodyFieldName)
            ->label($this->getLocalBodyLabel())
            ->placeholder(__('nepali-address::nepali-address.select_local_body'))
            ->options(function (Get $get) use ($districtFieldName): array {
                $districtId = $get($districtFieldName);
                if (filled($districtId)) {
                    return AddressData::getLocalBodyOptions((int) $districtId);
                }

                return AddressData::getLocalBodyOptions();
            })
            ->searchable($this->isSearchable)
            ->preload($this->isPreload)
            ->live()
            ->afterStateUpdated(function (Set $set, mixed $state) use ($districtFieldName, $provinceFieldName): void {
                if (filled($state)) {
                    $localBody = AddressData::findLocalBody((int) $state);
                    if ($localBody && isset($localBody['district_id'])) {
                        $dId = (int) $localBody['district_id'];
                        $set($districtFieldName, $dId);
                        $district = AddressData::findDistrict($dId);
                        if ($district && isset($district['province_id'])) {
                            $set($provinceFieldName, (int) $district['province_id']);
                        }
                    }
                }
            });

        if ($this->level === 'province') {
            return [$provinceSelect];
        }

        if ($this->level === 'district') {
            return [$districtSelect];
        }

        if ($this->level === 'local_body') {
            return [$localBodySelect];
        }

        $fields = [$provinceSelect, $districtSelect, $localBodySelect];

        if ($this->hasWard()) {
            $wardFieldName = $this->getWardFieldName();

            if ($this->wardOptions !== null) {
                /** @var array<int|string, string> $options */
                $options = is_callable($this->wardOptions) ? ($this->wardOptions)() : $this->wardOptions;

                $wardField = Select::make($wardFieldName)
                    ->label($this->getWardLabel())
                    ->options($options)
                    ->searchable($this->isSearchable)
                    ->preload($this->isPreload);
            } else {
                $wardField = TextInput::make($wardFieldName)
                    ->label($this->getWardLabel())
                    ->numeric()
                    ->minValue(1)
                    ->maxValue($this->maxWards)
                    ->placeholder('e.g. 1');
            }

            $fields[] = $wardField;
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyAddressFilter(Builder $query, array $data): Builder
    {
        $provinceId = $data[$this->getProvinceFieldName()] ?? null;
        $districtId = $data[$this->getDistrictFieldName()] ?? null;
        $localBodyId = $data[$this->getLocalBodyFieldName()] ?? null;
        $ward = $data[$this->getWardFieldName()] ?? null;

        if ($this->level === 'province') {
            return $query->when(
                filled($provinceId),
                fn (Builder $q) => $q->where($this->getProvinceFieldName(), (int) $provinceId)
            );
        }

        if ($this->level === 'district') {
            return $query->when(
                filled($districtId),
                fn (Builder $q) => $q->where($this->getDistrictFieldName(), (int) $districtId)
            );
        }

        if ($this->level === 'local_body') {
            return $query->when(
                filled($localBodyId),
                fn (Builder $q) => $q->where($this->getLocalBodyFieldName(), (int) $localBodyId)
            );
        }

        return $query
            ->when(
                filled($provinceId),
                fn (Builder $q) => $q->where($this->getProvinceFieldName(), (int) $provinceId)
            )
            ->when(
                filled($districtId),
                fn (Builder $q) => $q->where($this->getDistrictFieldName(), (int) $districtId)
            )
            ->when(
                filled($localBodyId),
                fn (Builder $q) => $q->where($this->getLocalBodyFieldName(), (int) $localBodyId)
            )
            ->when(
                $this->hasWard() && filled($ward),
                fn (Builder $q) => $q->where($this->getWardFieldName(), $ward)
            );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<Indicator>
     */
    public function buildIndicators(array $data): array
    {
        $indicators = [];

        $provinceId = $data[$this->getProvinceFieldName()] ?? null;
        if (filled($provinceId)) {
            $name = AddressData::getProvinceName((int) $provinceId);
            if (filled($name)) {
                $indicators[] = Indicator::make($this->getProvinceLabel() . ': ' . $name)
                    ->removeField($this->getProvinceFieldName());
            }
        }

        $districtId = $data[$this->getDistrictFieldName()] ?? null;
        if (filled($districtId)) {
            $name = AddressData::getDistrictName((int) $districtId);
            if (filled($name)) {
                $indicators[] = Indicator::make($this->getDistrictLabel() . ': ' . $name)
                    ->removeField($this->getDistrictFieldName());
            }
        }

        $localBodyId = $data[$this->getLocalBodyFieldName()] ?? null;
        if (filled($localBodyId)) {
            $name = AddressData::getLocalBodyName((int) $localBodyId);
            if (filled($name)) {
                $indicators[] = Indicator::make($this->getLocalBodyLabel() . ': ' . $name)
                    ->removeField($this->getLocalBodyFieldName());
            }
        }

        if ($this->hasWard()) {
            $ward = $data[$this->getWardFieldName()] ?? null;
            if (filled($ward)) {
                $indicators[] = Indicator::make($this->getWardLabel() . ' ' . $ward)
                    ->removeField($this->getWardFieldName());
            }
        }

        return $indicators;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResetState(): array
    {
        return [
            $this->getProvinceFieldName() => null,
            $this->getDistrictFieldName() => null,
            $this->getLocalBodyFieldName() => null,
            $this->getWardFieldName() => null,
        ];
    }

    /**
     * Shorthand factory for Province filter.
     */
    public static function province(?string $name = 'province_filter', ?string $fieldName = 'province_id'): static
    {
        return static::make($name ?? 'province_filter')
            ->onlyProvince()
            ->provinceFieldName($fieldName ?? 'province_id');
    }

    /**
     * Shorthand factory for District filter.
     */
    public static function district(?string $name = 'district_filter', ?string $fieldName = 'district_id'): static
    {
        return static::make($name ?? 'district_filter')
            ->onlyDistrict()
            ->districtFieldName($fieldName ?? 'district_id');
    }

    /**
     * Shorthand factory for Local Body filter.
     */
    public static function localBody(?string $name = 'local_body_filter', ?string $fieldName = 'municipality_id'): static
    {
        return static::make($name ?? 'local_body_filter')
            ->onlyLocalBody()
            ->localBodyFieldName($fieldName ?? 'municipality_id');
    }
}
