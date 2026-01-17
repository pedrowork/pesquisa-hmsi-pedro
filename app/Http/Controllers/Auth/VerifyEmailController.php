<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    /**
     * Verifica o email do usuário sem exigir autenticação.
     * A segurança é garantida pela assinatura da URL.
     */
    public function __invoke(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verifica se o hash corresponde ao email do usuário
        if (! hash_equals((string) $hash, sha1($user->email))) {
            abort(403, 'Invalid verification link.');
        }

        // Verifica se o email já foi verificado
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Email já foi verificado. Você pode fazer login.');
        }

        // Verifica a assinatura da URL (segurança)
        if (! URL::hasValidSignature($request)) {
            abort(403, 'Invalid or expired verification link.');
        }

        // Marca o email como verificado
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('login')->with('status', 'Email verificado com sucesso! Você pode fazer login agora.');
    }
}
