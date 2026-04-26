import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import BottomNav from '../components/BottomNav';
import CartDrawer from '../components/CartDrawer';
import Footer from '../components/Footer';
import Header from '../components/Header';
import SaleAlertToast from '../components/SaleAlertToast';
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
      <main className="pb-14 md:pb-0">{children}</main>
      <Footer />
      <CartDrawer />
      <BottomNav />
      <SaleAlertToast />
    </>
  );
}
