<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod\Packer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\Serialization\Contract\NormalizationRegistryContract;
use Heptacom\HeptaConnect\Portal\Base\Serialization\Contract\SerializableStream;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;

class MediaPacker
{
    private NormalizationRegistryContract $normalizer;

    private NasaApodClient $nasaApodClient;

    public function __construct(NormalizationRegistryContract $normalizer, NasaApodClient $nasaApodClient)
    {
        $this->normalizer = $normalizer;
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

        $imageResponse = $this->nasaApodClient->getImage($imageUrl);

        if ($imageResponse === null) {
            throw new \RuntimeException('The APOD entry has no valid image');
        }

        $stream = new SerializableStream($imageResponse->getBody());
        $normalizer = $this->normalizer->getNormalizer($stream);

        $result = new Media();

        $result->setPrimaryKey($date);
        $result->setNormalizedStream((string) $normalizer->normalize($stream, null, [
            'mediaId' => $result->getPrimaryKey(),
        ]));
        $result->setMimeType($imageResponse->getHeaderLine('Content-Type'));
        $result->getTitle()->setFallback($title ?? $explanation ?? $copyright);

        return $result;
    }
}
