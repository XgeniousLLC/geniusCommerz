import { Head, Link } from '@inertiajs/react';
import { useWishlistStore } from '../store/wishlistStore';
import { useCartStore } from '../store/cartStore';
import Layout from '../layouts/Layout';

export default function Wishlist() {
  const { items, remove, clear } = useWishlistStore();
  const addToCart = useCartStore(s => s.add);

  const moveToCart = (item: typeof items[0]) => {
    addToCart({
      id: item.id,
      slug: item.slug,
      name: item.name,
      price: item.price,
      quantity: 1,
      image_url: item.image_url,
      variant_id: null,
      variant_label: null,
      sku: null,
      shipping_included: false,
    });
    remove(item.id);
  };

  const discount = (price: number, compare: number | null) =>
    compare ? Math.round((1 - price / compare) * 100) : null;

  return (
    <Layout>
      <Head title="Wishlist" />

      <div className="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-10">
        <div className="flex items-center justify-between mb-5">
          <h1 className="text-2xl font-bold text-gray-900">
            Wishlist <span className="text-gray-400 font-medium text-lg">({items.length})</span>
          </h1>
          {items.length > 0 && (
            <button onClick={() => clear()} className="text-sm text-rose-600 hover:underline">Clear all</button>
          )}
        </div>

        {items.length === 0 ? (
          <div className="kb-card p-12 text-center">
            <svg className="w-14 h-14 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <p className="text-gray-500 mb-4">Your wishlist is empty.</p>
            <Link href="/shop" className="kb-btn kb-btn-primary">Browse Products</Link>
          </div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {items.map(item => {
              const pct = discount(item.price, item.compare_at_price);
              return (
                <div key={item.id} className="kb-card overflow-hidden group">
                  <Link href={`/shop/${item.slug}`} className="block">
                    <div className="aspect-square bg-slate-100 relative overflow-hidden">
                      {item.image_url
                        ? <img src={item.image_url} alt={item.name} className="w-full h-full object-cover group-hover:scale-105 transition" />
                        : <div className="w-full h-full" />
                      }
                      {pct && (
                        <span className="absolute top-2 left-2 bg-rose-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                          -{pct}%
                        </span>
                      )}
                      <button
                        onClick={e => { e.preventDefault(); remove(item.id); }}
                        className="absolute top-2 right-2 w-8 h-8 grid place-items-center bg-white/90 rounded-full text-rose-500 hover:bg-rose-50"
                      >
                        <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/></svg>
                      </button>
                    </div>
                  </Link>
                  <div className="p-3">
                    {item.category && <div className="text-xs text-gray-500">{item.category}</div>}
                    <div className="font-semibold mt-0.5 line-clamp-1 text-sm text-gray-900">{item.name}</div>
                    <div className="mt-2 flex items-center gap-2">
                      <span className="font-bold text-gray-900">৳{item.price.toLocaleString()}</span>
                      {item.compare_at_price && (
                        <span className="text-xs text-gray-400 line-through">৳{item.compare_at_price.toLocaleString()}</span>
                      )}
                    </div>
                    <button
                      onClick={() => moveToCart(item)}
                      className="kb-btn kb-btn-primary w-full mt-2 text-sm flex items-center justify-center gap-1.5"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                      </svg>
                      Move to Cart
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </Layout>
  );
}
