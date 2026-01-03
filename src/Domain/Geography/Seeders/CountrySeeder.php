<?php

declare(strict_types=1);

namespace Domain\Geography\Seeders;

use Domain\Geography\Models\Continent;
use Domain\Geography\Models\Country;
use Illuminate\Database\Seeder;
use Symfony\Component\Intl\Countries;

class CountrySeeder extends Seeder
{
    /**
     * Seed the countries table using Unicode CLDR data via symfony/intl.
     *
     * Country names and alpha-3 codes come from the authoritative CLDR/ICU data.
     * Continent mappings are based on UN M.49 geographic regions.
     *
     * Depends on ContinentSeeder running first.
     */
    public function run(): void
    {
        // Cache continent IDs for performance
        $continents = Continent::all()->keyBy('code');

        // Get continent mapping (ISO alpha-2 => continent code)
        $continentMapping = $this->getContinentMapping();

        // Get all countries from CLDR
        $countryNames = Countries::getNames('en');

        foreach ($countryNames as $alpha2 => $name) {
            // Get alpha-3 code from CLDR
            $alpha3 = Countries::getAlpha3Code($alpha2);

            // Get continent for this country
            $continentCode = $continentMapping[$alpha2] ?? null;
            if (! $continentCode) {
                continue; // Skip countries without continent mapping
            }

            $continent = $continents->get($continentCode);
            if (! $continent) {
                continue;
            }

            Country::updateOrCreate(
                ['iso_alpha2' => $alpha2],
                [
                    'iso_alpha3' => $alpha3,
                    'name' => $name,
                    'continent_id' => $continent->id,
                ]
            );
        }
    }

    /**
     * Get mapping of ISO alpha-2 country codes to continent codes.
     *
     * Based on UN M.49 geographic regions. This mapping is stable
     * as countries do not change continents.
     *
     * @return array<string, string>
     */
    private function getContinentMapping(): array
    {
        return [
            // Africa (AF)
            'DZ' => 'AF', 'AO' => 'AF', 'BJ' => 'AF', 'BW' => 'AF', 'BF' => 'AF',
            'BI' => 'AF', 'CV' => 'AF', 'CM' => 'AF', 'CF' => 'AF', 'TD' => 'AF',
            'KM' => 'AF', 'CG' => 'AF', 'CD' => 'AF', 'CI' => 'AF', 'DJ' => 'AF',
            'EG' => 'AF', 'GQ' => 'AF', 'ER' => 'AF', 'SZ' => 'AF', 'ET' => 'AF',
            'GA' => 'AF', 'GM' => 'AF', 'GH' => 'AF', 'GN' => 'AF', 'GW' => 'AF',
            'KE' => 'AF', 'LS' => 'AF', 'LR' => 'AF', 'LY' => 'AF', 'MG' => 'AF',
            'MW' => 'AF', 'ML' => 'AF', 'MR' => 'AF', 'MU' => 'AF', 'YT' => 'AF',
            'MA' => 'AF', 'MZ' => 'AF', 'NA' => 'AF', 'NE' => 'AF', 'NG' => 'AF',
            'RE' => 'AF', 'RW' => 'AF', 'SH' => 'AF', 'ST' => 'AF', 'SN' => 'AF',
            'SC' => 'AF', 'SL' => 'AF', 'SO' => 'AF', 'ZA' => 'AF', 'SS' => 'AF',
            'SD' => 'AF', 'TZ' => 'AF', 'TG' => 'AF', 'TN' => 'AF', 'UG' => 'AF',
            'EH' => 'AF', 'ZM' => 'AF', 'ZW' => 'AF',

            // Antarctica (AN)
            'AQ' => 'AN', 'BV' => 'AN', 'TF' => 'AN', 'HM' => 'AN', 'GS' => 'AN',

            // Asia (AS)
            'AF' => 'AS', 'AM' => 'AS', 'AZ' => 'AS', 'BH' => 'AS', 'BD' => 'AS',
            'BT' => 'AS', 'BN' => 'AS', 'KH' => 'AS', 'CN' => 'AS', 'CY' => 'AS',
            'GE' => 'AS', 'HK' => 'AS', 'IN' => 'AS', 'ID' => 'AS', 'IR' => 'AS',
            'IQ' => 'AS', 'IL' => 'AS', 'JP' => 'AS', 'JO' => 'AS', 'KZ' => 'AS',
            'KW' => 'AS', 'KG' => 'AS', 'LA' => 'AS', 'LB' => 'AS', 'MO' => 'AS',
            'MY' => 'AS', 'MV' => 'AS', 'MN' => 'AS', 'MM' => 'AS', 'NP' => 'AS',
            'KP' => 'AS', 'OM' => 'AS', 'PK' => 'AS', 'PS' => 'AS', 'PH' => 'AS',
            'QA' => 'AS', 'SA' => 'AS', 'SG' => 'AS', 'KR' => 'AS', 'LK' => 'AS',
            'SY' => 'AS', 'TW' => 'AS', 'TJ' => 'AS', 'TH' => 'AS', 'TL' => 'AS',
            'TR' => 'AS', 'TM' => 'AS', 'AE' => 'AS', 'UZ' => 'AS', 'VN' => 'AS',
            'YE' => 'AS',

            // Europe (EU)
            'AL' => 'EU', 'AD' => 'EU', 'AT' => 'EU', 'BY' => 'EU', 'BE' => 'EU',
            'BA' => 'EU', 'BG' => 'EU', 'HR' => 'EU', 'CZ' => 'EU', 'DK' => 'EU',
            'EE' => 'EU', 'FO' => 'EU', 'FI' => 'EU', 'FR' => 'EU', 'DE' => 'EU',
            'GI' => 'EU', 'GR' => 'EU', 'GG' => 'EU', 'HU' => 'EU', 'IS' => 'EU',
            'IE' => 'EU', 'IM' => 'EU', 'IT' => 'EU', 'JE' => 'EU', 'XK' => 'EU',
            'LV' => 'EU', 'LI' => 'EU', 'LT' => 'EU', 'LU' => 'EU', 'MT' => 'EU',
            'MD' => 'EU', 'MC' => 'EU', 'ME' => 'EU', 'NL' => 'EU', 'MK' => 'EU',
            'NO' => 'EU', 'PL' => 'EU', 'PT' => 'EU', 'RO' => 'EU', 'RU' => 'EU',
            'SM' => 'EU', 'RS' => 'EU', 'SK' => 'EU', 'SI' => 'EU', 'ES' => 'EU',
            'SJ' => 'EU', 'SE' => 'EU', 'CH' => 'EU', 'UA' => 'EU', 'GB' => 'EU',
            'VA' => 'EU', 'AX' => 'EU',

            // North America (NA)
            'AI' => 'NA', 'AG' => 'NA', 'AW' => 'NA', 'BS' => 'NA', 'BB' => 'NA',
            'BZ' => 'NA', 'BM' => 'NA', 'BQ' => 'NA', 'VG' => 'NA', 'CA' => 'NA',
            'KY' => 'NA', 'CR' => 'NA', 'CU' => 'NA', 'CW' => 'NA', 'DM' => 'NA',
            'DO' => 'NA', 'SV' => 'NA', 'GL' => 'NA', 'GD' => 'NA', 'GP' => 'NA',
            'GT' => 'NA', 'HT' => 'NA', 'HN' => 'NA', 'JM' => 'NA', 'MQ' => 'NA',
            'MX' => 'NA', 'MS' => 'NA', 'NI' => 'NA', 'PA' => 'NA', 'PR' => 'NA',
            'BL' => 'NA', 'KN' => 'NA', 'LC' => 'NA', 'MF' => 'NA', 'PM' => 'NA',
            'VC' => 'NA', 'SX' => 'NA', 'TT' => 'NA', 'TC' => 'NA', 'US' => 'NA',
            'VI' => 'NA',

            // Oceania (OC)
            'AS' => 'OC', 'AU' => 'OC', 'CX' => 'OC', 'CC' => 'OC', 'CK' => 'OC',
            'FJ' => 'OC', 'PF' => 'OC', 'GU' => 'OC', 'KI' => 'OC', 'MH' => 'OC',
            'FM' => 'OC', 'NR' => 'OC', 'NC' => 'OC', 'NZ' => 'OC', 'NU' => 'OC',
            'NF' => 'OC', 'MP' => 'OC', 'PW' => 'OC', 'PG' => 'OC', 'PN' => 'OC',
            'WS' => 'OC', 'SB' => 'OC', 'TK' => 'OC', 'TO' => 'OC', 'TV' => 'OC',
            'UM' => 'OC', 'VU' => 'OC', 'WF' => 'OC',

            // South America (SA)
            'AR' => 'SA', 'BO' => 'SA', 'BR' => 'SA', 'CL' => 'SA', 'CO' => 'SA',
            'EC' => 'SA', 'FK' => 'SA', 'GF' => 'SA', 'GY' => 'SA', 'PY' => 'SA',
            'PE' => 'SA', 'SR' => 'SA', 'UY' => 'SA', 'VE' => 'SA',
        ];
    }
}
