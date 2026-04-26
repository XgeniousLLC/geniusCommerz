import { usePage } from '@inertiajs/react';
import type { SharedProps } from '../types';

interface Language {
  name: string;
  code: string;
}

interface Props {
  languages: Language[];
}

export default function LocaleSwitcher({ languages }: Props) {
  const { locale } = usePage<SharedProps>().props;

  if (!languages || languages.length <= 1) return null;

  function switchLocale(code: string) {
    window.location.href = `/locale/${code}`;
  }

  return (
    <div className="relative group inline-block">
      <button className="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 px-2 py-1 rounded">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
        <span className="uppercase">{locale}</span>
        <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
        </svg>
      </button>
      <div className="absolute right-0 mt-1 w-36 bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden group-hover:block">
        {languages.map((lang) => (
          <button
            key={lang.code}
            onClick={() => switchLocale(lang.code)}
            className={`block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors
              ${lang.code === locale ? 'font-semibold text-blue-600' : 'text-gray-700'}`}
          >
            {lang.name}
          </button>
        ))}
      </div>
    </div>
  );
}
