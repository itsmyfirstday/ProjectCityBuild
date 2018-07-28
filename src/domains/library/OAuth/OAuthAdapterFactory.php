<?php
namespace Domains\Library\OAuth;

use Infrastructure\Enum;
use Domains\Library\OAuth\Adapters\Discord\DiscordOAuthAdapter;
use Domains\Library\OAuth\Adapters\Google\GoogleOAuthAdapter;
use Domains\Library\OAuth\Exceptions\UnsupportedOAuthAdapter;

class OAuthAdapterFactory extends Enum
{
    public const FACEBOOK   = 'facebook';
    public const TWITTER    = 'twitter';
    public const GOOGLE     = 'google';
    public const DISCORD    = 'discord';
    public const STEAM      = 'steam';

    public function make(string $providerName) : OAuthProviderContract
    {
        switch ($providerName) {
            case self::DISCORD:
                return resolve(DiscordOAuthAdapter::class);
            
            case self::GOOGLE:
                return resolve(GoogleOAuthAdapter::class);

            default:
                throw new UnsupportedOAuthAdapter('Unsupported OAuth provider');
        }
    }
}