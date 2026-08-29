<?php

namespace App\Support;

/**
 * ISO 3166-1 alpha-2 country reference data.
 *
 * Static reference data, mirroring App\Support\Currencies — countries are not
 * merchant-editable, so they do not belong in a table. `postal` records whether the
 * country uses postal codes at all (UPU): 'required', 'optional', or 'none'. Several
 * real countries (UAE, Hong Kong, Qatar, Panama, Jamaica) have no postal system, so a
 * blanket "postal code required" rule would make checkout impossible there.
 */
class Countries
{
    /** @var array<string, array{name: string, dial: string, currency: string, postal: string}> */
    private const LIST = [
        'AD' => ['name' => 'Andorra',                          'dial' => '376', 'currency' => 'EUR', 'postal' => 'required'],
        'AE' => ['name' => 'United Arab Emirates',             'dial' => '971', 'currency' => 'AED', 'postal' => 'none'],
        'AF' => ['name' => 'Afghanistan',                      'dial' => '93', 'currency' => 'AFN', 'postal' => 'optional'],
        'AG' => ['name' => 'Antigua and Barbuda',              'dial' => '1268', 'currency' => 'XCD', 'postal' => 'none'],
        'AI' => ['name' => 'Anguilla',                         'dial' => '1264', 'currency' => 'XCD', 'postal' => 'optional'],
        'AL' => ['name' => 'Albania',                          'dial' => '355', 'currency' => 'ALL', 'postal' => 'optional'],
        'AM' => ['name' => 'Armenia',                          'dial' => '374', 'currency' => 'AMD', 'postal' => 'optional'],
        'AO' => ['name' => 'Angola',                           'dial' => '244', 'currency' => 'AOA', 'postal' => 'none'],
        'AR' => ['name' => 'Argentina',                        'dial' => '54', 'currency' => 'ARS', 'postal' => 'required'],
        'AS' => ['name' => 'American Samoa',                   'dial' => '1684', 'currency' => 'USD', 'postal' => 'required'],
        'AT' => ['name' => 'Austria',                          'dial' => '43', 'currency' => 'EUR', 'postal' => 'required'],
        'AU' => ['name' => 'Australia',                        'dial' => '61', 'currency' => 'AUD', 'postal' => 'required'],
        'AW' => ['name' => 'Aruba',                            'dial' => '297', 'currency' => 'AWG', 'postal' => 'none'],
        'AX' => ['name' => 'Åland Islands',                    'dial' => '358', 'currency' => 'EUR', 'postal' => 'required'],
        'AZ' => ['name' => 'Azerbaijan',                       'dial' => '994', 'currency' => 'AZN', 'postal' => 'required'],
        'BA' => ['name' => 'Bosnia and Herzegovina',           'dial' => '387', 'currency' => 'BAM', 'postal' => 'required'],
        'BB' => ['name' => 'Barbados',                         'dial' => '1246', 'currency' => 'BBD', 'postal' => 'optional'],
        'BD' => ['name' => 'Bangladesh',                       'dial' => '880', 'currency' => 'BDT', 'postal' => 'required'],
        'BE' => ['name' => 'Belgium',                          'dial' => '32', 'currency' => 'EUR', 'postal' => 'required'],
        'BF' => ['name' => 'Burkina Faso',                     'dial' => '226', 'currency' => 'XOF', 'postal' => 'none'],
        'BG' => ['name' => 'Bulgaria',                         'dial' => '359', 'currency' => 'BGN', 'postal' => 'required'],
        'BH' => ['name' => 'Bahrain',                          'dial' => '973', 'currency' => 'BHD', 'postal' => 'optional'],
        'BI' => ['name' => 'Burundi',                          'dial' => '257', 'currency' => 'BIF', 'postal' => 'none'],
        'BJ' => ['name' => 'Benin',                            'dial' => '229', 'currency' => 'XOF', 'postal' => 'none'],
        'BM' => ['name' => 'Bermuda',                          'dial' => '1441', 'currency' => 'BMD', 'postal' => 'required'],
        'BN' => ['name' => 'Brunei',                           'dial' => '673', 'currency' => 'BND', 'postal' => 'required'],
        'BO' => ['name' => 'Bolivia',                          'dial' => '591', 'currency' => 'BOB', 'postal' => 'optional'],
        'BR' => ['name' => 'Brazil',                           'dial' => '55', 'currency' => 'BRL', 'postal' => 'required'],
        'BS' => ['name' => 'Bahamas',                          'dial' => '1242', 'currency' => 'BSD', 'postal' => 'none'],
        'BT' => ['name' => 'Bhutan',                           'dial' => '975', 'currency' => 'BTN', 'postal' => 'optional'],
        'BW' => ['name' => 'Botswana',                         'dial' => '267', 'currency' => 'BWP', 'postal' => 'none'],
        'BY' => ['name' => 'Belarus',                          'dial' => '375', 'currency' => 'BYN', 'postal' => 'required'],
        'BZ' => ['name' => 'Belize',                           'dial' => '501', 'currency' => 'BZD', 'postal' => 'none'],
        'CA' => ['name' => 'Canada',                           'dial' => '1', 'currency' => 'CAD', 'postal' => 'required'],
        'CD' => ['name' => 'DR Congo',                         'dial' => '243', 'currency' => 'CDF', 'postal' => 'none'],
        'CF' => ['name' => 'Central African Republic',         'dial' => '236', 'currency' => 'XAF', 'postal' => 'none'],
        'CG' => ['name' => 'Republic of the Congo',            'dial' => '242', 'currency' => 'XAF', 'postal' => 'none'],
        'CH' => ['name' => 'Switzerland',                      'dial' => '41', 'currency' => 'CHF', 'postal' => 'required'],
        'CI' => ['name' => 'Côte d\'Ivoire',                   'dial' => '225', 'currency' => 'XOF', 'postal' => 'none'],
        'CL' => ['name' => 'Chile',                            'dial' => '56', 'currency' => 'CLP', 'postal' => 'optional'],
        'CM' => ['name' => 'Cameroon',                         'dial' => '237', 'currency' => 'XAF', 'postal' => 'none'],
        'CN' => ['name' => 'China',                            'dial' => '86', 'currency' => 'CNY', 'postal' => 'required'],
        'CO' => ['name' => 'Colombia',                         'dial' => '57', 'currency' => 'COP', 'postal' => 'optional'],
        'CR' => ['name' => 'Costa Rica',                       'dial' => '506', 'currency' => 'CRC', 'postal' => 'required'],
        'CU' => ['name' => 'Cuba',                             'dial' => '53', 'currency' => 'CUP', 'postal' => 'optional'],
        'CV' => ['name' => 'Cape Verde',                       'dial' => '238', 'currency' => 'CVE', 'postal' => 'optional'],
        'CY' => ['name' => 'Cyprus',                           'dial' => '357', 'currency' => 'EUR', 'postal' => 'required'],
        'CZ' => ['name' => 'Czechia',                          'dial' => '420', 'currency' => 'CZK', 'postal' => 'required'],
        'DE' => ['name' => 'Germany',                          'dial' => '49', 'currency' => 'EUR', 'postal' => 'required'],
        'DJ' => ['name' => 'Djibouti',                         'dial' => '253', 'currency' => 'DJF', 'postal' => 'none'],
        'DK' => ['name' => 'Denmark',                          'dial' => '45', 'currency' => 'DKK', 'postal' => 'required'],
        'DM' => ['name' => 'Dominica',                         'dial' => '1767', 'currency' => 'XCD', 'postal' => 'none'],
        'DO' => ['name' => 'Dominican Republic',               'dial' => '1809', 'currency' => 'DOP', 'postal' => 'optional'],
        'DZ' => ['name' => 'Algeria',                          'dial' => '213', 'currency' => 'DZD', 'postal' => 'required'],
        'EC' => ['name' => 'Ecuador',                          'dial' => '593', 'currency' => 'USD', 'postal' => 'optional'],
        'EE' => ['name' => 'Estonia',                          'dial' => '372', 'currency' => 'EUR', 'postal' => 'required'],
        'EG' => ['name' => 'Egypt',                            'dial' => '20', 'currency' => 'EGP', 'postal' => 'optional'],
        'ER' => ['name' => 'Eritrea',                          'dial' => '291', 'currency' => 'ERN', 'postal' => 'none'],
        'ES' => ['name' => 'Spain',                            'dial' => '34', 'currency' => 'EUR', 'postal' => 'required'],
        'ET' => ['name' => 'Ethiopia',                         'dial' => '251', 'currency' => 'ETB', 'postal' => 'optional'],
        'FI' => ['name' => 'Finland',                          'dial' => '358', 'currency' => 'EUR', 'postal' => 'required'],
        'FJ' => ['name' => 'Fiji',                             'dial' => '679', 'currency' => 'FJD', 'postal' => 'none'],
        'FO' => ['name' => 'Faroe Islands',                    'dial' => '298', 'currency' => 'DKK', 'postal' => 'required'],
        'FR' => ['name' => 'France',                           'dial' => '33', 'currency' => 'EUR', 'postal' => 'required'],
        'GA' => ['name' => 'Gabon',                            'dial' => '241', 'currency' => 'XAF', 'postal' => 'optional'],
        'GB' => ['name' => 'United Kingdom',                   'dial' => '44', 'currency' => 'GBP', 'postal' => 'required'],
        'GD' => ['name' => 'Grenada',                          'dial' => '1473', 'currency' => 'XCD', 'postal' => 'none'],
        'GE' => ['name' => 'Georgia',                          'dial' => '995', 'currency' => 'GEL', 'postal' => 'optional'],
        'GF' => ['name' => 'French Guiana',                    'dial' => '594', 'currency' => 'EUR', 'postal' => 'required'],
        'GG' => ['name' => 'Guernsey',                         'dial' => '44', 'currency' => 'GBP', 'postal' => 'required'],
        'GH' => ['name' => 'Ghana',                            'dial' => '233', 'currency' => 'GHS', 'postal' => 'none'],
        'GI' => ['name' => 'Gibraltar',                        'dial' => '350', 'currency' => 'GIP', 'postal' => 'optional'],
        'GL' => ['name' => 'Greenland',                        'dial' => '299', 'currency' => 'DKK', 'postal' => 'required'],
        'GM' => ['name' => 'Gambia',                           'dial' => '220', 'currency' => 'GMD', 'postal' => 'none'],
        'GN' => ['name' => 'Guinea',                           'dial' => '224', 'currency' => 'GNF', 'postal' => 'optional'],
        'GP' => ['name' => 'Guadeloupe',                       'dial' => '590', 'currency' => 'EUR', 'postal' => 'required'],
        'GQ' => ['name' => 'Equatorial Guinea',                'dial' => '240', 'currency' => 'XAF', 'postal' => 'none'],
        'GR' => ['name' => 'Greece',                           'dial' => '30', 'currency' => 'EUR', 'postal' => 'required'],
        'GT' => ['name' => 'Guatemala',                        'dial' => '502', 'currency' => 'GTQ', 'postal' => 'optional'],
        'GU' => ['name' => 'Guam',                             'dial' => '1671', 'currency' => 'USD', 'postal' => 'required'],
        'GW' => ['name' => 'Guinea-Bissau',                    'dial' => '245', 'currency' => 'XOF', 'postal' => 'optional'],
        'GY' => ['name' => 'Guyana',                           'dial' => '592', 'currency' => 'GYD', 'postal' => 'none'],
        'HK' => ['name' => 'Hong Kong',                        'dial' => '852', 'currency' => 'HKD', 'postal' => 'none'],
        'HN' => ['name' => 'Honduras',                         'dial' => '504', 'currency' => 'HNL', 'postal' => 'optional'],
        'HR' => ['name' => 'Croatia',                          'dial' => '385', 'currency' => 'EUR', 'postal' => 'required'],
        'HT' => ['name' => 'Haiti',                            'dial' => '509', 'currency' => 'HTG', 'postal' => 'optional'],
        'HU' => ['name' => 'Hungary',                          'dial' => '36', 'currency' => 'HUF', 'postal' => 'required'],
        'ID' => ['name' => 'Indonesia',                        'dial' => '62', 'currency' => 'IDR', 'postal' => 'required'],
        'IE' => ['name' => 'Ireland',                          'dial' => '353', 'currency' => 'EUR', 'postal' => 'optional'],
        'IL' => ['name' => 'Israel',                           'dial' => '972', 'currency' => 'ILS', 'postal' => 'required'],
        'IM' => ['name' => 'Isle of Man',                      'dial' => '44', 'currency' => 'GBP', 'postal' => 'required'],
        'IN' => ['name' => 'India',                            'dial' => '91', 'currency' => 'INR', 'postal' => 'required'],
        'IQ' => ['name' => 'Iraq',                             'dial' => '964', 'currency' => 'IQD', 'postal' => 'optional'],
        'IR' => ['name' => 'Iran',                             'dial' => '98', 'currency' => 'IRR', 'postal' => 'required'],
        'IS' => ['name' => 'Iceland',                          'dial' => '354', 'currency' => 'ISK', 'postal' => 'required'],
        'IT' => ['name' => 'Italy',                            'dial' => '39', 'currency' => 'EUR', 'postal' => 'required'],
        'JE' => ['name' => 'Jersey',                           'dial' => '44', 'currency' => 'GBP', 'postal' => 'required'],
        'JM' => ['name' => 'Jamaica',                          'dial' => '1876', 'currency' => 'JMD', 'postal' => 'none'],
        'JO' => ['name' => 'Jordan',                           'dial' => '962', 'currency' => 'JOD', 'postal' => 'optional'],
        'JP' => ['name' => 'Japan',                            'dial' => '81', 'currency' => 'JPY', 'postal' => 'required'],
        'KE' => ['name' => 'Kenya',                            'dial' => '254', 'currency' => 'KES', 'postal' => 'none'],
        'KG' => ['name' => 'Kyrgyzstan',                       'dial' => '996', 'currency' => 'KGS', 'postal' => 'optional'],
        'KH' => ['name' => 'Cambodia',                         'dial' => '855', 'currency' => 'KHR', 'postal' => 'optional'],
        'KM' => ['name' => 'Comoros',                          'dial' => '269', 'currency' => 'KMF', 'postal' => 'none'],
        'KN' => ['name' => 'Saint Kitts and Nevis',            'dial' => '1869', 'currency' => 'XCD', 'postal' => 'none'],
        'KR' => ['name' => 'South Korea',                      'dial' => '82', 'currency' => 'KRW', 'postal' => 'required'],
        'KW' => ['name' => 'Kuwait',                           'dial' => '965', 'currency' => 'KWD', 'postal' => 'optional'],
        'KY' => ['name' => 'Cayman Islands',                   'dial' => '1345', 'currency' => 'KYD', 'postal' => 'optional'],
        'KZ' => ['name' => 'Kazakhstan',                       'dial' => '7', 'currency' => 'KZT', 'postal' => 'required'],
        'LA' => ['name' => 'Laos',                             'dial' => '856', 'currency' => 'LAK', 'postal' => 'optional'],
        'LB' => ['name' => 'Lebanon',                          'dial' => '961', 'currency' => 'LBP', 'postal' => 'optional'],
        'LC' => ['name' => 'Saint Lucia',                      'dial' => '1758', 'currency' => 'XCD', 'postal' => 'none'],
        'LI' => ['name' => 'Liechtenstein',                    'dial' => '423', 'currency' => 'CHF', 'postal' => 'required'],
        'LK' => ['name' => 'Sri Lanka',                        'dial' => '94', 'currency' => 'LKR', 'postal' => 'required'],
        'LR' => ['name' => 'Liberia',                          'dial' => '231', 'currency' => 'LRD', 'postal' => 'optional'],
        'LS' => ['name' => 'Lesotho',                          'dial' => '266', 'currency' => 'LSL', 'postal' => 'optional'],
        'LT' => ['name' => 'Lithuania',                        'dial' => '370', 'currency' => 'EUR', 'postal' => 'required'],
        'LU' => ['name' => 'Luxembourg',                       'dial' => '352', 'currency' => 'EUR', 'postal' => 'required'],
        'LV' => ['name' => 'Latvia',                           'dial' => '371', 'currency' => 'EUR', 'postal' => 'required'],
        'LY' => ['name' => 'Libya',                            'dial' => '218', 'currency' => 'LYD', 'postal' => 'none'],
        'MA' => ['name' => 'Morocco',                          'dial' => '212', 'currency' => 'MAD', 'postal' => 'required'],
        'MC' => ['name' => 'Monaco',                           'dial' => '377', 'currency' => 'EUR', 'postal' => 'required'],
        'MD' => ['name' => 'Moldova',                          'dial' => '373', 'currency' => 'MDL', 'postal' => 'required'],
        'ME' => ['name' => 'Montenegro',                       'dial' => '382', 'currency' => 'EUR', 'postal' => 'required'],
        'MG' => ['name' => 'Madagascar',                       'dial' => '261', 'currency' => 'MGA', 'postal' => 'optional'],
        'MK' => ['name' => 'North Macedonia',                  'dial' => '389', 'currency' => 'MKD', 'postal' => 'optional'],
        'ML' => ['name' => 'Mali',                             'dial' => '223', 'currency' => 'XOF', 'postal' => 'none'],
        'MM' => ['name' => 'Myanmar',                          'dial' => '95', 'currency' => 'MMK', 'postal' => 'optional'],
        'MN' => ['name' => 'Mongolia',                         'dial' => '976', 'currency' => 'MNT', 'postal' => 'optional'],
        'MO' => ['name' => 'Macao',                            'dial' => '853', 'currency' => 'MOP', 'postal' => 'none'],
        'MQ' => ['name' => 'Martinique',                       'dial' => '596', 'currency' => 'EUR', 'postal' => 'required'],
        'MR' => ['name' => 'Mauritania',                       'dial' => '222', 'currency' => 'MRU', 'postal' => 'none'],
        'MT' => ['name' => 'Malta',                            'dial' => '356', 'currency' => 'EUR', 'postal' => 'required'],
        'MU' => ['name' => 'Mauritius',                        'dial' => '230', 'currency' => 'MUR', 'postal' => 'none'],
        'MV' => ['name' => 'Maldives',                         'dial' => '960', 'currency' => 'MVR', 'postal' => 'optional'],
        'MW' => ['name' => 'Malawi',                           'dial' => '265', 'currency' => 'MWK', 'postal' => 'none'],
        'MX' => ['name' => 'Mexico',                           'dial' => '52', 'currency' => 'MXN', 'postal' => 'required'],
        'MY' => ['name' => 'Malaysia',                         'dial' => '60', 'currency' => 'MYR', 'postal' => 'required'],
        'MZ' => ['name' => 'Mozambique',                       'dial' => '258', 'currency' => 'MZN', 'postal' => 'optional'],
        'NA' => ['name' => 'Namibia',                          'dial' => '264', 'currency' => 'NAD', 'postal' => 'optional'],
        'NC' => ['name' => 'New Caledonia',                    'dial' => '687', 'currency' => 'XPF', 'postal' => 'required'],
        'NE' => ['name' => 'Niger',                            'dial' => '227', 'currency' => 'XOF', 'postal' => 'optional'],
        'NG' => ['name' => 'Nigeria',                          'dial' => '234', 'currency' => 'NGN', 'postal' => 'optional'],
        'NI' => ['name' => 'Nicaragua',                        'dial' => '505', 'currency' => 'NIO', 'postal' => 'optional'],
        'NL' => ['name' => 'Netherlands',                      'dial' => '31', 'currency' => 'EUR', 'postal' => 'required'],
        'NO' => ['name' => 'Norway',                           'dial' => '47', 'currency' => 'NOK', 'postal' => 'required'],
        'NP' => ['name' => 'Nepal',                            'dial' => '977', 'currency' => 'NPR', 'postal' => 'optional'],
        'NZ' => ['name' => 'New Zealand',                      'dial' => '64', 'currency' => 'NZD', 'postal' => 'required'],
        'OM' => ['name' => 'Oman',                             'dial' => '968', 'currency' => 'OMR', 'postal' => 'optional'],
        'PA' => ['name' => 'Panama',                           'dial' => '507', 'currency' => 'PAB', 'postal' => 'none'],
        'PE' => ['name' => 'Peru',                             'dial' => '51', 'currency' => 'PEN', 'postal' => 'optional'],
        'PF' => ['name' => 'French Polynesia',                 'dial' => '689', 'currency' => 'XPF', 'postal' => 'required'],
        'PG' => ['name' => 'Papua New Guinea',                 'dial' => '675', 'currency' => 'PGK', 'postal' => 'optional'],
        'PH' => ['name' => 'Philippines',                      'dial' => '63', 'currency' => 'PHP', 'postal' => 'required'],
        'PK' => ['name' => 'Pakistan',                         'dial' => '92', 'currency' => 'PKR', 'postal' => 'required'],
        'PL' => ['name' => 'Poland',                           'dial' => '48', 'currency' => 'PLN', 'postal' => 'required'],
        'PR' => ['name' => 'Puerto Rico',                      'dial' => '1787', 'currency' => 'USD', 'postal' => 'required'],
        'PS' => ['name' => 'Palestine',                        'dial' => '970', 'currency' => 'ILS', 'postal' => 'optional'],
        'PT' => ['name' => 'Portugal',                         'dial' => '351', 'currency' => 'EUR', 'postal' => 'required'],
        'PY' => ['name' => 'Paraguay',                         'dial' => '595', 'currency' => 'PYG', 'postal' => 'optional'],
        'QA' => ['name' => 'Qatar',                            'dial' => '974', 'currency' => 'QAR', 'postal' => 'none'],
        'RE' => ['name' => 'Réunion',                          'dial' => '262', 'currency' => 'EUR', 'postal' => 'required'],
        'RO' => ['name' => 'Romania',                          'dial' => '40', 'currency' => 'RON', 'postal' => 'required'],
        'RS' => ['name' => 'Serbia',                           'dial' => '381', 'currency' => 'RSD', 'postal' => 'required'],
        'RU' => ['name' => 'Russia',                           'dial' => '7', 'currency' => 'RUB', 'postal' => 'required'],
        'RW' => ['name' => 'Rwanda',                           'dial' => '250', 'currency' => 'RWF', 'postal' => 'none'],
        'SA' => ['name' => 'Saudi Arabia',                     'dial' => '966', 'currency' => 'SAR', 'postal' => 'required'],
        'SC' => ['name' => 'Seychelles',                       'dial' => '248', 'currency' => 'SCR', 'postal' => 'none'],
        'SD' => ['name' => 'Sudan',                            'dial' => '249', 'currency' => 'SDG', 'postal' => 'optional'],
        'SE' => ['name' => 'Sweden',                           'dial' => '46', 'currency' => 'SEK', 'postal' => 'required'],
        'SG' => ['name' => 'Singapore',                        'dial' => '65', 'currency' => 'SGD', 'postal' => 'required'],
        'SI' => ['name' => 'Slovenia',                         'dial' => '386', 'currency' => 'EUR', 'postal' => 'required'],
        'SK' => ['name' => 'Slovakia',                         'dial' => '421', 'currency' => 'EUR', 'postal' => 'required'],
        'SL' => ['name' => 'Sierra Leone',                     'dial' => '232', 'currency' => 'SLE', 'postal' => 'none'],
        'SM' => ['name' => 'San Marino',                       'dial' => '378', 'currency' => 'EUR', 'postal' => 'required'],
        'SN' => ['name' => 'Senegal',                          'dial' => '221', 'currency' => 'XOF', 'postal' => 'optional'],
        'SO' => ['name' => 'Somalia',                          'dial' => '252', 'currency' => 'SOS', 'postal' => 'none'],
        'SR' => ['name' => 'Suriname',                         'dial' => '597', 'currency' => 'SRD', 'postal' => 'none'],
        'SS' => ['name' => 'South Sudan',                      'dial' => '211', 'currency' => 'SSP', 'postal' => 'optional'],
        'SV' => ['name' => 'El Salvador',                      'dial' => '503', 'currency' => 'USD', 'postal' => 'optional'],
        'SY' => ['name' => 'Syria',                            'dial' => '963', 'currency' => 'SYP', 'postal' => 'none'],
        'SZ' => ['name' => 'Eswatini',                         'dial' => '268', 'currency' => 'SZL', 'postal' => 'optional'],
        'TD' => ['name' => 'Chad',                             'dial' => '235', 'currency' => 'XAF', 'postal' => 'optional'],
        'TG' => ['name' => 'Togo',                             'dial' => '228', 'currency' => 'XOF', 'postal' => 'none'],
        'TH' => ['name' => 'Thailand',                         'dial' => '66', 'currency' => 'THB', 'postal' => 'required'],
        'TJ' => ['name' => 'Tajikistan',                       'dial' => '992', 'currency' => 'TJS', 'postal' => 'optional'],
        'TL' => ['name' => 'Timor-Leste',                      'dial' => '670', 'currency' => 'USD', 'postal' => 'none'],
        'TM' => ['name' => 'Turkmenistan',                     'dial' => '993', 'currency' => 'TMT', 'postal' => 'optional'],
        'TN' => ['name' => 'Tunisia',                          'dial' => '216', 'currency' => 'TND', 'postal' => 'required'],
        'TO' => ['name' => 'Tonga',                            'dial' => '676', 'currency' => 'TOP', 'postal' => 'none'],
        'TR' => ['name' => 'Türkiye',                          'dial' => '90', 'currency' => 'TRY', 'postal' => 'required'],
        'TT' => ['name' => 'Trinidad and Tobago',              'dial' => '1868', 'currency' => 'TTD', 'postal' => 'none'],
        'TW' => ['name' => 'Taiwan',                           'dial' => '886', 'currency' => 'TWD', 'postal' => 'required'],
        'TZ' => ['name' => 'Tanzania',                         'dial' => '255', 'currency' => 'TZS', 'postal' => 'none'],
        'UA' => ['name' => 'Ukraine',                          'dial' => '380', 'currency' => 'UAH', 'postal' => 'required'],
        'UG' => ['name' => 'Uganda',                           'dial' => '256', 'currency' => 'UGX', 'postal' => 'none'],
        'US' => ['name' => 'United States',                    'dial' => '1', 'currency' => 'USD', 'postal' => 'required'],
        'UY' => ['name' => 'Uruguay',                          'dial' => '598', 'currency' => 'UYU', 'postal' => 'optional'],
        'UZ' => ['name' => 'Uzbekistan',                       'dial' => '998', 'currency' => 'UZS', 'postal' => 'optional'],
        'VA' => ['name' => 'Vatican City',                     'dial' => '379', 'currency' => 'EUR', 'postal' => 'required'],
        'VC' => ['name' => 'Saint Vincent and the Grenadines', 'dial' => '1784', 'currency' => 'XCD', 'postal' => 'optional'],
        'VE' => ['name' => 'Venezuela',                        'dial' => '58', 'currency' => 'VES', 'postal' => 'optional'],
        'VG' => ['name' => 'British Virgin Islands',           'dial' => '1284', 'currency' => 'USD', 'postal' => 'optional'],
        'VI' => ['name' => 'U.S. Virgin Islands',              'dial' => '1340', 'currency' => 'USD', 'postal' => 'required'],
        'VN' => ['name' => 'Vietnam',                          'dial' => '84', 'currency' => 'VND', 'postal' => 'required'],
        'VU' => ['name' => 'Vanuatu',                          'dial' => '678', 'currency' => 'VUV', 'postal' => 'none'],
        'WS' => ['name' => 'Samoa',                            'dial' => '685', 'currency' => 'WST', 'postal' => 'optional'],
        'XK' => ['name' => 'Kosovo',                           'dial' => '383', 'currency' => 'EUR', 'postal' => 'required'],
        'YE' => ['name' => 'Yemen',                            'dial' => '967', 'currency' => 'YER', 'postal' => 'none'],
        'YT' => ['name' => 'Mayotte',                          'dial' => '262', 'currency' => 'EUR', 'postal' => 'required'],
        'ZA' => ['name' => 'South Africa',                     'dial' => '27', 'currency' => 'ZAR', 'postal' => 'required'],
        'ZM' => ['name' => 'Zambia',                           'dial' => '260', 'currency' => 'ZMW', 'postal' => 'optional'],
        'ZW' => ['name' => 'Zimbabwe',                         'dial' => '263', 'currency' => 'ZWL', 'postal' => 'none'],
    ];

    /**
     * Subdivisions for the countries where state/province is structured and load-bearing
     * for tax or shipping. Countries absent from this list fall back to a free-text field.
     *
     * @var array<string, array<string, string>>
     */
    private const SUBDIVISIONS = [
        'US' => [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ],
        'CA' => [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland and Labrador',
            'NS' => 'Nova Scotia',
            'NT' => 'Northwest Territories',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon',
        ],
        'AU' => [
            'ACT' => 'Australian Capital Territory',
            'NSW' => 'New South Wales',
            'NT' => 'Northern Territory',
            'QLD' => 'Queensland',
            'SA' => 'South Australia',
            'TAS' => 'Tasmania',
            'VIC' => 'Victoria',
            'WA' => 'Western Australia',
        ],
        'IN' => [
            'AN' => 'Andaman and Nicobar Islands',
            'AP' => 'Andhra Pradesh',
            'AR' => 'Arunachal Pradesh',
            'AS' => 'Assam',
            'BR' => 'Bihar',
            'CH' => 'Chandigarh',
            'CT' => 'Chhattisgarh',
            'DH' => 'Dadra and Nagar Haveli and Daman and Diu',
            'DL' => 'Delhi',
            'GA' => 'Goa',
            'GJ' => 'Gujarat',
            'HR' => 'Haryana',
            'HP' => 'Himachal Pradesh',
            'JK' => 'Jammu and Kashmir',
            'JH' => 'Jharkhand',
            'KA' => 'Karnataka',
            'KL' => 'Kerala',
            'LA' => 'Ladakh',
            'LD' => 'Lakshadweep',
            'MP' => 'Madhya Pradesh',
            'MH' => 'Maharashtra',
            'MN' => 'Manipur',
            'ML' => 'Meghalaya',
            'MZ' => 'Mizoram',
            'NL' => 'Nagaland',
            'OR' => 'Odisha',
            'PY' => 'Puducherry',
            'PB' => 'Punjab',
            'RJ' => 'Rajasthan',
            'SK' => 'Sikkim',
            'TN' => 'Tamil Nadu',
            'TG' => 'Telangana',
            'TR' => 'Tripura',
            'UP' => 'Uttar Pradesh',
            'UT' => 'Uttarakhand',
            'WB' => 'West Bengal',
        ],
        'BR' => [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins',
        ],
        'MX' => [
            'AGU' => 'Aguascalientes',
            'BCN' => 'Baja California',
            'BCS' => 'Baja California Sur',
            'CAM' => 'Campeche',
            'CHP' => 'Chiapas',
            'CHH' => 'Chihuahua',
            'CMX' => 'Ciudad de México',
            'COA' => 'Coahuila',
            'COL' => 'Colima',
            'DUR' => 'Durango',
            'GUA' => 'Guanajuato',
            'GRO' => 'Guerrero',
            'HID' => 'Hidalgo',
            'JAL' => 'Jalisco',
            'MEX' => 'México',
            'MIC' => 'Michoacán',
            'MOR' => 'Morelos',
            'NAY' => 'Nayarit',
            'NLE' => 'Nuevo León',
            'OAX' => 'Oaxaca',
            'PUE' => 'Puebla',
            'QUE' => 'Querétaro',
            'ROO' => 'Quintana Roo',
            'SLP' => 'San Luis Potosí',
            'SIN' => 'Sinaloa',
            'SON' => 'Sonora',
            'TAB' => 'Tabasco',
            'TAM' => 'Tamaulipas',
            'TLA' => 'Tlaxcala',
            'VER' => 'Veracruz',
            'YUC' => 'Yucatán',
            'ZAC' => 'Zacatecas',
        ],
        'MY' => [
            'JHR' => 'Johor',
            'KDH' => 'Kedah',
            'KTN' => 'Kelantan',
            'KUL' => 'Kuala Lumpur',
            'LBN' => 'Labuan',
            'MLK' => 'Melaka',
            'NSN' => 'Negeri Sembilan',
            'PHG' => 'Pahang',
            'PNG' => 'Pulau Pinang',
            'PRK' => 'Perak',
            'PLS' => 'Perlis',
            'PJY' => 'Putrajaya',
            'SBH' => 'Sabah',
            'SWK' => 'Sarawak',
            'SGR' => 'Selangor',
            'TRG' => 'Terengganu',
        ],
        'ZA' => [
            'EC' => 'Eastern Cape',
            'FS' => 'Free State',
            'GP' => 'Gauteng',
            'KZN' => 'KwaZulu-Natal',
            'LP' => 'Limpopo',
            'MP' => 'Mpumalanga',
            'NC' => 'Northern Cape',
            'NW' => 'North West',
            'WC' => 'Western Cape',
        ],
        'NG' => [
            'AB' => 'Abia',
            'AD' => 'Adamawa',
            'AK' => 'Akwa Ibom',
            'AN' => 'Anambra',
            'BA' => 'Bauchi',
            'BY' => 'Bayelsa',
            'BE' => 'Benue',
            'BO' => 'Borno',
            'CR' => 'Cross River',
            'DE' => 'Delta',
            'EB' => 'Ebonyi',
            'ED' => 'Edo',
            'EK' => 'Ekiti',
            'EN' => 'Enugu',
            'FC' => 'Federal Capital Territory',
            'GO' => 'Gombe',
            'IM' => 'Imo',
            'JI' => 'Jigawa',
            'KD' => 'Kaduna',
            'KN' => 'Kano',
            'KT' => 'Katsina',
            'KE' => 'Kebbi',
            'KO' => 'Kogi',
            'KW' => 'Kwara',
            'LA' => 'Lagos',
            'NA' => 'Nasarawa',
            'NI' => 'Niger',
            'OG' => 'Ogun',
            'ON' => 'Ondo',
            'OS' => 'Osun',
            'OY' => 'Oyo',
            'PL' => 'Plateau',
            'RI' => 'Rivers',
            'SO' => 'Sokoto',
            'TA' => 'Taraba',
            'YO' => 'Yobe',
            'ZA' => 'Zamfara',
        ],
        'AR' => [
            'C' => 'Ciudad Autónoma de Buenos Aires',
            'B' => 'Buenos Aires',
            'K' => 'Catamarca',
            'H' => 'Chaco',
            'U' => 'Chubut',
            'X' => 'Córdoba',
            'W' => 'Corrientes',
            'E' => 'Entre Ríos',
            'P' => 'Formosa',
            'Y' => 'Jujuy',
            'L' => 'La Pampa',
            'F' => 'La Rioja',
            'M' => 'Mendoza',
            'N' => 'Misiones',
            'Q' => 'Neuquén',
            'R' => 'Río Negro',
            'A' => 'Salta',
            'J' => 'San Juan',
            'D' => 'San Luis',
            'Z' => 'Santa Cruz',
            'S' => 'Santa Fe',
            'G' => 'Santiago del Estero',
            'V' => 'Tierra del Fuego',
            'T' => 'Tucumán',
        ],
        'BD' => [
            'BA' => 'Barishal',
            'CH' => 'Chattogram',
            'DH' => 'Dhaka',
            'KH' => 'Khulna',
            'MY' => 'Mymensingh',
            'RA' => 'Rajshahi',
            'RP' => 'Rangpur',
            'SY' => 'Sylhet',
        ],
    ];

    /** @return array<string, array{name: string, dial: string, currency: string, postal: string}> */
    public static function all(): array
    {
        return self::LIST;
    }

    /** @return array{name: string, dial: string, currency: string, postal: string}|null */
    public static function find(string $code): ?array
    {
        return self::LIST[strtoupper($code)] ?? null;
    }

    public static function exists(string $code): bool
    {
        return isset(self::LIST[strtoupper($code)]);
    }

    /** Country name, falling back to the code itself for unknown values. */
    public static function name(string $code): string
    {
        return self::LIST[strtoupper($code)]['name'] ?? $code;
    }

    /** International dialling prefix without the leading "+". */
    public static function dial(string $code): ?string
    {
        return self::LIST[strtoupper($code)]['dial'] ?? null;
    }

    /** The country's usual ISO 4217 currency code. */
    public static function currency(string $code): ?string
    {
        return self::LIST[strtoupper($code)]['currency'] ?? null;
    }

    /** Whether the country uses postal codes at all. */
    public static function hasPostalCode(string $code): bool
    {
        return (self::LIST[strtoupper($code)]['postal'] ?? 'optional') !== 'none';
    }

    /** Whether a postal code must be supplied for a deliverable address. */
    public static function requiresPostalCode(string $code): bool
    {
        return (self::LIST[strtoupper($code)]['postal'] ?? 'optional') === 'required';
    }

    /** @return array<string, string> subdivision code => name, empty when free-text. */
    public static function subdivisions(string $code): array
    {
        return self::SUBDIVISIONS[strtoupper($code)] ?? [];
    }

    public static function hasSubdivisions(string $code): bool
    {
        return isset(self::SUBDIVISIONS[strtoupper($code)]);
    }

    /**
     * Resolve a subdivision name from its code, tolerating a name being passed in
     * (older orders and admin-entered addresses store free text).
     */
    public static function subdivisionName(string $country, ?string $state): ?string
    {
        if ($state === null || $state === '') {
            return null;
        }

        $subdivisions = self::subdivisions($country);

        return $subdivisions[strtoupper($state)] ?? $state;
    }

    /**
     * Country list shaped for a <select>, sorted by name.
     *
     * @return list<array{code: string, name: string, dial: string, currency: string, postal: string, states: array<string, string>}>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::LIST as $code => $meta) {
            $options[] = [
                'code' => $code,
                'name' => $meta['name'],
                'dial' => $meta['dial'],
                'currency' => $meta['currency'],
                'postal' => $meta['postal'],
                'states' => self::SUBDIVISIONS[$code] ?? [],
            ];
        }

        // Sort on a transliterated key so accented names (Åland, Côte d'Ivoire, Réunion)
        // land in their alphabetical place rather than after Z on a byte-wise compare.
        usort($options, fn (array $a, array $b) => strcmp(self::sortKey($a['name']), self::sortKey($b['name'])));

        return $options;
    }

    private static function sortKey(string $name): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);

        return strtoupper($ascii !== false ? $ascii : $name);
    }
}
