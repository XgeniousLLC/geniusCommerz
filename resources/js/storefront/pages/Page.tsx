import { Head } from '@inertiajs/react';
import Layout from '../layouts/Layout';
import type { StaticPage } from '../types';

interface Props {
  page: StaticPage;
}

export default function Page({ page }: Props) {
  const meta = page.metaInformation;

  return (
    <Layout>
      <Head>
        <title>{meta?.meta_title || page.title}</title>
        {meta?.meta_description && <meta name="description" content={meta.meta_description} />}
      </Head>
      <div className="max-w-3xl mx-auto px-4 py-10 lg:py-14">
        <h1 className="text-3xl md:text-4xl font-extrabold mb-8" style={{ color: 'var(--kb-ink)' }}>{page.title}</h1>
        <div className="kb-prose" dangerouslySetInnerHTML={{ __html: page.content }} />
      </div>
    </Layout>
  );
}
