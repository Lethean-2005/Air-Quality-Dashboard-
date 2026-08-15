// Country name -> ISO 3166-1 alpha-2 code, for flagcdn.com flag images.
// Shared between CitySearch.vue and AdminDashboard.vue so both resolve the
// same broad set of country names instead of maintaining separate lists.
export const countryCodeMap = {
  afghanistan: 'af', albania: 'al', algeria: 'dz', andorra: 'ad', angola: 'ao',
  argentina: 'ar', armenia: 'am', australia: 'au', austria: 'at', azerbaijan: 'az',
  bahamas: 'bs', bahrain: 'bh', bangladesh: 'bd', belarus: 'by', belgium: 'be',
  belize: 'bz', benin: 'bj', bhutan: 'bt', bolivia: 'bo', bosnia: 'ba',
  botswana: 'bw', brazil: 'br', brunei: 'bn', bulgaria: 'bg', cambodia: 'kh',
  cameroon: 'cm', canada: 'ca', chad: 'td', chile: 'cl', china: 'cn',
  colombia: 'co', congo: 'cg', 'costa rica': 'cr', croatia: 'hr', cuba: 'cu',
  cyprus: 'cy', czechia: 'cz', 'czech republic': 'cz', denmark: 'dk', djibouti: 'dj',
  dominica: 'dm', 'dominican republic': 'do', ecuador: 'ec', egypt: 'eg', 'el salvador': 'sv',
  estonia: 'ee', ethiopia: 'et', fiji: 'fj', finland: 'fi', france: 'fr',
  gabon: 'ga', gambia: 'gm', georgia: 'ge', germany: 'de', ghana: 'gh',
  greece: 'gr', greenland: 'gl', guatemala: 'gt', guinea: 'gn', guyana: 'gy',
  haiti: 'ht', honduras: 'hn', 'hong kong': 'hk', hungary: 'hu', iceland: 'is',
  india: 'in', indonesia: 'id', iran: 'ir', iraq: 'iq', ireland: 'ie',
  israel: 'il', italy: 'it', jamaica: 'jm', japan: 'jp', jordan: 'jo',
  kazakhstan: 'kz', kenya: 'ke', kuwait: 'kw', kyrgyzstan: 'kg', laos: 'la',
  latvia: 'lv', lebanon: 'lb', lesotho: 'ls', liberia: 'lr', libya: 'ly',
  liechtenstein: 'li', lithuania: 'lt', luxembourg: 'lu', macau: 'mo', madagascar: 'mg',
  malawi: 'mw', malaysia: 'my', maldives: 'mv', mali: 'ml', malta: 'mt',
  mauritania: 'mr', mauritius: 'mu', mexico: 'mx', moldova: 'md', monaco: 'mc',
  mongolia: 'mn', montenegro: 'me', morocco: 'ma', mozambique: 'mz', myanmar: 'mm',
  namibia: 'na', nepal: 'np', netherlands: 'nl', 'new zealand': 'nz', nicaragua: 'ni',
  niger: 'ne', nigeria: 'ng', 'north korea': 'kp', 'north macedonia': 'mk', norway: 'no',
  oman: 'om', pakistan: 'pk', palestine: 'ps', panama: 'pa', 'papua new guinea': 'pg',
  paraguay: 'py', peru: 'pe', philippines: 'ph', poland: 'pl', portugal: 'pt',
  qatar: 'qa', romania: 'ro', russia: 'ru', rwanda: 'rw', 'saudi arabia': 'sa',
  senegal: 'sn', serbia: 'rs', 'sierra leone': 'sl', singapore: 'sg', slovakia: 'sk',
  slovenia: 'si', somalia: 'so', 'south africa': 'za', 'south korea': 'kr', 'south sudan': 'ss',
  spain: 'es', 'sri lanka': 'lk', sudan: 'sd', suriname: 'sr', sweden: 'se',
  switzerland: 'ch', syria: 'sy', taiwan: 'tw', tajikistan: 'tj', tanzania: 'tz',
  thailand: 'th', togo: 'tg', 'trinidad and tobago': 'tt', tunisia: 'tn', turkey: 'tr',
  turkmenistan: 'tm', uganda: 'ug', ukraine: 'ua', 'united arab emirates': 'ae', uae: 'ae',
  'united kingdom': 'gb', uk: 'gb', 'united states': 'us', 'united states of america': 'us', usa: 'us',
  uruguay: 'uy', uzbekistan: 'uz', vanuatu: 'vu', 'vatican city': 'va', venezuela: 've',
  vietnam: 'vn', yemen: 'ye', zambia: 'zm', zimbabwe: 'zw',
}

// Builds a flagcdn.com URL from a free-text country name; falls back to a blank/unknown flag.
export function getFlagUrl(country) {
  const key = (country || '').toLowerCase().trim()
  const code = countryCodeMap[key]
  if (!code) return null
  return `https://flagcdn.com/w40/${code}.png`
}
