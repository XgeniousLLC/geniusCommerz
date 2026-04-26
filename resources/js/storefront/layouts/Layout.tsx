import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import CartDrawer from '../components/CartDrawer';
import Footer from '../components/Footer';
import Header from '../components/Header';
import type { SharedProps } from '../types';

interface Props {
  children: ReactNode;
}

export default function Layout({ children }: Props) {
  const { site } = usePage<SharedProps>().props;

  return (
    <>
      {site.announceBar && (
        <div className="kb-bar-announce">{site.announceBar}</div>
      )}
      <Header />
      <main>{children}</main>
      <Footer />
      <CartDrawer />
    </>
  );
}
