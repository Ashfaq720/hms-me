<?php

namespace App\Http\Controllers\ServiceCharge;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceCharge\StoreServiceCatalogRequest;
use App\Models\ServiceCharge\ServiceCatalog;
use Illuminate\Http\Request;

class ServiceCatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorizeAction('service_charge.view');

        $query = ServiceCatalog::query()->orderBy('name');

        if ($q = $request->string('q')->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($type = $request->string('type')->toString()) {
            $query->where('service_type', $type);
        }

        $catalogs = $query->paginate(20)->withQueryString();

        return view('service-charge.catalog.index', compact('catalogs'));
    }

    public function create()
    {
        $this->authorizeAction('service_charge.manage');
        $catalog = new ServiceCatalog();
        return view('service-charge.catalog.create', compact('catalog'));
    }

    public function store(StoreServiceCatalogRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $catalog = ServiceCatalog::create($data);
        return redirect()
            ->route('service-charge.catalog.show', $catalog)
            ->with('success', 'Service catalog entry created.');
    }

    public function show(ServiceCatalog $service_catalog)
    {
        $this->authorizeAction('service_charge.view');
        $service_catalog->load('rules', 'postings');
        return view('service-charge.catalog.show', ['catalog' => $service_catalog]);
    }

    public function edit(ServiceCatalog $service_catalog)
    {
        $this->authorizeAction('service_charge.manage');
        return view('service-charge.catalog.edit', ['catalog' => $service_catalog]);
    }

    public function update(StoreServiceCatalogRequest $request, ServiceCatalog $service_catalog)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $service_catalog->update($data);
        return redirect()
            ->route('service-charge.catalog.show', $service_catalog)
            ->with('success', 'Service catalog entry updated.');
    }

    public function destroy(ServiceCatalog $service_catalog)
    {
        $this->authorizeAction('service_charge.manage');
        $service_catalog->delete();
        return redirect()
            ->route('service-charge.catalog.index')
            ->with('success', 'Service catalog entry archived.');
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if (! $user || ! $user->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
