<?php

namespace App\Http\Controllers\Auth;

use App\Application\User\Actions\RegisterAction;
use App\Application\User\DTO\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterAction $registerAction
    ) {}

    public function showRegistrationForm(): View
    {
        return view('backend.auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $dto = RegisterDTO::fromRequest($request->validated());
            $this->registerAction->execute($dto);

            return redirect()->route('home')
                ->with('success', 'Registration successful! Welcome aboard.');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('name', 'email', 'phone'))
                ->withErrors(['email' => $e->getMessage()]);
        }
    }
}
