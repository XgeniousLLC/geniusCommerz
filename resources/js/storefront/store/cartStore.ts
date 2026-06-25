import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface CartItem {
  key: string;
  product_id: number;
  variant_id: number | null;
  name: string;
  variant_label: string | null;
  price: number;
  image_url: string | null;
  quantity: number;
  slug: string;
  shipping_included: boolean;
}

export interface CouponState {
  code: string;
  discount: number;
  type: 'fixed' | 'percent';
  message: string;
}

interface CartStore {
  items: CartItem[];
  coupon: CouponState | null;
  isOpen: boolean;

  openCart: () => void;
  closeCart: () => void;
  addItem: (item: Omit<CartItem, 'key' | 'quantity'>, qty?: number) => void;
  removeItem: (key: string) => void;
  updateQty: (key: string, qty: number) => void;
  clearCart: () => void;
  applyCoupon: (code: string) => Promise<string | null>;
  removeCoupon: () => void;
}

export const useCartStore = create<CartStore>()(
  persist(
    (set, get) => ({
      items:  [],
      coupon: null,
      isOpen: false,

      openCart:  () => set({ isOpen: true }),
      closeCart: () => set({ isOpen: false }),

      addItem: (item, qty = 1) => {
        const key = item.variant_id
          ? `${item.product_id}-${item.variant_id}`
          : `${item.product_id}`;
        set(state => {
          const existing = state.items.find(i => i.key === key);
          const items = existing
            ? state.items.map(i => i.key === key ? { ...i, quantity: i.quantity + qty } : i)
            : [...state.items, { ...item, key, quantity: qty }];
          return { items, isOpen: true };
        });

        // Meta CAPI — AddToCart (fire and forget)
        fetch('/capi/event', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
          },
          body: JSON.stringify({
            event_name:   'AddToCart',
            currency:     'BDT',
            value:        item.price * qty,
            content_ids:  [String(item.product_id)],
            content_name: item.name,
            content_type: 'product',
            num_items:    qty,
            contents:     [{ id: String(item.product_id), quantity: qty, item_price: item.price }],
            source_url:   window.location.href,
          }),
        }).catch(() => {});
      },

      removeItem: (key) => set(state => ({ items: state.items.filter(i => i.key !== key) })),

      updateQty: (key, qty) => {
        if (qty <= 0) { get().removeItem(key); return; }
        set(state => ({ items: state.items.map(i => i.key === key ? { ...i, quantity: qty } : i) }));
      },

      clearCart: () => set({ items: [], coupon: null }),

      applyCoupon: async (code) => {
        const subtotal = get().items.reduce((s, i) => s + i.price * i.quantity, 0);
        try {
          const res = await fetch('/coupon/validate', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ code, subtotal }),
          });
          const data = await res.json();
          if (!res.ok) return data.message ?? 'Invalid coupon.';
          set({ coupon: { code: data.code, discount: data.discount, type: data.type, message: data.message } });
          return null;
        } catch {
          return 'Could not validate coupon.';
        }
      },

      removeCoupon: () => set({ coupon: null }),
    }),
    {
      name: 'kb_cart',
      partialize: (state) => ({ items: state.items, coupon: state.coupon }),
    }
  )
);

export function useCartDerived() {
  const items  = useCartStore(s => s.items);
  const coupon = useCartStore(s => s.coupon);
  const subtotal = items.reduce((s, i) => s + i.price * i.quantity, 0);
  const discount = coupon
    ? coupon.type === 'percent'
      ? Math.min(subtotal * coupon.discount / 100, subtotal)
      : Math.min(coupon.discount, subtotal)
    : 0;
  return {
    subtotal,
    discount,
    total: subtotal - discount,
    count: items.reduce((s, i) => s + i.quantity, 0),
  };
}
