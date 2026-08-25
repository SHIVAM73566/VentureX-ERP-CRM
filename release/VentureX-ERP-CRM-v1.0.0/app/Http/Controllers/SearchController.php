<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $q = $request->string('q')->trim()->toString();

        $results = ['customers' => [], 'leads' => [], 'opportunities' => [], 'suppliers' => [], 'offers' => [], 'documents' => []];

        if (strlen($q) >= 2) {
            $searchTerm = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $like = "%{$searchTerm}%";

            $results['customers'] = Customer::ofCompany()
                ->where(fn ($b) => $b->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('tax_id', 'like', $like))
                ->limit(5)->get();

            $results['leads'] = Lead::ofCompany()
                ->where(fn ($b) => $b->where('contact_name', 'like', $like)->orWhere('company_name', 'like', $like)->orWhere('email', 'like', $like))
                ->limit(5)->get();

            $results['opportunities'] = Opportunity::ofCompany()
                ->where('name', 'like', $like)
                ->limit(5)->get();

            $results['suppliers'] = Supplier::ofCompany()
                ->where(fn ($b) => $b->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->limit(5)->get();

            $results['offers'] = SupplierOffer::ofCompany()
                ->with('supplier')
                ->where(fn ($b) => $b->where('material_description', 'like', $like)->orWhere('grade', 'like', $like))
                ->limit(5)->get();

            $results['documents'] = Document::ofCompany()
                ->where('title', 'like', $like)
                ->limit(5)->get();
        }

        return view('search.index', ['q' => $q, 'results' => $results]);
    }
}
