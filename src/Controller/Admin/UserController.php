<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/users', name: 'admin_user_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends BaseController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $search = trim((string) $request->query->get('q', ''));

        return $this->render('admin/user/list.html.twig', [
            'users' => $this->userRepository->findForAdmin($search),
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(
        '/{id}/toggle-active',
        name: 'toggle_active',
        methods: ['POST'],
    )]
    public function toggleActive(
        User $user,
        Request $request,
    ): RedirectResponse {
        $this->denyInvalidCsrf(
            'toggle-user-'.$user->getId(),
            $request->request->get('_token'),
            $this->translator,
        );

        if ($user === $this->getUser()) {
            $this->addFlash(
                'error',
                'admin.user.cannot_disable_self',
            );

            return $this->redirectToRoute('admin_user_show', [
                'id' => $user->getId(),
            ]);
        }

        $user->setIsActive(!$user->isActive());
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $user->isActive()
                ? 'admin.user.activated'
                : 'admin.user.deactivated',
        );

        return $this->redirectToRoute('admin_user_show', [
            'id' => $user->getId(),
        ]);
    }
}
