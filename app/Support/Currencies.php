<?php

namespace App\Support;

class Currencies
{
    /** @var array<string, array{name: string, symbol: string, position: string}> */
    private const LIST = [
        'AED' => ['name' => 'UAE Dirham',                          'symbol' => 'د.إ',   'position' => 'left'],
        'AFN' => ['name' => 'Afghan Afghani',                      'symbol' => '؋',     'position' => 'left'],
        'ALL' => ['name' => 'Albanian Lek',                        'symbol' => 'L',     'position' => 'right'],
        'AMD' => ['name' => 'Armenian Dram',                       'symbol' => '֏',     'position' => 'right'],
        'ANG' => ['name' => 'Netherlands Antillean Guilder',       'symbol' => 'ƒ',     'position' => 'left'],
        'AOA' => ['name' => 'Angolan Kwanza',                      'symbol' => 'Kz',    'position' => 'left'],
        'ARS' => ['name' => 'Argentine Peso',                      'symbol' => '$',     'position' => 'left'],
        'AUD' => ['name' => 'Australian Dollar',                   'symbol' => '$',     'position' => 'left'],
        'AWG' => ['name' => 'Aruban Florin',                       'symbol' => 'ƒ',     'position' => 'left'],
        'AZN' => ['name' => 'Azerbaijani Manat',                   'symbol' => '₼',     'position' => 'left'],
        'BAM' => ['name' => 'Bosnia-Herzegovina Convertible Mark', 'symbol' => 'KM',    'position' => 'right'],
        'BBD' => ['name' => 'Barbadian Dollar',                    'symbol' => '$',     'position' => 'left'],
        'BDT' => ['name' => 'Bangladeshi Taka',                    'symbol' => '৳',     'position' => 'left'],
        'BGN' => ['name' => 'Bulgarian Lev',                       'symbol' => 'лв',    'position' => 'right'],
        'BHD' => ['name' => 'Bahraini Dinar',                      'symbol' => '.د.ب',  'position' => 'left'],
        'BIF' => ['name' => 'Burundian Franc',                     'symbol' => 'FBu',   'position' => 'right'],
        'BMD' => ['name' => 'Bermudan Dollar',                     'symbol' => '$',     'position' => 'left'],
        'BND' => ['name' => 'Brunei Dollar',                       'symbol' => '$',     'position' => 'left'],
        'BOB' => ['name' => 'Bolivian Boliviano',                  'symbol' => 'Bs.',   'position' => 'left'],
        'BRL' => ['name' => 'Brazilian Real',                      'symbol' => 'R$',    'position' => 'left'],
        'BSD' => ['name' => 'Bahamian Dollar',                     'symbol' => '$',     'position' => 'left'],
        'BTN' => ['name' => 'Bhutanese Ngultrum',                  'symbol' => 'Nu.',   'position' => 'left'],
        'BWP' => ['name' => 'Botswanan Pula',                      'symbol' => 'P',     'position' => 'left'],
        'BYN' => ['name' => 'Belarusian Ruble',                    'symbol' => 'Br',    'position' => 'right'],
        'BZD' => ['name' => 'Belize Dollar',                       'symbol' => 'BZ$',   'position' => 'left'],
        'CAD' => ['name' => 'Canadian Dollar',                     'symbol' => '$',     'position' => 'left'],
        'CDF' => ['name' => 'Congolese Franc',                     'symbol' => 'FC',    'position' => 'right'],
        'CHF' => ['name' => 'Swiss Franc',                         'symbol' => 'CHF',   'position' => 'left'],
        'CLP' => ['name' => 'Chilean Peso',                        'symbol' => '$',     'position' => 'left'],
        'CNY' => ['name' => 'Chinese Yuan',                        'symbol' => '¥',     'position' => 'left'],
        'COP' => ['name' => 'Colombian Peso',                      'symbol' => '$',     'position' => 'left'],
        'CRC' => ['name' => 'Costa Rican Colón',                   'symbol' => '₡',     'position' => 'left'],
        'CUP' => ['name' => 'Cuban Peso',                          'symbol' => '$',     'position' => 'left'],
        'CVE' => ['name' => 'Cape Verdean Escudo',                 'symbol' => '$',     'position' => 'right'],
        'CZK' => ['name' => 'Czech Koruna',                        'symbol' => 'Kč',    'position' => 'right'],
        'DJF' => ['name' => 'Djiboutian Franc',                    'symbol' => 'Fdj',   'position' => 'right'],
        'DKK' => ['name' => 'Danish Krone',                        'symbol' => 'kr',    'position' => 'right'],
        'DOP' => ['name' => 'Dominican Peso',                      'symbol' => 'RD$',   'position' => 'left'],
        'DZD' => ['name' => 'Algerian Dinar',                      'symbol' => 'د.ج',   'position' => 'left'],
        'EGP' => ['name' => 'Egyptian Pound',                      'symbol' => '£',     'position' => 'left'],
        'ERN' => ['name' => 'Eritrean Nakfa',                      'symbol' => 'Nfk',   'position' => 'left'],
        'ETB' => ['name' => 'Ethiopian Birr',                      'symbol' => 'Br',    'position' => 'left'],
        'EUR' => ['name' => 'Euro',                                 'symbol' => '€',     'position' => 'left'],
        'FJD' => ['name' => 'Fijian Dollar',                       'symbol' => '$',     'position' => 'left'],
        'FKP' => ['name' => 'Falkland Islands Pound',              'symbol' => '£',     'position' => 'left'],
        'GBP' => ['name' => 'British Pound',                       'symbol' => '£',     'position' => 'left'],
        'GEL' => ['name' => 'Georgian Lari',                       'symbol' => '₾',     'position' => 'left'],
        'GHS' => ['name' => 'Ghanaian Cedi',                       'symbol' => '₵',     'position' => 'left'],
        'GIP' => ['name' => 'Gibraltar Pound',                     'symbol' => '£',     'position' => 'left'],
        'GMD' => ['name' => 'Gambian Dalasi',                      'symbol' => 'D',     'position' => 'left'],
        'GNF' => ['name' => 'Guinean Franc',                       'symbol' => 'FG',    'position' => 'right'],
        'GTQ' => ['name' => 'Guatemalan Quetzal',                  'symbol' => 'Q',     'position' => 'left'],
        'GYD' => ['name' => 'Guyanaese Dollar',                    'symbol' => '$',     'position' => 'left'],
        'HKD' => ['name' => 'Hong Kong Dollar',                    'symbol' => 'HK$',   'position' => 'left'],
        'HNL' => ['name' => 'Honduran Lempira',                    'symbol' => 'L',     'position' => 'left'],
        'HRK' => ['name' => 'Croatian Kuna',                       'symbol' => 'kn',    'position' => 'right'],
        'HTG' => ['name' => 'Haitian Gourde',                      'symbol' => 'G',     'position' => 'left'],
        'HUF' => ['name' => 'Hungarian Forint',                    'symbol' => 'Ft',    'position' => 'right'],
        'IDR' => ['name' => 'Indonesian Rupiah',                   'symbol' => 'Rp',    'position' => 'left'],
        'ILS' => ['name' => 'Israeli New Shekel',                  'symbol' => '₪',     'position' => 'left'],
        'INR' => ['name' => 'Indian Rupee',                        'symbol' => '₹',     'position' => 'left'],
        'IQD' => ['name' => 'Iraqi Dinar',                         'symbol' => 'ع.د',   'position' => 'left'],
        'IRR' => ['name' => 'Iranian Rial',                        'symbol' => '﷼',     'position' => 'left'],
        'ISK' => ['name' => 'Icelandic Króna',                     'symbol' => 'kr',    'position' => 'right'],
        'JMD' => ['name' => 'Jamaican Dollar',                     'symbol' => 'J$',    'position' => 'left'],
        'JOD' => ['name' => 'Jordanian Dinar',                     'symbol' => 'د.ا',   'position' => 'left'],
        'JPY' => ['name' => 'Japanese Yen',                        'symbol' => '¥',     'position' => 'left'],
        'KES' => ['name' => 'Kenyan Shilling',                     'symbol' => 'KSh',   'position' => 'left'],
        'KGS' => ['name' => 'Kyrgystani Som',                      'symbol' => 'с',     'position' => 'right'],
        'KHR' => ['name' => 'Cambodian Riel',                      'symbol' => '៛',     'position' => 'right'],
        'KMF' => ['name' => 'Comorian Franc',                      'symbol' => 'CF',    'position' => 'right'],
        'KPW' => ['name' => 'North Korean Won',                    'symbol' => '₩',     'position' => 'left'],
        'KRW' => ['name' => 'South Korean Won',                    'symbol' => '₩',     'position' => 'left'],
        'KWD' => ['name' => 'Kuwaiti Dinar',                       'symbol' => 'د.ك',   'position' => 'left'],
        'KYD' => ['name' => 'Cayman Islands Dollar',               'symbol' => '$',     'position' => 'left'],
        'KZT' => ['name' => 'Kazakhstani Tenge',                   'symbol' => '₸',     'position' => 'left'],
        'LAK' => ['name' => 'Laotian Kip',                         'symbol' => '₭',     'position' => 'left'],
        'LBP' => ['name' => 'Lebanese Pound',                      'symbol' => 'ل.ل',   'position' => 'left'],
        'LKR' => ['name' => 'Sri Lankan Rupee',                    'symbol' => 'Rs',    'position' => 'left'],
        'LRD' => ['name' => 'Liberian Dollar',                     'symbol' => 'L$',    'position' => 'left'],
        'LSL' => ['name' => 'Lesotho Loti',                        'symbol' => 'L',     'position' => 'left'],
        'LYD' => ['name' => 'Libyan Dinar',                        'symbol' => 'ل.د',   'position' => 'left'],
        'MAD' => ['name' => 'Moroccan Dirham',                     'symbol' => 'د.م.',  'position' => 'left'],
        'MDL' => ['name' => 'Moldovan Leu',                        'symbol' => 'L',     'position' => 'right'],
        'MGA' => ['name' => 'Malagasy Ariary',                     'symbol' => 'Ar',    'position' => 'left'],
        'MKD' => ['name' => 'Macedonian Denar',                    'symbol' => 'ден',   'position' => 'right'],
        'MMK' => ['name' => 'Myanmar Kyat',                        'symbol' => 'K',     'position' => 'left'],
        'MNT' => ['name' => 'Mongolian Tugrik',                    'symbol' => '₮',     'position' => 'left'],
        'MOP' => ['name' => 'Macanese Pataca',                     'symbol' => 'MOP$',  'position' => 'left'],
        'MRU' => ['name' => 'Mauritanian Ouguiya',                 'symbol' => 'UM',    'position' => 'right'],
        'MUR' => ['name' => 'Mauritian Rupee',                     'symbol' => '₨',     'position' => 'left'],
        'MVR' => ['name' => 'Maldivian Rufiyaa',                   'symbol' => 'Rf',    'position' => 'left'],
        'MWK' => ['name' => 'Malawian Kwacha',                     'symbol' => 'MK',    'position' => 'left'],
        'MXN' => ['name' => 'Mexican Peso',                        'symbol' => '$',     'position' => 'left'],
        'MYR' => ['name' => 'Malaysian Ringgit',                   'symbol' => 'RM',    'position' => 'left'],
        'MZN' => ['name' => 'Mozambican Metical',                  'symbol' => 'MT',    'position' => 'left'],
        'NAD' => ['name' => 'Namibian Dollar',                     'symbol' => 'N$',    'position' => 'left'],
        'NGN' => ['name' => 'Nigerian Naira',                      'symbol' => '₦',     'position' => 'left'],
        'NIO' => ['name' => 'Nicaraguan Córdoba',                  'symbol' => 'C$',    'position' => 'left'],
        'NOK' => ['name' => 'Norwegian Krone',                     'symbol' => 'kr',    'position' => 'left'],
        'NPR' => ['name' => 'Nepalese Rupee',                      'symbol' => '₨',     'position' => 'left'],
        'NZD' => ['name' => 'New Zealand Dollar',                  'symbol' => '$',     'position' => 'left'],
        'OMR' => ['name' => 'Omani Rial',                          'symbol' => 'ر.ع.',  'position' => 'left'],
        'PAB' => ['name' => 'Panamanian Balboa',                   'symbol' => 'B/.',   'position' => 'left'],
        'PEN' => ['name' => 'Peruvian Sol',                        'symbol' => 'S/.',   'position' => 'left'],
        'PGK' => ['name' => 'Papua New Guinean Kina',              'symbol' => 'K',     'position' => 'left'],
        'PHP' => ['name' => 'Philippine Peso',                     'symbol' => '₱',     'position' => 'left'],
        'PKR' => ['name' => 'Pakistani Rupee',                     'symbol' => '₨',     'position' => 'left'],
        'PLN' => ['name' => 'Polish Zloty',                        'symbol' => 'zł',    'position' => 'right'],
        'PYG' => ['name' => 'Paraguayan Guarani',                  'symbol' => '₲',     'position' => 'left'],
        'QAR' => ['name' => 'Qatari Rial',                         'symbol' => 'ر.ق',   'position' => 'left'],
        'RON' => ['name' => 'Romanian Leu',                        'symbol' => 'lei',   'position' => 'right'],
        'RSD' => ['name' => 'Serbian Dinar',                       'symbol' => 'дин.',  'position' => 'right'],
        'RUB' => ['name' => 'Russian Ruble',                       'symbol' => '₽',     'position' => 'right'],
        'RWF' => ['name' => 'Rwandan Franc',                       'symbol' => 'FRw',   'position' => 'right'],
        'SAR' => ['name' => 'Saudi Riyal',                         'symbol' => '﷼',     'position' => 'left'],
        'SBD' => ['name' => 'Solomon Islands Dollar',              'symbol' => '$',     'position' => 'left'],
        'SCR' => ['name' => 'Seychellois Rupee',                   'symbol' => '₨',     'position' => 'left'],
        'SDG' => ['name' => 'Sudanese Pound',                      'symbol' => 'ج.س.',  'position' => 'left'],
        'SEK' => ['name' => 'Swedish Krona',                       'symbol' => 'kr',    'position' => 'right'],
        'SGD' => ['name' => 'Singapore Dollar',                    'symbol' => 'S$',    'position' => 'left'],
        'SHP' => ['name' => 'St. Helena Pound',                    'symbol' => '£',     'position' => 'left'],
        'SLE' => ['name' => 'Sierra Leonean Leone',                'symbol' => 'Le',    'position' => 'left'],
        'SOS' => ['name' => 'Somali Shilling',                     'symbol' => 'Sh',    'position' => 'left'],
        'SRD' => ['name' => 'Surinamese Dollar',                   'symbol' => '$',     'position' => 'left'],
        'SSP' => ['name' => 'South Sudanese Pound',                'symbol' => '£',     'position' => 'left'],
        'STN' => ['name' => 'São Tomé & Príncipe Dobra',           'symbol' => 'Db',    'position' => 'left'],
        'SYP' => ['name' => 'Syrian Pound',                        'symbol' => '£',     'position' => 'left'],
        'SZL' => ['name' => 'Swazi Lilangeni',                     'symbol' => 'E',     'position' => 'left'],
        'THB' => ['name' => 'Thai Baht',                           'symbol' => '฿',     'position' => 'left'],
        'TJS' => ['name' => 'Tajikistani Somoni',                  'symbol' => 'SM',    'position' => 'right'],
        'TMT' => ['name' => 'Turkmenistani Manat',                 'symbol' => 'T',     'position' => 'left'],
        'TND' => ['name' => 'Tunisian Dinar',                      'symbol' => 'د.ت',   'position' => 'left'],
        'TOP' => ['name' => 'Tongan Paʻanga',                      'symbol' => 'T$',    'position' => 'left'],
        'TRY' => ['name' => 'Turkish Lira',                        'symbol' => '₺',     'position' => 'left'],
        'TTD' => ['name' => 'Trinidad & Tobago Dollar',            'symbol' => 'TT$',   'position' => 'left'],
        'TWD' => ['name' => 'New Taiwan Dollar',                   'symbol' => 'NT$',   'position' => 'left'],
        'TZS' => ['name' => 'Tanzanian Shilling',                  'symbol' => 'TSh',   'position' => 'left'],
        'UAH' => ['name' => 'Ukrainian Hryvnia',                   'symbol' => '₴',     'position' => 'left'],
        'UGX' => ['name' => 'Ugandan Shilling',                    'symbol' => 'USh',   'position' => 'left'],
        'USD' => ['name' => 'US Dollar',                           'symbol' => '$',     'position' => 'left'],
        'UYU' => ['name' => 'Uruguayan Peso',                      'symbol' => '$U',    'position' => 'left'],
        'UZS' => ['name' => 'Uzbekistani Som',                     'symbol' => "so'm",  'position' => 'right'],
        'VES' => ['name' => 'Venezuelan Bolívar Soberano',         'symbol' => 'Bs.S',  'position' => 'left'],
        'VND' => ['name' => 'Vietnamese Dong',                     'symbol' => '₫',     'position' => 'right'],
        'VUV' => ['name' => 'Vanuatu Vatu',                        'symbol' => 'VT',    'position' => 'right'],
        'WST' => ['name' => 'Samoan Tala',                         'symbol' => 'WS$',   'position' => 'left'],
        'XAF' => ['name' => 'Central African CFA Franc',           'symbol' => 'FCFA',  'position' => 'right'],
        'XCD' => ['name' => 'East Caribbean Dollar',               'symbol' => '$',     'position' => 'left'],
        'XOF' => ['name' => 'West African CFA Franc',              'symbol' => 'CFA',   'position' => 'right'],
        'XPF' => ['name' => 'CFP Franc',                           'symbol' => '₣',     'position' => 'right'],
        'YER' => ['name' => 'Yemeni Rial',                         'symbol' => '﷼',     'position' => 'left'],
        'ZAR' => ['name' => 'South African Rand',                  'symbol' => 'R',     'position' => 'left'],
        'ZMW' => ['name' => 'Zambian Kwacha',                      'symbol' => 'ZK',    'position' => 'left'],
        'ZWL' => ['name' => 'Zimbabwean Dollar',                   'symbol' => 'Z$',    'position' => 'left'],
    ];

    /** @return array<string, array{name: string, symbol: string, position: string}> */
    public static function all(): array
    {
        return self::LIST;
    }

    /** @return array{name: string, symbol: string, position: string}|null */
    public static function find(string $code): ?array
    {
        return self::LIST[strtoupper($code)] ?? null;
    }

    /**
     * Format a monetary amount using the correct symbol and position for the given currency code.
     *
     * Pass $fromMinorUnit = true when the amount is stored in the minor unit (e.g. paisa for BDT,
     * cents for USD). The value will be divided by 100 before formatting.
     */
    public static function format(
        int|float $amount,
        string $code = 'BDT',
        int $decimals = 2,
        bool $fromMinorUnit = false,
    ): string {
        $currency = self::find($code);
        $symbol = $currency['symbol'] ?? $code;
        $position = $currency['position'] ?? 'left';

        if ($fromMinorUnit) {
            $amount = $amount / 100;
        }

        $formatted = number_format((float) $amount, $decimals);

        return $position === 'left'
            ? $symbol.$formatted
            : $formatted.$symbol;
    }

    /**
     * Return the symbol for a currency code, falling back to the code itself.
     */
    public static function symbol(string $code): string
    {
        return self::LIST[strtoupper($code)]['symbol'] ?? $code;
    }
}
