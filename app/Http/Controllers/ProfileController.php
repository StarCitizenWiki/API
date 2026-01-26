<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateTokenRequest;
use App\Http\Requests\DestroyAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $tokens = $user->tokens()->latest()->get();

        return view('profile', [
            'tokens' => $tokens,
        ]);
    }

    /**
     * Create a new API token for the authenticated user.
     *
     * Users can have at most 5 API tokens. If the user already has 5 tokens,
     * the request will be blocked with an error message.
     *
     * The token name is provided by the user via the request.
     * The plain text token is only shown once for the user to copy.
     */
    public function createToken(CreateTokenRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user has reached the maximum limit of 5 tokens
        if ($user->tokens()->count() >= 5) {
            return back()
                ->withInput()
                ->withErrors(['token' => 'You have reached the maximum limit of 5 API tokens. Delete an existing token before creating a new one.']);
        }

        $tokenName = $request->validated('name');

        $tokenResult = $user->createToken($tokenName, ['*']);
        $plainTextToken = $tokenResult->plainTextToken;

        $request->session()->flash('token', $plainTextToken);
        $request->session()->flash('token_name', $tokenName);

        return back()->with('status', 'Your API token is ready');
    }

    /**
     * Delete the user's account.
     *
     * Requires user to type "DELETE_ACCOUNT" to confirm deletion.
     * After deletion, the user is logged out and redirected to the home page.
     */
    public function destroy(DestroyAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        return redirect('/')->with('status', 'account-deleted');
    }

    /**
     * Delete a specific API token for the authenticated user.
     *
     * Validates that the token belongs to the authenticated user before deletion.
     * Returns 404 if the token doesn't exist or belongs to a different user.
     */
    public function deleteToken(Request $request, int $tokenId): RedirectResponse
    {
        $user = $request->user();

        $token = $user->tokens()->find($tokenId);

        if ($token === null) {
            abort(404, 'Token not found');
        }

        $token->delete();

        return back()->with('status', 'api-token-deleted');
    }
}
