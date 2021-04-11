<?php

namespace Moukail\AppleWalletPassBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Moukail\AppleWalletPassBundle\Entity\Token;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AppleAuthenticator extends AbstractGuardAuthenticator
{
    private EntityManagerInterface $entityManager;

    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        //return $request->headers->has('X-AUTH-TOKEN');
        return $request->headers->has('Authorization') && (0 === strpos($request->headers->get('Authorization'), 'ApplePass ') || 0 === strpos($request->headers->get('Authorization'), 'AndroidPass '));
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');

        $this->logger->error('AppleAuthenticator:getCredentials', [
            'data' => $authorizationHeader,
        ]);
        // skip beyond "Bearer "
        //return substr($authorizationHeader, 7);

        if (!$authorizationHeader) {
            throw new BadCredentialsException();
        }

        list($token) = sscanf($authorizationHeader, 'ApplePass %s') ?? sscanf($authorizationHeader, 'AndroidPass %s');


        $this->logger->error('AppleAuthenticator:getCredentials', [
            'token' => $token,
        ]);

        if (!$token) {
            throw new BadCredentialsException();
        }

        return $token;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var Token $appleToken */
        $appleToken = $this->entityManager->getRepository(Token::class)->findOneBy([
            'token' => $credentials,
        ]);

        if (!$appleToken instanceof Token) {
            throw new CustomUserMessageAuthenticationException(
                'Invalid API ApplePassToken'
            );
        }

/*        if ($token->isTokenExpired($this->apiSecret)) {
            throw new CustomUserMessageAuthenticationException(
                'Token expired'
            );
        }*/

        return new User('apple', null);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Does this method support remember me cookies?
     *
     * Remember me cookie will be set if *all* of the following are met:
     *  A) This method returns true
     *  B) The remember_me key under your firewall is configured
     *  C) The "remember me" functionality is activated. This is usually
     *      done by having a _remember_me checkbox in your form, but
     *      can be configured by the "always_remember_me" and "remember_me_parameter"
     *      parameters under the "remember_me" firewall key
     *  D) The onAuthenticationSuccess method returns a Response object
     *
     * @return bool
     */
    public function supportsRememberMe()
    {
        // TODO: Implement supportsRememberMe() method.
    }
}
