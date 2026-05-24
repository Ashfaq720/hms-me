<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::latest()->paginate(15);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(CreateServiceRequest $request)
    {
        $input           = $request->validated();
        $input['status'] = isset($input['status']) ? 1 : 0;
        Service::create($input);

        return redirect()
            ->route('services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Service $service, UpdateServiceRequest $request)
    {
        $input           = $request->validated();
        $input['status'] = isset($input['status']) ? 1 : 0;
        $service->update($input);

        return redirect()
            ->route('services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
