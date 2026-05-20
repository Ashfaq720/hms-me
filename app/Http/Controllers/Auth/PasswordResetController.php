<?php

namespace App\Http\Controllers\Auth;

use App\Application\User\Actions\ForgotPasswordAction;
use App\Application\User\Actions\ResetPasswordAction;
use App\Application\User\DTO\ForgotPasswordDTO;
use App\Application\User\DTO\ResetPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly ForgotPasswordAction $forgotPasswordAction,
        private readonly ResetPasswordAction $resetPasswordAction
    ) {}

    public function showForgotForm(): View
    {
        return view('backend.auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        try {
            $dto = ForgotPasswordDTO::fromRequest($request->validated());
            $status = $this->forgotPasswordAction->execute($dto);

            return $status === Password::RESET_LINK_SENT
                ? back()->with('success', 'Password reset link sent to your email.')
                : back()->withErrors(['email' => 'Unable to send reset link.']);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('backend.auth.new-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        try {
            $dto = ResetPasswordDTO::fromRequest($request->validated());
            $success = $this->resetPasswordAction->execute($dto);

            if ($success) {
                return redirect()->route('login')
                    ->with('success', 'Password reset successfully. Please login with your new password.');
            }

            return back()->withErrors(['email' => 'Failed to reset password. Please try again.']);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }
}
