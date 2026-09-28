<?php

namespace Dev1191\FilamentNepaliAddress\Forms\Components;

use Closure;
use Dev1191\FilamentNepaliAddress\Rules\NepaliDistrictBelongsToProvince;
use Dev1191\FilamentNepaliAddress\Rules\NepaliLocalBodyBelongsToDistrict;
use Dev1191\FilamentNepaliAddress\Support\AddressData;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Concerns\EntanglesStateWithSingularRelationship;
use Filament\Schemas\Components\Contracts\CanEntangleWithSingularRelationships;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;

class NepaliAddressSelects extends Component implements CanEntangleWithSingularRelationships, HasEmbeddedView
{
    use EntanglesStateWithSingularRelationship;

    protected ?string $publishedViewOverrideCheckPath = 'filament-schemas::components.grid';

    protected ?string $prefix = null;

    protected ?string $provinceFieldName = null;

    protected ?string $districtFieldName = null;

    protected ?string $localBodyFieldName = null;

    protected ?string $wardFieldName = null;

    protected ?string $postalCodeFieldName = null;

    protected string | Closure | null $provinceLabel = null;

    protected string | Closure | null $districtLabel = null;

    protected string | Closure | null $localBodyLabel = null;

    protected string | Closure | null $wardLabel = null;

    protected string | Closure | null $postalCodeLabel = null;

    protected bool | Closure $isSearchable = true;

    protected bool | Closure $isPreload = true;

    protected bool | Closure $isRequired = false;

    protected bool | Closure | null $isProvinceRequired = null;

    protected bool | Closure | null $isDistrictRequired = null;

    protected bool | Closure | null $isLocalBodyRequired = null;

    protected bool | Closure | null $isWardRequired = null;

    protected bool | Closure | null $isPostalCodeRequired = null;

    protected bool | Closure $hasRelationshipValidation = true;

    protected bool | Closure $hasWard = false;

    /**
     * @var array<int|string, string>|Closure|null
     */
    protected array | Closure | null $wardOptions = null;

    protected int $maxWards = 35;

    protected bool | Closure $hasPostalCode = false;

    protected ?Closure $modifyProvinceSelectUsing = null;

    protected ?Closure $modifyDistrictSelectUsing = null;

    protected ?Closure $modifyLocalBodySelectUsing = null;

    protected ?Closure $modifyWardInputUsing = null;

    protected ?Closure $modifyPostalCodeInputUsing = null;

    /**
     * @param  array<string, ?int> | int | null  $columns
     */
    public static function make(?string $prefix = null, array | int | null $columns = 3): static
    {
        /** @var static $static */
        $static = app(static::class);

        if ($prefix !== null) {
            $static->prefix($prefix);
        }

        if ($columns !== null) {
            $static->columns($columns);
        }

        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->getColumns('lg') === null) {
            $this->columns(3);
        }

        $this->schema(fn (): array => $this->buildComponents());
    }

    /**
     * @return array<Component|Action|ActionGroup>
     */
    public function getChildComponents(?string $key = null): array
    {
        if (! isset($this->container)) {
            return $this->buildComponents();
        }

        return parent::getChildComponents($key);
    }

    /**
     * @return array<Component>
     */
    public function getComponents(): array
    {
        return $this->buildComponents();
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

    public function postalCodeFieldName(string $name): static
    {
        $this->postalCodeFieldName = $name;

        return $this;
    }

    public function getPostalCodeFieldName(): string
    {
        return $this->postalCodeFieldName ?? ($this->prefix . 'postal_code');
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

    public function postalCodeLabel(string | Closure | null $label): static
    {
        $this->postalCodeLabel = $label;

        return $this;
    }

    public function getPostalCodeLabel(): string
    {
        if ($this->postalCodeLabel !== null) {
            return (string) $this->evaluate($this->postalCodeLabel);
        }

        return __('nepali-address::nepali-address.postal_code');
    }

    public function searchable(bool | Closure $condition = true): static
    {
        $this->isSearchable = $condition;

        return $this;
    }

    public function isSearchable(): bool
    {
        return (bool) $this->evaluate($this->isSearchable);
    }

    public function preload(bool | Closure $condition = true): static
    {
        $this->isPreload = $condition;

        return $this;
    }

    public function isPreload(): bool
    {
        return (bool) $this->evaluate($this->isPreload);
    }

    public function required(bool | Closure $condition = true): static
    {
        $this->isRequired = $condition;

        return $this;
    }

    public function isRequired(): bool
    {
        return (bool) $this->evaluate($this->isRequired);
    }

    public function provinceRequired(bool | Closure | null $condition = true): static
    {
        $this->isProvinceRequired = $condition;

        return $this;
    }

    public function isProvinceRequired(): bool
    {
        if ($this->isProvinceRequired !== null) {
            return (bool) $this->evaluate($this->isProvinceRequired);
        }

        return $this->isRequired();
    }

    public function districtRequired(bool | Closure | null $condition = true): static
    {
        $this->isDistrictRequired = $condition;

        return $this;
    }

    public function isDistrictRequired(): bool
    {
        if ($this->isDistrictRequired !== null) {
            return (bool) $this->evaluate($this->isDistrictRequired);
        }

        return $this->isRequired();
    }

    public function localBodyRequired(bool | Closure | null $condition = true): static
    {
        $this->isLocalBodyRequired = $condition;

        return $this;
    }

    public function isLocalBodyRequired(): bool
    {
        if ($this->isLocalBodyRequired !== null) {
            return (bool) $this->evaluate($this->isLocalBodyRequired);
        }

        return $this->isRequired();
    }

    public function wardRequired(bool | Closure | null $condition = true): static
    {
        $this->isWardRequired = $condition;

        return $this;
    }

    public function isWardRequired(): bool
    {
        if ($this->isWardRequired !== null) {
            return (bool) $this->evaluate($this->isWardRequired);
        }

        return $this->isRequired();
    }

    public function postalCodeRequired(bool | Closure | null $condition = true): static
    {
        $this->isPostalCodeRequired = $condition;

        return $this;
    }

    public function isPostalCodeRequired(): bool
    {
        if ($this->isPostalCodeRequired !== null) {
            return (bool) $this->evaluate($this->isPostalCodeRequired);
        }

        return $this->isRequired();
    }

    public function relationshipValidation(bool | Closure $condition = true): static
    {
        $this->hasRelationshipValidation = $condition;

        return $this;
    }

    public function hasRelationshipValidation(): bool
    {
        return (bool) $this->evaluate($this->hasRelationshipValidation);
    }

    /**
     * Enable ward field (numeric input or custom select).
     *
     * @param  array<int|string, string>|Closure|null  $options
     */
    public function withWard(
        bool | Closure $condition = true,
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
        return (bool) $this->evaluate($this->hasWard);
    }

    /**
     * Enable postal code field with bundled Nepal postal code validation.
     */
    public function withPostalCode(
        bool | Closure $condition = true,
        ?string $fieldName = null
    ): static {
        $this->hasPostalCode = $condition;

        if ($fieldName !== null) {
            $this->postalCodeFieldName = $fieldName;
        }

        return $this;
    }

    public function hasPostalCode(): bool
    {
        return (bool) $this->evaluate($this->hasPostalCode);
    }

    public function modifyProvinceSelectUsing(?Closure $callback): static
    {
        $this->modifyProvinceSelectUsing = $callback;

        return $this;
    }

    public function modifyDistrictSelectUsing(?Closure $callback): static
    {
        $this->modifyDistrictSelectUsing = $callback;

        return $this;
    }

    public function modifyLocalBodySelectUsing(?Closure $callback): static
    {
        $this->modifyLocalBodySelectUsing = $callback;

        return $this;
    }

    public function modifyWardInputUsing(?Closure $callback): static
    {
        $this->modifyWardInputUsing = $callback;

        return $this;
    }

    public function modifyPostalCodeInputUsing(?Closure $callback): static
    {
        $this->modifyPostalCodeInputUsing = $callback;

        return $this;
    }

    /**
     * Factory for standalone Province select.
     */
    public static function makeProvinceSelect(?string $name = 'province_id'): Select
    {
        return Select::make($name ?? 'province_id')
            ->label(__('nepali-address::nepali-address.province'))
            ->placeholder(__('nepali-address::nepali-address.select_province'))
            ->options(fn (): array => AddressData::getProvinceOptions())
            ->searchable()
            ->preload();
    }

    /**
     * Factory for standalone District select with cascading support.
     */
    public static function makeDistrictSelect(
        ?string $name = 'district_id',
        string $provinceFieldName = 'province_id'
    ): Select {
        return Select::make($name ?? 'district_id')
            ->label(__('nepali-address::nepali-address.district'))
            ->placeholder(__('nepali-address::nepali-address.select_district'))
            ->options(function (Get $get) use ($provinceFieldName): array {
                $provinceId = $get($provinceFieldName);
                if (blank($provinceId)) {
                    return [];
                }

                return AddressData::getDistrictOptions((int) $provinceId);
            })
            ->disabled(fn (Get $get): bool => blank($get($provinceFieldName)))
            ->searchable()
            ->preload()
            ->rule(function (Get $get) use ($provinceFieldName): NepaliDistrictBelongsToProvince {
                return new NepaliDistrictBelongsToProvince(
                    provinceId: fn (): mixed => $get($provinceFieldName)
                );
            });
    }

    /**
     * Factory for standalone Local Body select with cascading support.
     */
    public static function makeLocalBodySelect(
        ?string $name = 'municipality_id',
        string $districtFieldName = 'district_id'
    ): Select {
        return Select::make($name ?? 'municipality_id')
            ->label(__('nepali-address::nepali-address.local_body'))
            ->placeholder(__('nepali-address::nepali-address.select_local_body'))
            ->options(function (Get $get) use ($districtFieldName): array {
                $districtId = $get($districtFieldName);
                if (blank($districtId)) {
                    return [];
                }

                return AddressData::getLocalBodyOptions((int) $districtId);
            })
            ->disabled(fn (Get $get): bool => blank($get($districtFieldName)))
            ->searchable()
            ->preload()
            ->rule(function (Get $get) use ($districtFieldName): NepaliLocalBodyBelongsToDistrict {
                return new NepaliLocalBodyBelongsToDistrict(
                    districtId: fn (): mixed => $get($districtFieldName)
                );
            });
    }

    /**
     * Factory for standalone Postal Code input.
     */
    public static function makePostalCodeInput(?string $name = 'postal_code'): NepaliPostalCodeInput
    {
        return NepaliPostalCodeInput::make($name ?? 'postal_code');
    }

    /**
     * @return array<Component>
     */
    protected function buildComponents(): array
    {
        $provinceName = $this->getProvinceFieldName();
        $districtName = $this->getDistrictFieldName();
        $localBodyName = $this->getLocalBodyFieldName();

        $province = Select::make($provinceName)
            ->label($this->getProvinceLabel())
            ->placeholder(__('nepali-address::nepali-address.select_province'))
            ->options(fn (): array => AddressData::getProvinceOptions())
            ->searchable($this->isSearchable())
            ->preload($this->isPreload())
            ->live()
            ->afterStateUpdated(function (Set $set) use ($districtName, $localBodyName): void {
                $set($districtName, null);
                $set($localBodyName, null);
            });

        if ($this->isProvinceRequired()) {
            $province->required();
        }

        if ($this->modifyProvinceSelectUsing) {
            ($this->modifyProvinceSelectUsing)($province);
        }

        $district = Select::make($districtName)
            ->label($this->getDistrictLabel())
            ->placeholder(__('nepali-address::nepali-address.select_district'))
            ->options(function (Get $get) use ($provinceName): array {
                $provinceId = $get($provinceName);
                if (blank($provinceId)) {
                    return [];
                }

                return AddressData::getDistrictOptions((int) $provinceId);
            })
            ->disabled(fn (Get $get): bool => blank($get($provinceName)))
            ->searchable($this->isSearchable())
            ->preload($this->isPreload())
            ->live()
            ->afterStateUpdated(function (Set $set) use ($localBodyName): void {
                $set($localBodyName, null);
            });

        if ($this->isDistrictRequired()) {
            $district->required();
        }

        if ($this->hasRelationshipValidation()) {
            $district->rule(function (Get $get) use ($provinceName): NepaliDistrictBelongsToProvince {
                return new NepaliDistrictBelongsToProvince(
                    provinceId: fn (): mixed => $get($provinceName)
                );
            });
        }

        if ($this->modifyDistrictSelectUsing) {
            ($this->modifyDistrictSelectUsing)($district);
        }

        $localBody = Select::make($localBodyName)
            ->label($this->getLocalBodyLabel())
            ->placeholder(__('nepali-address::nepali-address.select_local_body'))
            ->options(function (Get $get) use ($districtName): array {
                $districtId = $get($districtName);
                if (blank($districtId)) {
                    return [];
                }

                return AddressData::getLocalBodyOptions((int) $districtId);
            })
            ->disabled(fn (Get $get): bool => blank($get($districtName)))
            ->searchable($this->isSearchable())
            ->preload($this->isPreload());

        if ($this->isLocalBodyRequired()) {
            $localBody->required();
        }

        if ($this->hasRelationshipValidation()) {
            $localBody->rule(function (Get $get) use ($districtName): NepaliLocalBodyBelongsToDistrict {
                return new NepaliLocalBodyBelongsToDistrict(
                    districtId: fn (): mixed => $get($districtName)
                );
            });
        }

        if ($this->modifyLocalBodySelectUsing) {
            ($this->modifyLocalBodySelectUsing)($localBody);
        }

        $components = [$province, $district, $localBody];

        if ($this->hasWard()) {
            $wardName = $this->getWardFieldName();

            if ($this->wardOptions !== null) {
                /** @var array<int|string, string> $options */
                $options = is_callable($this->wardOptions) ? ($this->wardOptions)() : $this->wardOptions;

                $ward = Select::make($wardName)
                    ->label($this->getWardLabel())
                    ->options($options)
                    ->searchable($this->isSearchable())
                    ->preload($this->isPreload());
            } else {
                $ward = TextInput::make($wardName)
                    ->label($this->getWardLabel())
                    ->numeric()
                    ->minValue(1)
                    ->maxValue($this->maxWards)
                    ->placeholder('e.g. 1');
            }

            if ($this->isWardRequired()) {
                $ward->required();
            }

            if ($this->modifyWardInputUsing) {
                ($this->modifyWardInputUsing)($ward);
            }

            $components[] = $ward;
        }

        if ($this->hasPostalCode()) {
            $postalCode = NepaliPostalCodeInput::make($this->getPostalCodeFieldName())
                ->label($this->getPostalCodeLabel());

            if ($this->isPostalCodeRequired()) {
                $postalCode->required();
            }

            if ($this->modifyPostalCodeInputUsing) {
                ($this->modifyPostalCodeInputUsing)($postalCode);
            }

            $components[] = $postalCode;
        }

        return $components;
    }

    public function toEmbeddedHtml(): string
    {
        $attributes = (new FilamentComponentAttributeBag)
            ->merge(['id' => $this->getId()], escape: false)
            ->merge($this->getExtraAttributes(), escape: false);

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?= $this->getChildSchema()->toHtml() ?>
        </div>

        <?php return ob_get_clean();
    }
}
