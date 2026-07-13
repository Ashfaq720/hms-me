<?php

namespace App\Http\Controllers\Auth;

use App\Application\User\Actions\LoginAction;
use App\Application\User\Actions\LogoutAction;
use App\Application\User\DTO\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginAction $loginAction,
        private readonly LogoutAction $logoutAction
    ) {}

    public function showLoginForm(): View
    {
        return view('backend.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $dto = LoginDTO::fromRequest($request->validated());
            $this->loginAction->execute($dto);

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back!');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function logout(): RedirectResponse
    {
        $this->logoutAction->execute();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
