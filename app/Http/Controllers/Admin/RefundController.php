<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function index(Request $request): View
    {
        $query = Refund::with(['order', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $refunds = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.refunds.index', compact('refunds'));
    }

    public function show(Refund $refund): View
    {
        $refund->load(['order.items', 'user']);
        return view('admin.refunds.show', compact('refund'));
    }

    public function update(Request $request, Refund $refund): RedirectResponse
    {
        $data = $request->validate([
            'status'     => 'required|in:approved,rejected,processed',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $data['resolved_at'] = now();

        // If approved/processed, update order payment_status
        if (in_array($data['status'], ['approved', 'processed'])) {
            $refund->order->update(['payment_status' => 'refunded', 'status' => 'refunded']);
        }

        $refund->update($data);

        $refund->order->logActivity(
            'refund_' . $data['status'],
            'Refund ' . ucfirst($data['status']),
            $data['admin_note'],
            [],
            auth('admin')->id()
        );

        return back()->with('success', 'Refund ' . $data['status'] . '.');
    }
}
