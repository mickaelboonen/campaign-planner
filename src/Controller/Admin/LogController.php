<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/logs', name: 'admin_log_')]
#[IsGranted('ROLE_ADMIN')]
final class LogController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, KernelInterface $kernel): Response
    {
        $date = $request->query->get('date');

        try {
            $selectedDate = $date
                ? new \DateTimeImmutable($date)
                : new \DateTimeImmutable();
        } catch (\Exception) {
            $selectedDate = new \DateTimeImmutable();
        }

        if ($kernel->getEnvironment() === 'prod') {
            $logFile = sprintf(
                '%s/var/log/prod-%s.log',
                $kernel->getProjectDir(),
                $selectedDate->format('Y-m-d'),
            );
        } else {
            $logFile = sprintf(
                '%s/var/log/%s.log',
                $kernel->getProjectDir(),
                $kernel->getEnvironment(),
            );
        }

        $logs = [];

        if (is_file($logFile) && is_readable($logFile)) {
            $lines = file(
                $logFile,
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES,
            ) ?: [];

            foreach (array_reverse($lines) as $line) {
                $entry = json_decode($line, true);

                if (!is_array($entry) || ($entry['level'] ?? 0) < 400) {
                    continue;
                }

                $logs[] = [
                    'date' => new \DateTimeImmutable($entry['datetime']),
                    'channel' => $entry['channel'] ?? 'app',
                    'level' => $entry['level_name'] ?? 'ERROR',
                    'message' => $entry['message'] ?? 'Erreur inconnue',
                ];
            }
        }

        return $this->render('admin/log/list.html.twig', [
            'logs' => $logs,
            'logFileExists' => is_file($logFile),
            'selectedDate' => $selectedDate,
            'previousDate' => $selectedDate->modify('-1 day'),
            'nextDate' => $selectedDate->modify('+1 day'),
        ]);
    }
}
