<?php

namespace App\Handler;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionHandler {
    protected $session;
    protected $router;
    protected $maxIdleTime;
    protected $tokenStorage;
    


    public function __construct(SessionInterface $session, TokenStorageInterface $tokenStorage, RouterInterface $router, $maxIdleTime = 0)
    {
        $this->session = $session;
        $this->router = $router;
        $this->maxIdleTime = $maxIdleTime;
        $this->tokenStorage = $tokenStorage;
              
    }

    public function onKernelRequest(GetResponseEvent $event)
    {                
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {

            return;
        }

        if ($this->maxIdleTime > 0) {

            $this->session->start();
            $lapse = time() - $this->session->getMetadataBag()->getLastUsed();

            if ($lapse > $this->maxIdleTime) {

                $this->tokenStorage->setToken(null);
                $this->session->getFlashBag()->set('info', 'You have been logged out due to inactivity.');

                //Redirect URL to logout               
                $event->setResponse(new RedirectResponse($this->router->generate('logout'))); 
            }
        }
    }
} 