<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class LocaleSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_LOCALES = ['fr', 'en'];

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        // Choix manuel via /locale/{_locale}
        $requestedLocale = $request->attributes->get('_locale');

        if (
            is_string($requestedLocale)
            && in_array($requestedLocale, self::SUPPORTED_LOCALES, true)
        ) {
            $session->set('_locale', $requestedLocale);
            $request->setLocale($requestedLocale);

            return;
        }

        // Locale déjà choisie précédemment
        $locale = $session->get('_locale');

        if (is_string($locale)) {
            $request->setLocale($locale);

            return;
        }

        // Première visite : détection du navigateur
        $locale = $request->getPreferredLanguage(
            self::SUPPORTED_LOCALES,
        ) ?? 'fr';

        $session->set('_locale', $locale);
        $request->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 20],
            ],
        ];
    }
}
