<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/logs', name: 'admin_log_')]
#[IsGranted('ROLE_ADMIN')]
final class LogController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        KernelInterface $kernel,
    ): Response {
        $logFile = sprintf(
            '%s/var/log/%s.log',
            $kernel->getProjectDir(),
            $kernel->getEnvironment(),
        );

        $logs = [];

        if (is_file($logFile) && is_readable($logFile)) {
            $lines = file(
                $logFile,
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES,
            ) ?: [];

            foreach (array_reverse($lines) as $line) {
                if (!preg_match(
                    '/^\[(?<date>[^\]]+)\]\s+(?<channel>[\w.-]+)\.(?<level>ERROR|CRITICAL|ALERT|EMERGENCY):\s+(?<message>.*)$/',
                    $line,
                    $matches,
                )) {
                    continue;
                }

                $logs[] = [
                    'date' => new \DateTimeImmutable($matches['date']),
                    'channel' => $matches['channel'],
                    'level' => $matches['level'],
                    'message' => $matches['message'],
                ];

                if (count($logs) >= 100) {
                    break;
                }
            }
        }

        return $this->render('admin/log/list.html.twig', [
            'logs' => $logs,
            'logFileExists' => is_file($logFile),
        ]);
    }
}
