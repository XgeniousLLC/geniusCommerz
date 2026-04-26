export interface SiteSettings {
  name: string;
  tagline: string;
  logoUrl: string | null;
  faviconUrl: string | null;
  announceBar: string;
  phone: string;
  email: string;
  address: string;
  copyright: string;
  productWhatsappEnabled: boolean;
  productWhatsappNumber: string;
  productWhatsappMessage: string;
  productCallEnabled: boolean;
  productCallNumber: string;
  globalReturnPolicy: string;
  shippingCost: number;
  freeShippingAbove: number;
  cartGoals: CartGoal[];
  mainNav: NavItem[];
  visitorCounterEnabled: boolean;
  visitorCounterMin: number;
  visitorCounterMax: number;
}

export interface NavItem {
  label: string;
  url: string;
  target: string;
  children: Array<{ label: string; url: string; target: string }>;
}

export interface CartGoal {
  amount: number;
  reward: string;    // 'free_shipping' | 'discount_pct' | 'discount_fixed'
  value?: number;    // discount amount/pct when reward is not free_shipping
  label?: string;    // custom label override
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

export interface BlogCategory {
  id: number;
  name: string;
  slug: string;
  blogs_count?: number;
}

export interface BlogPost {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  content: string | null;
  cover_url: string | null;
  category_name: string | null;
  author_display_name: string;
  read_time: string;
  published_at: string | null;
  enable_toc: boolean;
  faqs: Array<{ question: string; answer: string }> | null;
  blog_category_id: number | null;
  blogCategory: BlogCategory | null;
  metaInformation?: MetaInformation | null;
}

export interface MetaInformation {
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  robots: string | null;
  og_title: string | null;
  og_description: string | null;
  og_image: string | null;
  canonical_url: string | null;
}

export interface Comment {
  id: number;
  name: string;
  body: string;
  created_at: string;
  replies: Comment[];
}

export interface ShopCategory {
  id: number;
  name: string;
  slug: string;
}

export interface ProductCard {
  id: number;
  name: string;
  slug: string;
  price: number;
  compare_at_price: number | null;
  has_variants: boolean;
  image_url: string | null;
  is_featured: boolean;
  in_stock: boolean;
}

export interface ProductVariantOption {
  attribute: string;
  value: string;
}

export interface ProductReview {
  id: number;
  rating: number;
  title: string | null;
  body: string | null;
  user_name: string;
  created_at: string;
}

export interface ProductVariant {
  id: number;
  price: number;
  compare_at_price: number | null;
  sku: string | null;
  stock_qty: number | null;
  in_stock: boolean;
  label: string;
  options: ProductVariantOption[];
}

export interface ProductFull {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  description: string | null;
  price: number;
  compare_at_price: number | null;
  sku: string | null;
  has_variants: boolean;
  in_stock: boolean;
  brand: { id: number; name: string; slug: string } | null;
  categories: Array<{ id: number; name: string; slug: string }>;
  images: Array<{ id: number; url: string; thumb: string }>;
  stock_qty: number | null;
  shipping_included: boolean;
  variants: ProductVariant[];
  warranty: string | null;
  reviews: ProductReview[];
  reviews_count: number;
  reviews_avg: number | null;
  return_policy: string | null;
  specifications: Array<{ key: string; value: string }>;
  meta: { meta_title: string | null; meta_description: string | null } | null;
  preorder_enabled: boolean;
  preorder_message: string | null;
  preorder_expected_date: string | null;
}

export interface Brand {
  id: number;
  name: string;
  slug: string;
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: PaginationLink[];
  from: number | null;
  to: number | null;
}

export interface OrderItem {
  id: number;
  product_name: string;
  quantity: number;
  unit_price: number;
  total_price: number;
}

export interface OrderActivity {
  id: number;
  description: string;
  created_at: string;
}

export interface Order {
  id: number;
  order_number: string;
  status: string;
  payment_status: string;
  customer_name: string;
  customer_phone: string;
  customer_email: string | null;
  shipping_address: string;
  subtotal: number;
  shipping_charge: number;
  discount: number;
  total: number;
  tracking_number: string | null;
  notes: string | null;
  created_at: string;
  items: OrderItem[];
  activities: OrderActivity[];
}

export interface StaticPage {
  id: number;
  title: string;
  slug: string;
  content: string;
  metaInformation?: MetaInformation | null;
}

export interface SharedProps {
  site: SiteSettings;
  auth: { user: AuthUser | null };
  flash: { success: string | null; comment_success: string | null; error: string | null };
  errors: Record<string, string>;
  locale: string;
  strings: Record<string, string>;
  languages: Array<{ name: string; code: string }>;
}
