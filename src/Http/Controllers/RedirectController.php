<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Shammaa\LaravelUrlShortener\Models\ShortLink;
use Shammaa\LaravelUrlShortener\Services\LinkManager;
use Shammaa\LaravelUrlShortener\Services\VisitTracker;

class RedirectController
{
    protected LinkManager $linkManager;
    protected VisitTracker $visitTracker;

    public function __construct(LinkManager $linkManager, VisitTracker $visitTracker)
    {
        $this->linkManager = $linkManager;
        $this->visitTracker = $visitTracker;
    }

    /**
     * Redirect to destination URL
     */
    public function __invoke(Request $request, string $key): RedirectResponse|View
    {
        $link = $this->linkManager->findByKey($key);

        if (!$link) {
            abort(404, 'Short link not found');
        }

        // Check if link is accessible
        if (!$this->linkManager->isAccessible($link)) {
            abort(410, 'This link has expired or is no longer available');
        }

        // Check password protection
        if ($link->password_protected) {
            // Check if password is already verified in session
            if (!$request->session()->has("link_password_verified_{$link->id}")) {
                return $this->showPasswordForm($link);
            }
        }

        // Track visit
        $this->visitTracker->track($link, $request);

        // Get destination URL with UTM parameters
        $destinationUrl = $this->linkManager->getDestinationUrl($link, [
            'utm_campaign' => $link->key,
        ]);

        // Redirect
        return redirect(
            $destinationUrl,
            (int) $link->redirect_status_code
        );
    }

    /**
     * Show password form
     */
    protected function showPasswordForm(ShortLink $link): View
    {
        return view('url-shortener::password', compact('link'));
    }

    /**
     * Check password and redirect
     */
    public function checkPassword(Request $request, string $key): RedirectResponse|View
    {
        $link = $this->linkManager->findByKey($key);

        if (!$link) {
            abort(404);
        }

        $password = $request->input('password');

        if ($this->linkManager->verifyPassword($link, $password)) {
            // Store verification in session
            $request->session()->put("link_password_verified_{$link->id}", true);

            // Redirect to destination
            return $this->__invoke($request, $key);
        }

        return view('url-shortener::password', [
            'link' => $link,
            'error' => 'Invalid password',
        ]);
    }
}
