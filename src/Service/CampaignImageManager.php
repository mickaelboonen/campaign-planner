<?php

namespace App\Service;

use App\Entity\Campaign;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class CampaignImageManager
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $campaignImagesDirectory,
    ) {
    }

    public function upload(
        Campaign $campaign,
        UploadedFile $file,
    ): void {
        $originalName = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME,
        );

        $safeName = $this->slugger
            ->slug($originalName)
            ->lower();

        $extension = $file->guessExtension() ?? 'bin';

        $fileName = sprintf(
            '%s-%s.%s',
            $safeName,
            bin2hex(random_bytes(6)),
            $extension,
        );

        $oldImage = $campaign->getImage();

        $file->move(
            $this->campaignImagesDirectory,
            $fileName,
        );

        $campaign->setImage($fileName);

        if ($oldImage !== null) {
            $this->deleteFile($oldImage);
        }
    }

    private function deleteFile(string $fileName): void
    {
        $path = sprintf(
            '%s/%s',
            rtrim($this->campaignImagesDirectory, '/'),
            $fileName,
        );

        if (is_file($path)) {
            unlink($path);
        }
    }

    public function delete(
        Campaign $campaign,
    ): void
    {
        if ($campaign->getImage() === null) {
            return;
        }

        $this->deleteFile(
            $campaign->getImage(),
        );
    }
}
