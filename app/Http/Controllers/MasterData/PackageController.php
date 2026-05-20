<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::latest()->paginate(20);
        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        $services = Service::orderBy('name')->get(['id', 'name']); // if you have rate in services, add it too
        return view('packages.create', compact('services'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name'               => 'required|string|max:255',
            'discount'           => 'required|numeric|min:0|max:100',
            'description'        => 'nullable|string',

            'items'              => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.rate'       => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            $package = Package::create([
                'name'         => $request->name,
                'description'  => $request->description,
                'discount'     => $request->discount,
                'total_amount' => 0,
            ]);

            $subtotal = 0;

            foreach ($request->items as $row) {
                $amount    = (float) $row['quantity'] * (float) $row['rate'];
                $subtotal += $amount;

                PackageService::create([
                    'package_id' => $package->id,
                    'service_id' => $row['service_id'],
                    'quantity'   => $row['quantity'],
                    'rate'       => $row['rate'],
                    'amount'     => $amount,
                ]);
            }

            // total_amount = subtotal (same as your schema name)
            $package->update([
                'total_amount' => $subtotal,
            ]);
        });

        return redirect()->route('packages.index')->with('success', 'Package created successfully!');
    }

    public function edit($id)
    {
        $package  = Package::with('services')->findOrFail($id);
        $services = Service::orderBy('name')->get(['id', 'name']);

        return view('packages.edit', compact('package', 'services'));
    }

    public function update(Request $request, $id)
    {
        $package = Package::with('services')->findOrFail($id);

        $request->validate([
            'name'               => 'required|string|max:255',
            'discount'           => 'required|numeric|min:0|max:100',
            'description'        => 'nullable|string',

            'items'              => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.rate'       => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $package) {

            $package->update([
                'name'        => $request->name,
                'description' => $request->description,
                'discount'    => $request->discount,
            ]);

            // delete old items
            PackageService::where('package_id', $package->id)->delete();

            $subtotal = 0;
            foreach ($request->items as $row) {
                $amount    = (float) $row['quantity'] * (float) $row['rate'];
                $subtotal += $amount;

                PackageService::create([
                    'package_id' => $package->id,
                    'service_id' => $row['service_id'],
                    'quantity'   => $row['quantity'],
                    'rate'       => $row['rate'],
                    'amount'     => $amount,
                ]);
            }

            $package->update([
                'total_amount' => $subtotal,
            ]);
        });

        return redirect()->route('packages.index')->with('success', 'Package updated successfully!');
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $package->delete();
        return back()->with('success', 'Package deleted successfully!');
    }
}
