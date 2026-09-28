<?php

namespace Dev1191\FilamentNepaliAddress\Support;

use Khanaldpk\NepaliAddress\NepaliAddress;

class AddressData
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $provinces = [];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $districts = [];

    /**
     * @var array<string, array<int, array<int, array<string, mixed>>>>
     */
    protected static array $districtsByProvince = [];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $localBodies = [];

    /**
     * @var array<string, array<int, array<int, array<string, mixed>>>>
     */
    protected static array $localBodiesByDistrict = [];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $localBodyTypes = [];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $provincesById = [];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $districtsById = [];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $localBodiesById = [];

    public static function currentLanguage(): string
    {
        return (string) config('nepali-address.lang', 'en');
    }

    public static function getDriver(): NepaliAddress
    {
        return app(NepaliAddress::class);
    }

    /**
     * Retrieve all provinces.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getProvinces(): array
    {
        $lang = static::currentLanguage();

        if (! isset(static::$provinces[$lang])) {
            /** @var array<int, array<string, mixed>> $data */
            $data = static::getDriver()->getProvinces();
            static::$provinces[$lang] = $data;

            $indexed = [];
            foreach ($data as $province) {
                if (isset($province['province_id'])) {
                    $indexed[(int) $province['province_id']] = $province;
                }
            }
            static::$provincesById[$lang] = $indexed;
        }

        return static::$provinces[$lang];
    }

    /**
     * Retrieve all districts, optionally filtered by province ID.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getDistricts(?int $provinceId = null): array
    {
        $lang = static::currentLanguage();

        if (! isset(static::$districts[$lang])) {
            /** @var array<int, array<string, mixed>> $allDistricts */
            $allDistricts = static::getDriver()->getDistricts();
            static::$districts[$lang] = $allDistricts;

            $byProvince = [];
            $indexed = [];
            foreach ($allDistricts as $district) {
                $pId = (int) ($district['province_id'] ?? 0);
                $dId = (int) ($district['district_id'] ?? 0);

                $byProvince[$pId][] = $district;
                $indexed[$dId] = $district;
            }

            static::$districtsByProvince[$lang] = $byProvince;
            static::$districtsById[$lang] = $indexed;
        }

        if ($provinceId !== null) {
            return static::$districtsByProvince[$lang][$provinceId] ?? [];
        }

        return static::$districts[$lang];
    }

    /**
     * Retrieve all local bodies, optionally filtered by district ID.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getLocalBodies(?int $districtId = null): array
    {
        $lang = static::currentLanguage();

        if (! isset(static::$localBodies[$lang])) {
            /** @var array<int, array<string, mixed>> $allBodies */
            $allBodies = static::getDriver()->getLocalBodies();
            static::$localBodies[$lang] = $allBodies;

            $byDistrict = [];
            $indexed = [];
            foreach ($allBodies as $body) {
                $dId = (int) ($body['district_id'] ?? 0);
                $mId = (int) ($body['municipality_id'] ?? 0);

                $byDistrict[$dId][] = $body;
                $indexed[$mId] = $body;
            }

            static::$localBodiesByDistrict[$lang] = $byDistrict;
            static::$localBodiesById[$lang] = $indexed;
        }

        if ($districtId !== null) {
            return static::$localBodiesByDistrict[$lang][$districtId] ?? [];
        }

        return static::$localBodies[$lang];
    }

    /**
     * Retrieve local body types (Metropolitan, Sub-metropolitan, Municipality, Rural Municipality).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getLocalBodyTypes(): array
    {
        $lang = static::currentLanguage();

        if (! isset(static::$localBodyTypes[$lang])) {
            /** @var array<int, array<string, mixed>> $data */
            $data = static::getDriver()->getLocalBodyTypes();
            static::$localBodyTypes[$lang] = $data;
        }

        return static::$localBodyTypes[$lang];
    }

    /**
     * Retrieve city-wise postal codes hierarchy.
     *
     * @return array<mixed>
     */
    public static function getPostalCodes(): array
    {
        /** @var array<mixed> */
        return static::getDriver()->getPostalCodes();
    }

    /**
     * Get options array for Province select: [province_id => name].
     *
     * @return array<int, string>
     */
    public static function getProvinceOptions(): array
    {
        $options = [];
        foreach (static::getProvinces() as $province) {
            $options[(int) $province['province_id']] = (string) $province['name'];
        }

        return $options;
    }

    /**
     * Get options array for District select: [district_id => name].
     *
     * @return array<int, string>
     */
    public static function getDistrictOptions(?int $provinceId = null): array
    {
        $options = [];
        foreach (static::getDistricts($provinceId) as $district) {
            $options[(int) $district['district_id']] = (string) $district['name'];
        }

        return $options;
    }

    /**
     * Get options array for Local Body select: [municipality_id => name].
     *
     * @return array<int, string>
     */
    public static function getLocalBodyOptions(?int $districtId = null): array
    {
        $options = [];
        foreach (static::getLocalBodies($districtId) as $localBody) {
            $options[(int) $localBody['municipality_id']] = (string) $localBody['name'];
        }

        return $options;
    }

    /**
     * Find single province record by province_id.
     *
     * @return array<string, mixed>|null
     */
    public static function findProvince(?int $provinceId): ?array
    {
        if ($provinceId === null) {
            return null;
        }

        static::getProvinces();
        $lang = static::currentLanguage();

        return static::$provincesById[$lang][$provinceId] ?? null;
    }

    /**
     * Find single district record by district_id.
     *
     * @return array<string, mixed>|null
     */
    public static function findDistrict(?int $districtId): ?array
    {
        if ($districtId === null) {
            return null;
        }

        static::getDistricts();
        $lang = static::currentLanguage();

        return static::$districtsById[$lang][$districtId] ?? null;
    }

    /**
     * Find single local body record by municipality_id.
     *
     * @return array<string, mixed>|null
     */
    public static function findLocalBody(?int $municipalityId): ?array
    {
        if ($municipalityId === null) {
            return null;
        }

        static::getLocalBodies();
        $lang = static::currentLanguage();

        return static::$localBodiesById[$lang][$municipalityId] ?? null;
    }

    /**
     * Get display name of a province.
     */
    public static function getProvinceName(?int $provinceId): ?string
    {
        $province = static::findProvince($provinceId);

        return $province ? (string) $province['name'] : null;
    }

    /**
     * Get display name of a district.
     */
    public static function getDistrictName(?int $districtId): ?string
    {
        $district = static::findDistrict($districtId);

        return $district ? (string) $district['name'] : null;
    }

    /**
     * Get display name of a local body.
     */
    public static function getLocalBodyName(?int $municipalityId): ?string
    {
        $localBody = static::findLocalBody($municipalityId);

        return $localBody ? (string) $localBody['name'] : null;
    }

    /**
     * Format a combined address string from IDs.
     */
    public static function formatAddress(
        ?int $provinceId = null,
        ?int $districtId = null,
        ?int $municipalityId = null,
        int | string | null $wardNo = null,
        string $separator = ', '
    ): string {
        $localBodyName = static::getLocalBodyName($municipalityId);

        if (filled($wardNo)) {
            $localBodyName = filled($localBodyName)
                ? "{$localBodyName}-{$wardNo}"
                : (__('nepali-address::nepali-address.ward') . ' ' . $wardNo);
        }

        $parts = array_filter([
            $localBodyName,
            static::getDistrictName($districtId),
            static::getProvinceName($provinceId),
        ]);

        return implode($separator, $parts);
    }

    /**
     * Check if a district belongs to a specific province.
     */
    public static function districtBelongsToProvince(?int $districtId, ?int $provinceId): bool
    {
        if ($districtId === null || $provinceId === null) {
            return false;
        }

        $district = static::findDistrict($districtId);

        return $district !== null && (int) ($district['province_id'] ?? 0) === $provinceId;
    }

    /**
     * Check if a local body belongs to a specific district.
     */
    public static function localBodyBelongsToDistrict(?int $municipalityId, ?int $districtId): bool
    {
        if ($municipalityId === null || $districtId === null) {
            return false;
        }

        $localBody = static::findLocalBody($municipalityId);

        return $localBody !== null && (int) ($localBody['district_id'] ?? 0) === $districtId;
    }

    /**
     * Verify if province ID is valid.
     */
    public static function isValidProvince(?int $provinceId): bool
    {
        return static::findProvince($provinceId) !== null;
    }

    /**
     * Verify if district ID is valid, optionally checking province matching.
     */
    public static function isValidDistrict(?int $districtId, ?int $provinceId = null): bool
    {
        if ($provinceId !== null) {
            return static::districtBelongsToProvince($districtId, $provinceId);
        }

        return static::findDistrict($districtId) !== null;
    }

    /**
     * Verify if local body (municipality) ID is valid, optionally checking district matching.
     */
    public static function isValidLocalBody(?int $municipalityId, ?int $districtId = null): bool
    {
        if ($districtId !== null) {
            return static::localBodyBelongsToDistrict($municipalityId, $districtId);
        }

        return static::findLocalBody($municipalityId) !== null;
    }

    /**
     * Search province IDs matching a query string (in English or Nepali).
     *
     * @return array<int>
     */
    public static function searchProvinceIds(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return [];
        }

        $matches = [];
        foreach (static::getProvinces() as $province) {
            $name = mb_strtolower((string) ($province['name'] ?? ''));
            $nepaliName = mb_strtolower((string) ($province['nepali_name'] ?? ''));

            if (str_contains($name, $query) || str_contains($nepaliName, $query)) {
                $matches[] = (int) $province['province_id'];
            }
        }

        return $matches;
    }

    /**
     * Search district IDs matching a query string (in English or Nepali).
     *
     * @return array<int>
     */
    public static function searchDistrictIds(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return [];
        }

        $matches = [];
        foreach (static::getDistricts() as $district) {
            $name = mb_strtolower((string) ($district['name'] ?? ''));
            $nepaliName = mb_strtolower((string) ($district['nepali_name'] ?? ''));

            if (str_contains($name, $query) || str_contains($nepaliName, $query)) {
                $matches[] = (int) $district['district_id'];
            }
        }

        return $matches;
    }

    /**
     * Search local body (municipality) IDs matching a query string (in English or Nepali).
     *
     * @return array<int>
     */
    public static function searchLocalBodyIds(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return [];
        }

        $matches = [];
        foreach (static::getLocalBodies() as $localBody) {
            $name = mb_strtolower((string) ($localBody['name'] ?? ''));
            $nepaliName = mb_strtolower((string) ($localBody['nepali_name'] ?? ''));

            if (str_contains($name, $query) || str_contains($nepaliName, $query)) {
                $matches[] = (int) $localBody['municipality_id'];
            }
        }

        return $matches;
    }

    /**
     * Flush all in-memory static caches.
     */
    public static function flushCache(): void
    {
        static::$provinces = [];
        static::$districts = [];
        static::$districtsByProvince = [];
        static::$localBodies = [];
        static::$localBodiesByDistrict = [];
        static::$localBodyTypes = [];
        static::$provincesById = [];
        static::$districtsById = [];
        static::$localBodiesById = [];
    }
}
