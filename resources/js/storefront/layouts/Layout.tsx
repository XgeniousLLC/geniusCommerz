import type { ReactNode } from 'react';
import BottomNav from '../components/BottomNav';
import CartDrawer from '../components/CartDrawer';
import Footer from '../components/Footer';
import Header from '../components/Header';
import SaleAlertToast from '../components/SaleAlertToast';

interface Props {
  children: ReactNode;
}

export default function Layout({ children }: Props) {
  return (
    <>
      <Header />
      <main>{children}</main>
      <Footer />
      <CartDrawer />
      <BottomNav />
      <SaleAlertToast />
    </>
  );
}
