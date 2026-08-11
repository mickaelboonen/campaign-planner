<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'admin_user_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        UserRepository $userRepository,
    ): Response {
        $search = trim((string) $request->query->get('q', ''));

        return $this->render('admin/user/list.html.twig', [
            'users' => $userRepository->findForAdmin($search),
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
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid(
            'toggle-user-'.$user->getId(),
            $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        /*
         * Empêche un admin de désactiver son propre compte
         * par accident.
         */
        if ($user === $this->getUser()) {
            $this->addFlash(
                'error',
                'Vous ne pouvez pas désactiver votre propre compte.',
            );

            return $this->redirectToRoute('admin_user_show', [
                'id' => $user->getId(),
            ]);
        }

        $user->setIsActive(!$user->isActive());

        $entityManager->flush();

        $this->addFlash(
            'success',
            $user->isActive()
                ? 'Le compte a été réactivé.'
                : 'Le compte a été désactivé.',
        );

        return $this->redirectToRoute('admin_user_show', [
            'id' => $user->getId(),
        ]);
    }
}
