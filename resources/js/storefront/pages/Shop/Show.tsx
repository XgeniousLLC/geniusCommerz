import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCartStore } from '../../store/cartStore';
import { useWishlistStore } from '../../store/wishlistStore';
import Layout from '../../layouts/Layout';
import type { ProductCard, ProductFull, ProductVariant, SharedProps } from '../../types';

interface Props {
  product: ProductFull;
  related: ProductCard[];
}

function RelatedCard({ product }: { product: ProductCard }) {
  const discount = product.compare_at_price
    ? Math.round((1 - product.price / product.compare_at_price) * 100)
    : null;
  return (
    <Link href={`/shop/${product.slug}`} className="group block">
      <div className="kb-card overflow-hidden hover:shadow-md transition-shadow">
        <div className="relative aspect-square bg-gray-100 overflow-hidden">
          {product.image_url
            ? <img src={product.image_url} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            : <div className="w-full h-full flex items-center justify-center"><svg className="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
          }
          {discount && <span className="absolute top-2 left-2 kb-chip text-xs font-bold" style={{ background: '#ef4444', color: '#fff' }}>-{discount}%</span>}
        </div>
        <div className="p-3">
          <p className="text-sm font-medium text-gray-800 line-clamp-2">{product.name}</p>
          <div className="mt-1.5 flex items-center gap-2">
            <span className="font-bold text-gray-900">৳{product.price.toLocaleString()}</span>
            {product.compare_at_price && <span className="text-xs text-gray-400 line-through">৳{product.compare_at_price.toLocaleString()}</span>}
          </div>
        </div>
      </div>
    </Link>
  );
}

export default function ShopShow({ product, related }: Props) {
  const { site } = usePage<SharedProps>().props;
  const addItem = useCartStore(s => s.addItem);
  const { toggle: toggleWishlist, has: inWishlist } = useWishlistStore();
  const wishlisted = inWishlist(product.id);
  const [activeImage, setActiveImage] = useState(0);
  const [selectedVariant, setSelectedVariant] = useState<ProductVariant | null>(
    product.has_variants && product.variants.length > 0
      ? (product.variants.find(v => v.in_stock) ?? product.variants[0])
      : null
  );
  const [qty, setQty] = useState(1);
  const [added, setAdded] = useState(false);

  const cartItem = {
    product_id:        product.id,
    variant_id:        selectedVariant?.id ?? null,
    name:              product.name,
    variant_label:     selectedVariant?.label ?? null,
    price:             selectedVariant ? selectedVariant.price : product.price,
    image_url:         product.images[0]?.thumb ?? null,
    slug:              product.slug,
    shipping_included: product.shipping_included,
  };

  const handleAddToCart = () => {
    addItem(cartItem, qty);
    setAdded(true);
    setTimeout(() => setAdded(false), 2000);
  };

  const handleBuyNow = () => {
    addItem(cartItem, qty);
    window.location.href = '/checkout';
  };

  const whatsappUrl = site.productWhatsappNumber
    ? `https://wa.me/${site.productWhatsappNumber}?text=${encodeURIComponent(
        (site.productWhatsappMessage || 'Hi, I want to order: {product}').replace('{product}', product.name)
      )}`
    : null;

  const displayPrice = selectedVariant ? selectedVariant.price : product.price;
  const comparePrice = selectedVariant ? selectedVariant.compare_at_price : product.compare_at_price;
  const discount = comparePrice ? Math.round((1 - displayPrice / comparePrice) * 100) : null;

  const isInStock = selectedVariant ? selectedVariant.in_stock : product.in_stock;
  const stockQty  = selectedVariant ? selectedVariant.stock_qty : product.stock_qty;
  const isLowStock = stockQty !== null && stockQty > 0 && stockQty <= 5;

  const groupedOptions = product.variants.length > 0
    ? product.variants[0].options.map(o => o.attribute)
    : [];

  const getValuesForAttribute = (attr: string) =>
    [...new Set(product.variants.map(v => v.options.find(o => o.attribute === attr)?.value).filter(Boolean))] as string[];

  const selectedOptions: Record<string, string> = selectedVariant
    ? Object.fromEntries(selectedVariant.options.map(o => [o.attribute, o.value]))
    : {};

  const handleOptionSelect = (attr: string, val: string) => {
    const newOpts = { ...selectedOptions, [attr]: val };
    // Try exact match first
    const exact = product.variants.find(v =>
      v.options.every(o => newOpts[o.attribute] === o.value)
    );
    if (exact) { setSelectedVariant(exact); return; }
    // Fallback: match only the changed axis — pick first in-stock variant with that value, or any
    const partial = product.variants.find(v => v.in_stock && v.options.some(o => o.attribute === attr && o.value === val))
      ?? product.variants.find(v => v.options.some(o => o.attribute === attr && o.value === val));
    if (partial) setSelectedVariant(partial);
  };

  const pageTitle = product.meta?.meta_title ?? product.name;
  const pageDesc = product.meta?.meta_description ?? product.short_description ?? '';

  return (
    <Layout>
      <Head>
        <title>{pageTitle}</title>
        {pageDesc && <meta name="description" content={pageDesc} />}
      </Head>

      <div className="max-w-7xl mx-auto px-4 lg:px-6 py-8">

        {/* Breadcrumb */}
        <nav className="flex items-center gap-2 text-sm text-gray-400 mb-6">
          <Link href="/" className="hover:text-gray-600">Home</Link>
          <span>/</span>
          <Link href="/shop" className="hover:text-gray-600">Shop</Link>
          {product.categories[0] && <>
            <span>/</span>
            <Link href={`/shop/c/${product.categories[0].slug}`} className="hover:text-gray-600">{product.categories[0].name}</Link>
          </>}
          <span>/</span>
          <span className="text-gray-700 font-medium truncate max-w-[200px]">{product.name}</span>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">

          {/* Images */}
          <div className="space-y-3">
            <div className="aspect-square rounded-xl overflow-hidden bg-gray-100">
              {product.images.length > 0
                ? <img src={product.images[activeImage]?.url} alt={product.name} className="w-full h-full object-contain" />
                : <div className="w-full h-full flex items-center justify-center"><svg className="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
              }
            </div>
            {product.images.length > 1 && (
              <div className="flex gap-2 overflow-x-auto pb-1">
                {product.images.map((img, i) => (
                  <button key={img.id} onClick={() => setActiveImage(i)}
                    className={`shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-colors ${i === activeImage ? 'border-blue-500' : 'border-transparent hover:border-gray-300'}`}>
                    <img src={img.thumb} alt="" className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Info */}
          <div className="space-y-5">
            {product.brand && (
              <Link href={`/shop/b/${product.brand.slug}`} className="text-sm text-blue-600 font-medium hover:underline">
                {product.brand.name}
              </Link>
            )}
            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 leading-tight">{product.name}</h1>

            {product.categories.length > 0 && (
              <div className="flex flex-wrap gap-1.5">
                {product.categories.map(c => (
                  <Link key={c.id} href={`/shop/c/${c.slug}`} className="kb-chip text-xs hover:bg-blue-50 hover:text-blue-700 transition-colors">
                    {c.name}
                  </Link>
                ))}
              </div>
            )}

            {/* Price */}
            <div className="flex items-baseline gap-3">
              <span className="text-3xl font-extrabold text-gray-900">৳{displayPrice.toLocaleString()}</span>
              {comparePrice && (
                <span className="text-lg text-gray-400 line-through">৳{comparePrice.toLocaleString()}</span>
              )}
              {discount && (
                <span className="kb-chip text-sm font-bold" style={{ background: '#fef2f2', color: '#ef4444' }}>-{discount}%</span>
              )}
            </div>

            {product.short_description && (
              <p className="text-gray-600 leading-relaxed">{product.short_description}</p>
            )}

            {/* Variants */}
            {product.has_variants && groupedOptions.map(attr => (
              <div key={attr}>
                <p className="text-sm font-semibold text-gray-700 mb-2">
                  {attr}:{' '}
                  <span className="font-normal text-gray-500">{selectedOptions[attr]}</span>
                </p>
                <div className="flex flex-wrap gap-2">
                  {getValuesForAttribute(attr).map(val => {
                    // Find variant matching this value + all other currently selected axes
                    const combo = product.variants.find(v =>
                      Object.entries({ ...selectedOptions, [attr]: val }).every(([a, v2]) =>
                        v.options.some(o => o.attribute === a && o.value === v2)
                      )
                    );
                    const unavailable = !combo;
                    const outOfStock  = combo ? !combo.in_stock : false;
                    const isSelected  = selectedOptions[attr] === val;

                    return (
                      <button key={val}
                        onClick={() => !unavailable && !outOfStock && handleOptionSelect(attr, val)}
                        disabled={unavailable || outOfStock}
                        title={unavailable ? 'Not available in this configuration' : outOfStock ? 'Out of stock' : undefined}
                        className={`px-4 py-1.5 rounded-lg border text-sm font-medium transition-colors ${
                          unavailable
                            ? 'border-gray-100 text-gray-300 bg-gray-50 cursor-not-allowed'
                            : outOfStock
                              ? 'border-gray-200 text-gray-300 bg-gray-50 cursor-not-allowed'
                              : isSelected
                                ? 'border-blue-600 bg-blue-50 text-blue-700'
                                : 'border-gray-200 text-gray-700 hover:border-gray-400'
                        }`}>
                        <span className={unavailable || outOfStock ? 'line-through' : ''}>{val}</span>
                      </button>
                    );
                  })}
                </div>
              </div>
            ))}

            {/* How-to guide */}
            {product.has_variants && (
              <div className="rounded-lg bg-blue-50 border border-blue-100 px-4 py-3">
                <p className="text-xs font-semibold text-blue-700 mb-2 flex items-center gap-1">
                  <svg className="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  How to order
                </p>
                <ol className="space-y-1.5 text-xs text-blue-600">
                  <li className="flex items-start gap-2">
                    <span className="shrink-0 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-[10px] mt-0.5">1</span>
                    <span>Pick your options above (e.g. Size, Color, RAM). The price updates automatically for each combination.</span>
                  </li>
                  <li className="flex items-start gap-2">
                    <span className="shrink-0 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-[10px] mt-0.5">2</span>
                    <span>
                      <span className="line-through text-gray-400">Greyed options</span>
                      {' '}are unavailable or out of stock for your current selection. The price above updates as you pick each option.
                    </span>
                  </li>
                  <li className="flex items-start gap-2">
                    <span className="shrink-0 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-[10px] mt-0.5">3</span>
                    <span>Set the quantity, then tap <strong>Add to Cart</strong> to keep shopping or <strong>Buy Now</strong> to go straight to checkout.</span>
                  </li>
                </ol>
              </div>
            )}

            {/* SKU + Stock */}
            <div className="flex items-center gap-4 text-xs text-gray-400">
              {(selectedVariant?.sku ?? product.sku) && (
                <span>SKU: {selectedVariant?.sku ?? product.sku}</span>
              )}
              {isInStock
                ? isLowStock
                  ? <span className="flex items-center gap-1 text-orange-500 font-medium"><span className="w-2 h-2 rounded-full bg-orange-400 inline-block" />Only {stockQty} left!</span>
                  : <span className="flex items-center gap-1 text-green-600 font-medium"><span className="w-2 h-2 rounded-full bg-green-500 inline-block" />In Stock</span>
                : <span className="flex items-center gap-1 text-red-500 font-medium"><span className="w-2 h-2 rounded-full bg-red-400 inline-block" />Out of Stock</span>
              }
            </div>

            {/* Qty + Add to cart + Buy Now */}
            <div className="space-y-2 pt-1">
              <div className="flex items-center gap-3">
                <div className="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                  <button onClick={() => setQty(q => Math.max(1, q - 1))} className="w-10 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 text-lg font-medium">−</button>
                  <span className="w-12 text-center text-sm font-semibold">{qty}</span>
                  <button onClick={() => setQty(q => q + 1)} className="w-10 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 text-lg font-medium">+</button>
                </div>
                <button
                  onClick={() => toggleWishlist({ id: product.id, slug: product.slug, name: product.name, price: product.price, compare_at_price: product.compare_at_price, image_url: product.images[0]?.url ?? null, category: null })}
                  className={`kb-btn kb-btn-lg px-3 flex items-center justify-center transition-colors ${wishlisted ? 'text-rose-500 bg-rose-50' : 'text-gray-400 hover:text-rose-400'}`}
                  title={wishlisted ? 'Remove from wishlist' : 'Add to wishlist'}
                >
                  <svg className="w-5 h-5" fill={wishlisted ? 'currentColor' : 'none'} stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                </button>
                <button
                  onClick={handleAddToCart}
                  disabled={!isInStock}
                  className={`flex-1 kb-btn kb-btn-lg flex items-center justify-center gap-2 transition-colors ${added ? 'bg-green-600 text-white' : 'kb-btn-primary'} disabled:opacity-50 disabled:cursor-not-allowed`}
                >
                  {added ? (
                    <><svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"/></svg>Added!</>
                  ) : (
                    <><svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</>
                  )}
                </button>
              </div>

              <button
                onClick={handleBuyNow}
                disabled={!isInStock}
                className="w-full kb-btn kb-btn-lg flex items-center justify-center gap-2 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                style={{ background: '#0f172a', color: '#fff' }}
              >
                Buy Now
              </button>

              {/* Warranty + Return Policy + Free Shipping */}
              {(product.shipping_included || product.warranty || product.return_policy || site.globalReturnPolicy) && (
                <div className="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden text-sm">
                  {product.shipping_included && (
                    <div className="flex items-center gap-3 px-4 py-3">
                      <svg className="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 13h12l1-13M10 12h4"/>
                      </svg>
                      <span className="font-medium text-green-700">Free Shipping</span>
                      <span className="text-gray-500 text-sm">— Delivery charge included</span>
                    </div>
                  )}
                  {product.warranty && (
                    <div className="flex items-start gap-3 px-4 py-3">
                      <svg className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                      </svg>
                      <div><span className="font-medium text-gray-800">Warranty</span><span className="text-gray-500 ml-2">{product.warranty}</span></div>
                    </div>
                  )}
                  {(product.return_policy || site.globalReturnPolicy) && (
                    <div className="flex items-start gap-3 px-4 py-3">
                      <svg className="w-4 h-4 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                      </svg>
                      <div>
                        <span className="font-medium text-gray-800">Returns</span>
                        <p className="text-gray-500 mt-0.5 whitespace-pre-line">{product.return_policy || site.globalReturnPolicy}</p>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* WhatsApp + Call */}
              {(site.productWhatsappEnabled || site.productCallEnabled) && (
                <div className="flex gap-2 pt-1">
                  {site.productWhatsappEnabled && whatsappUrl && (
                    <a href={whatsappUrl} target="_blank" rel="noopener noreferrer"
                      className="flex-1 kb-btn kb-btn-lg flex items-center justify-center gap-2 font-medium"
                      style={{ background: '#25D366', color: '#fff' }}>
                      <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                      </svg>
                      Order on WhatsApp
                    </a>
                  )}
                  {site.productCallEnabled && site.productCallNumber && (
                    <a href={`tel:${site.productCallNumber}`}
                      className="flex-1 kb-btn kb-btn-lg flex items-center justify-center gap-2 font-medium"
                      style={{ background: '#2563eb', color: '#fff' }}>
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                      </svg>
                      Call to Order
                    </a>
                  )}
                </div>
              )}
            </div>

            {/* Trust badges */}
            <div className="grid grid-cols-3 gap-3 pt-2 border-t border-gray-100">
              {[
                { icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', label: 'Free Delivery' },
                { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', label: 'Secure Pay' },
                { icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', label: 'Easy Return' },
              ].map(b => (
                <div key={b.label} className="flex flex-col items-center gap-1 text-center p-2 rounded-lg bg-gray-50">
                  <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={b.icon}/></svg>
                  <span className="text-xs text-gray-600 font-medium">{b.label}</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Description */}
        {product.description && (
          <div className="mt-12">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Product Description</h2>
            <div className="kb-prose" dangerouslySetInnerHTML={{ __html: product.description }} />
          </div>
        )}

        {/* Specifications */}
        {product.specifications && product.specifications.length > 0 && (
          <div className="mt-10">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Specifications</h2>
            <div className="rounded-xl border border-gray-200 overflow-hidden">
              <table className="w-full text-sm">
                <tbody>
                  {product.specifications.map((spec, i) => (
                    <tr key={i} className={i % 2 === 0 ? 'bg-gray-50' : 'bg-white'}>
                      <td className="px-4 py-3 font-medium text-gray-700 w-40 border-r border-gray-200">{spec.key}</td>
                      <td className="px-4 py-3 text-gray-600">{spec.value}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Reviews */}
        <div className="mt-10">
          <h2 className="text-xl font-bold text-gray-900 mb-4">
            Customer Reviews
            {product.reviews_count > 0 && (
              <span className="text-base font-normal text-gray-500 ml-2">({product.reviews_count})</span>
            )}
          </h2>

          {product.reviews_count > 0 && product.reviews_avg && (
            <div className="flex items-center gap-3 mb-6">
              <span className="text-4xl font-bold text-gray-900">{Number(product.reviews_avg).toFixed(1)}</span>
              <div>
                <div className="text-yellow-400 text-xl">
                  {'★'.repeat(Math.round(product.reviews_avg))}{'☆'.repeat(5 - Math.round(product.reviews_avg))}
                </div>
                <p className="text-sm text-gray-500">{product.reviews_count} review{product.reviews_count !== 1 ? 's' : ''}</p>
              </div>
            </div>
          )}

          {product.reviews.length === 0 ? (
            <p className="text-sm text-gray-500">No reviews yet. Be the first to review this product.</p>
          ) : (
            <div className="space-y-4">
              {product.reviews.map(r => (
                <div key={r.id} className="border border-gray-200 rounded-xl p-4">
                  <div className="flex items-start justify-between gap-2">
                    <div>
                      <p className="text-sm font-semibold text-gray-900">{r.user_name}</p>
                      <div className="text-yellow-400 text-sm mt-0.5">
                        {'★'.repeat(r.rating)}{'☆'.repeat(5 - r.rating)}
                      </div>
                    </div>
                    <span className="text-xs text-gray-400 shrink-0">{r.created_at}</span>
                  </div>
                  {r.title && <p className="text-sm font-medium text-gray-800 mt-2">{r.title}</p>}
                  {r.body && <p className="text-sm text-gray-600 mt-1">{r.body}</p>}
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Related */}
        {related.length > 0 && (
          <div className="mt-14">
            <h2 className="text-xl font-bold text-gray-900 mb-5">Related Products</h2>
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
              {related.map(p => <RelatedCard key={p.id} product={p} />)}
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
}
