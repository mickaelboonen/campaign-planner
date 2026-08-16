<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LocaleController extends BaseController
{
    #[Route(
        '/locale/{_locale}',
        name: 'app_locale',
        requirements: [
            '_locale' => 'fr|en',
        ],
        methods: ['GET'],
    )]
    public function change(Request $request): Response
    {
        return $this->redirect(
            $request->headers->get('referer')
            ?? $this->generateUrl('app_login'),
        );
    }
}
