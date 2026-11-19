<?php

namespace App\Http\Controllers;

use App\Services\Localrydes\SpecialOfferApiException;
use App\Services\Localrydes\SpecialOfferClient;
use App\Support\SpecialOfferUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialOfferController extends Controller
{
    private const PER_PAGE = 12;

    public function __construct(private SpecialOfferClient $client) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $page = max(1, (int) $request->query('page', 1));

        $offers = [];
        $hasMore = false;
        $apiError = false;

        try {
            // Ask for one extra row so we know if a next page exists.
            $offers = $this->client->list([
                'search_key' => $search,
                'limit' => self::PER_PAGE + 1,
                'skip' => ($page - 1) * self::PER_PAGE,
            ]);
            $hasMore = count($offers) > self::PER_PAGE;
            $offers = array_slice($offers, 0, self::PER_PAGE);
        } catch (SpecialOfferApiException) {
            $apiError = true;
        }

        return view('pages.special-offers', compact('offers', 'search', 'page', 'hasMore', 'apiError'));
    }

    public function show(string $uuid, ?string $slug = null): View|RedirectResponse
    {
        try {
            $offer = $this->client->find($uuid);
        } catch (SpecialOfferApiException) {
            abort(503, 'Special offers are not available right now.');
        }

        abort_if($offer === null, 404);

        $expectedSlug = SpecialOfferUrl::slug($offer);

        if ($expectedSlug !== '' && $slug !== $expectedSlug) {
            return redirect()->to(SpecialOfferUrl::for($offer), 301);
        }

        $relatedOffers = [];

        try {
            $relatedOffers = collect($this->client->list(['limit' => 4]))
                ->reject(fn (array $item): bool => ($item['uuid'] ?? null) === ($offer['uuid'] ?? null))
                ->take(3)
                ->values()
                ->all();
        } catch (SpecialOfferApiException) {
            // Related offers are optional; the page still works without them.
        }

        return view('pages.special-offers-show', compact('offer', 'relatedOffers'));
    }
}
