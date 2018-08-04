<?php

namespace SocialiteProviders\StarCitizenWiki;

use SocialiteProviders\Manager\SocialiteWasCalled;

class StarCitizenWikiExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'starcitizenwiki',
            __NAMESPACE__.'\Provider',
            __NAMESPACE__.'\Server'
        );
    }
}
