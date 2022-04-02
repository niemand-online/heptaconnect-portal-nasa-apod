<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod\Packer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\File\FileReferenceFactoryContract;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;

class MediaPacker
{
    private FileReferenceFactoryContract $fileReferenceFactory;

    private NasaApodClient $nasaApodClient;

    public function __construct(FileReferenceFactoryContract $fileReferenceFactory, NasaApodClient $nasaApodClient)
    {
        $this->fileReferenceFactory = $fileReferenceFactory;
        $this->nasaApodClient = $nasaApodClient;
    }

    public function pack(array $apodEntry): Media
    {
        $copyright = $apodEntry['copyright'] ?? null;
        $date = $apodEntry['date'] ?? null;
        $explanation = $apodEntry['explanation'] ?? null;
        $hdurl = $apodEntry['hdurl'] ?? null;
        $mediaType = $apodEntry['media_type'] ?? null;
        $serviceVersion = $apodEntry['service_version'] ?? null;
        $title = $apodEntry['title'] ?? null;
        $url = $apodEntry['url'] ?? null;
        $thumbnailUrl = $apodEntry['thumbnail_url'] ?? null;

        $imageUrl = $mediaType === 'video' ? $thumbnailUrl : ($hdurl ?? $url);

        if (!\is_string($imageUrl) || $imageUrl === '') {
            throw new \RuntimeException('The APOD entry has no valid image URL');
        }

        $imageRequest = $this->nasaApodClient->getImageRequest($imageUrl);
        $imageMimeType = $this->nasaApodClient->getImageMimeType($imageUrl);
        $result = new Media();

        $result->setPrimaryKey($date);
        $result->setFile($this->fileReferenceFactory->fromRequest($imageRequest));
        $result->setMimeType($imageMimeType);
        $result->getTitle()->setFallback($title ?? $explanation ?? $copyright);

        return $result;
    }
}
