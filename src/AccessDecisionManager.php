<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 26.09.17
 */

namespace GepurIt\AccessManagerBundle;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This class is decorator for AccessDecisionManager
 * allows to check rules for specified user
 *
 * Class AccessDecisionManager
 * @package AppBundle\Security
 */
class AccessDecisionManager implements AccessDecisionManagerInterface
{
    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * AccessDecisionManager constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     * @param string $providerKey
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, string $providerKey)
    {
        $this->decisionManager = $decisionManager;
        $this->providerKey = $providerKey;
    }

    /**
     * @param UserInterface $user
     * @param string $access
     * @param string $source
     * @return bool
     */
    public function isGranted(UserInterface $user, string $access, string $source)
    {
        $token = new UsernamePasswordToken($user, '', $this->providerKey, $user->getRoles());
        return $this->decide($token, [$access], $source);
    }

    /**
     * {@inheritdoc}
     */
    public function decide(TokenInterface $token, array $attributes, $object = null)
    {
        return $this->decisionManager->decide($token, $attributes, $object);
    }
}

