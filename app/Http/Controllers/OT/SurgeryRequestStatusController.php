<?php

namespace App\Http\Controllers\OT;

use App\Models\Ot\OtSurgeryRequest;
use App\Services\Ot\SurgeryRequestService;
use Illuminate\Http\Request;

/**
 * State-transition endpoints for OT surgery requests. Each method is a
 * one-line dispatch to SurgeryRequestService — no business logic lives here.
 */
class SurgeryRequestStatusController extends OtBaseController
{
    public function __construct(protected SurgeryRequestService $service) {}

    public function submit($id)
    {
        return $this->respond($this->service->submit($this->find($id)));
    }

    public function review($id)
    {
        return $this->respond($this->service->review($this->find($id)));
    }

    public function accept(Request $request, $id)
    {
        return $this->respond(
            $this->service->accept($this->find($id), auth()->id(), $request->get('notes'))
        );
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        return $this->respond(
            $this->service->reject($this->find($id), auth()->id(), $request->get('reason'))
        );
    }

    public function sendBack(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        return $this->respond(
            $this->service->sendBack($this->find($id), auth()->id(), $request->get('reason'))
        );
    }

    public function pendingInfo(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        return $this->respond(
            $this->service->pendingInfo($this->find($id), auth()->id(), $request->get('reason'))
        );
    }

    public function fastTrack(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        return $this->respond(
            $this->service->fastTrack($this->find($id), auth()->id(), $request->get('reason'))
        );
    }

    public function moveToScheduling($id)
    {
        return $this->respond($this->service->moveToScheduling($this->find($id)));
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        return $this->respond(
            $this->service->cancel($this->find($id), $request->get('reason'))
        );
    }

    public function juniorApprove(Request $request, $id)
    {
        return $this->respond(
            $this->service->juniorApprove($this->find($id), auth()->id(), $request->get('notes'))
        );
    }

    public function consultantApprove(Request $request, $id)
    {
        return $this->respond(
            $this->service->consultantApprove($this->find($id), auth()->id(), $request->get('notes'))
        );
    }

    /* ────── Helpers ────── */

    protected function find($id): OtSurgeryRequest
    {
        return OtSurgeryRequest::findOrFail($id);
    }

    protected function respond(array $result)
    {
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error',   $result['message']);
    }
}
