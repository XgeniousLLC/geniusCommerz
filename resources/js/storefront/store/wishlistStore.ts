import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface WishlistItem {
  id: number;
  slug: string;
  name: string;
  price: number;
  compare_at_price: number | null;
  image_url: string | null;
  category: string | null;
}

interface WishlistStore {
  items: WishlistItem[];
  add: (item: WishlistItem) => void;
  remove: (id: number) => void;
  toggle: (item: WishlistItem) => void;
  has: (id: number) => boolean;
  clear: () => void;
}

export const useWishlistStore = create<WishlistStore>()(
  persist(
    (set, get) => ({
      items: [],
      add: (item) => set(s => ({ items: s.items.some(i => i.id === item.id) ? s.items : [...s.items, item] })),
      remove: (id) => set(s => ({ items: s.items.filter(i => i.id !== id) })),
      toggle: (item) => {
        if (get().has(item.id)) get().remove(item.id);
        else get().add(item);
      },
      has: (id) => get().items.some(i => i.id === id),
      clear: () => set({ items: [] }),
    }),
    { name: 'kb_wishlist' }
  )
);
