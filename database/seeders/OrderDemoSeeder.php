<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Rafiqul Islam',    'email' => 'rafiqul@example.com',    'phone' => '01711111111'],
            ['name' => 'Sumaiya Akter',    'email' => 'sumaiya@example.com',    'phone' => '01722222222'],
            ['name' => 'Karim Hossain',    'email' => 'karim@example.com',      'phone' => '01733333333'],
            ['name' => 'Nasrin Begum',     'email' => 'nasrin@example.com',     'phone' => '01744444444'],
            ['name' => 'Jahangir Alam',    'email' => 'jahangir@example.com',   'phone' => '01755555555'],
            ['name' => 'Roksana Khatun',   'email' => 'roksana@example.com',    'phone' => '01766666666'],
            ['name' => 'Sabbir Ahmed',     'email' => 'sabbir@example.com',     'phone' => '01777777777'],
            ['name' => 'Fatema Khatun',    'email' => 'fatema@example.com',     'phone' => '01788888888'],
        ];

        $users = collect($customers)->map(fn($c) => User::firstOrCreate(
            ['email' => $c['email']],
            [
                'name'      => $c['name'],
                'phone'     => $c['phone'],
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        ));

        $products = Product::inRandomOrder()->limit(20)->get();
        if ($products->isEmpty()) {
            $this->command->warn('No products found — run ShopDemoSeeder first.');
            return;
        }

        $statuses        = Order::STATUSES;
        $paymentStatuses = Order::PAYMENT_STATUSES;
        $sources         = array_keys(Order::SOURCES);
        $paymentMethods  = ['bkash', 'nagad', 'cod', 'rocket', 'card'];

        $orders = [
            ['status' => 'pending',    'payment_status' => 'unpaid',  'days_ago' => 0],
            ['status' => 'pending',    'payment_status' => 'unpaid',  'days_ago' => 1],
            ['status' => 'confirmed',  'payment_status' => 'paid',    'days_ago' => 2],
            ['status' => 'processing', 'payment_status' => 'paid',    'days_ago' => 3],
            ['status' => 'processing', 'payment_status' => 'paid',    'days_ago' => 3],
            ['status' => 'shipped',    'payment_status' => 'paid',    'days_ago' => 5],
            ['status' => 'shipped',    'payment_status' => 'paid',    'days_ago' => 6],
            ['status' => 'delivered',  'payment_status' => 'paid',    'days_ago' => 7],
            ['status' => 'delivered',  'payment_status' => 'paid',    'days_ago' => 8],
            ['status' => 'delivered',  'payment_status' => 'paid',    'days_ago' => 10],
            ['status' => 'pending',    'payment_status' => 'unpaid',  'days_ago' => 1],
            ['status' => 'cancelled',  'payment_status' => 'refunded','days_ago' => 12],
            ['status' => 'delivered',  'payment_status' => 'paid',    'days_ago' => 14],
            ['status' => 'confirmed',  'payment_status' => 'paid',    'days_ago' => 15],
            ['status' => 'shipped',    'payment_status' => 'paid',    'days_ago' => 16],
        ];

        foreach ($orders as $i => $def) {
            $user     = $users[$i % $users->count()];
            $source   = $sources[$i % count($sources)];
            $method   = $paymentMethods[$i % count($paymentMethods)];
            $createdAt = now()->subDays($def['days_ago'])->subHours(rand(0, 8));

            $itemCount = rand(1, 3);
            $picks     = $products->random(min($itemCount, $products->count()));
            $subtotal  = 0;
            $itemData  = [];

            foreach ($picks as $product) {
                $qty      = rand(1, 3);
                $price    = (float) $product->price;
                $itemData[] = [
                    'product'    => $product,
                    'qty'        => $qty,
                    'unit_price' => $price,
                    'total'      => $price * $qty,
                ];
                $subtotal += $price * $qty;
            }

            $shipping  = rand(0, 1) ? 60 : 0;
            $total     = $subtotal + $shipping;

            $shippingAddr = [
                'name'    => $user->name,
                'phone'   => $user->phone,
                'address' => rand(1, 100) . ' ' . collect(['Green Road','Mirpur-10','Uttara Sector-7','Dhanmondi','Gulshan-2'])->random(),
                'city'    => collect(['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna'])->random(),
                'state'   => 'Dhaka',
                'postcode'=> (string) rand(1200, 1230),
                'country' => 'Bangladesh',
            ];

            $paidAt = ($def['payment_status'] === 'paid') ? $createdAt->copy()->addMinutes(rand(5, 60)) : null;

            $serial = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $orderNumber = $createdAt->format('Y-m-d') . '-' . $serial;

            $order = Order::create([
                'order_number'    => $orderNumber,
                'source'          => $source,
                'user_id'         => $user->id,
                'customer_name'   => $user->name,
                'customer_email'  => $user->email,
                'customer_phone'  => $user->phone,
                'shipping_address'=> $shippingAddr,
                'billing_address' => $shippingAddr,
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shipping,
                'tax'             => 0,
                'discount_amount' => 0,
                'total'           => $total,
                'status'          => $def['status'],
                'payment_status'  => $def['payment_status'],
                'payment_method'  => $method,
                'paid_at'         => $paidAt,
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);

            foreach ($itemData as $it) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $it['product']->id,
                    'product_name' => $it['product']->name,
                    'sku'          => $it['product']->sku,
                    'unit_price'   => $it['unit_price'],
                    'quantity'     => $it['qty'],
                    'total'        => $it['total'],
                ]);
            }

            $order->logActivity('created', 'Order placed',
                'Order created via ' . (Order::SOURCES[$source] ?? ucfirst($source)) . '.',
                [], null
            );

            if ($def['payment_status'] === 'paid') {
                $order->logActivity('payment_updated', 'Marked as paid', null, [], null);
            }

            if (in_array($def['status'], ['shipped', 'delivered'])) {
                $order->logActivity('status_changed', 'Status changed to shipped', null, ['from' => 'processing', 'to' => 'shipped'], null);
            }

            if ($def['status'] === 'delivered') {
                $order->logActivity('status_changed', 'Status changed to delivered', null, ['from' => 'shipped', 'to' => 'delivered'], null);
            }
        }

        $this->command->info('15 demo orders seeded.');
    }
}
