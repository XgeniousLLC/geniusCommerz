<?php

namespace App\Http\Middleware;

use App\Models\Media;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $logoMediaId    = SiteSetting::get('general.logo_media_id');
        $faviconMediaId = SiteSetting::get('general.favicon_media_id');
        $logoUrl        = $logoMediaId    ? Media::find((int) $logoMediaId)?->getUrl('thumb')  : null;
        $faviconUrl     = $faviconMediaId ? Media::find((int) $faviconMediaId)?->getUrl()      : null;

        return array_merge(parent::share($request), [
            'site' => [
                'name'        => SiteSetting::get('general.site_name', config('app.name')),
                'tagline'     => SiteSetting::get('general.site_tagline', ''),
                'logoUrl'     => $logoUrl,
                'faviconUrl'  => $faviconUrl,
                'announceBar' => SiteSetting::get('general.announce_bar', ''),
                'phone'       => SiteSetting::get('general.phone', ''),
                'email'       => SiteSetting::get('general.admin_email', ''),
                'address'     => SiteSetting::get('general.address', ''),
                'copyright'   => SiteSetting::get('storefront.copyright', '') ?: SiteSetting::get('general.copyright_text', ''),
                'productWhatsappEnabled' => (bool) SiteSetting::get('storefront.product_whatsapp_enabled'),
                'productWhatsappNumber'  => SiteSetting::get('storefront.product_whatsapp_number', ''),
                'productWhatsappMessage' => SiteSetting::get('storefront.product_whatsapp_message', 'Hi, I want to order: {product}'),
                'productCallEnabled'     => (bool) SiteSetting::get('storefront.product_call_enabled'),
                'productCallNumber'      => SiteSetting::get('storefront.product_call_number', ''),
                'globalReturnPolicy'     => SiteSetting::get('storefront.global_return_policy', ''),
            ],
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id'    => $request->user()->id,
                        'name'  => $request->user()->name,
                        'email' => $request->user()->email,
                    ]
                    : null,
            ],
            'flash' => [
                'success'         => $request->session()->get('success'),
                'comment_success' => $request->session()->get('comment_success'),
                'error'           => $request->session()->get('error'),
            ],
        ]);
    }
}
