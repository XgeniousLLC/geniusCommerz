# Storefront Design Implementation Progress

Reference source: `KlixBd_html/` folder — 17 HTML files + shared `assets/styles.css`

## Status

| Page | HTML File | System File | Status |
|---|---|---|---|
| Home | `index.html` | `pages/Home.tsx` | ✅ Done |
| Login | `login.html` | `pages/auth/Login.tsx` | ✅ Done |
| Register | `register.html` | `pages/auth/Register.tsx` | ✅ Done |
| Forgot Password | `forgot.html` | `pages/auth/ForgotPassword.tsx` | ✅ Done |
| Shop listing | `shop.html` | `pages/Shop/Index.tsx` | ✅ Done |
| Product detail | `product.html` | `pages/Shop/Show.tsx` | ✅ Done |
| Cart | `cart.html` | `pages/Cart.tsx` | ✅ Done |
| Checkout | `checkout.html` | `pages/Checkout.tsx` | ✅ Done |
| Order confirmed | `thank-you.html` | `pages/OrderConfirmed.tsx` | ✅ Done |
| Account dashboard | `dashboard.html` | `pages/Account/Dashboard.tsx` | ✅ Done |
| Blog listing | `blog.html` | `pages/blog/Index.tsx` | ✅ Done |
| Blog post | `blog-post.html` | `pages/blog/Show.tsx` | ✅ Done |
| Wishlist | `wishlist.html` | `pages/Wishlist.tsx` | ✅ Done |
| About | `about.html` | static page | ✅ Done |
| Contact | `contact.html` | static page | ✅ Done |
| Privacy | `privacy.html` | static page | ✅ Done |
| Refund | `refund.html` | static page | ✅ Done |

## Notes

- Auth pages (login/register/forgot) render WITHOUT Layout — no header/footer, just gradient background + card
- Design tokens: `#0B1F4F` navy = `--kb-primary`, all reference `--brand-*` vars mapped to `--kb-*` in storefront.css
- Product cards: `HomeController` pattern — use `withAvg('reviews','rating')->withCount('reviews')->with(['images','variants','categories'])`
- Always run `npm run build` after changes
