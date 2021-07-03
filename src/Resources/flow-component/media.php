<?php
declare(strict_types=1);

use Heptacom\HeptaConnect\Dataset\Base\Date;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\Builder\FlowComponent;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;

FlowComponent::explorer(Media::class)
    ->run(static function(NasaApodClient $api, MediaPacker $packer): iterable {
        $now = new Date();
        $oneDayInterval = new \DateInterval('P1D');

        while ($now->format('Y-m-d') >= '1995-06-16') {
            try {
                yield $packer->pack($api->getImageOfTheDay($now));
            } catch (\RuntimeException $exception) {
            }

            $now = $now->sub($oneDayInterval);
        }
    });

FlowComponent::emitter(Media::class)
    ->run(static fn (string $id, NasaApodClient $api, MediaPacker $packer): ?Media => $packer->pack($api->getImageOfTheDay(\date_create_from_format('Y-m-d', $id))));
