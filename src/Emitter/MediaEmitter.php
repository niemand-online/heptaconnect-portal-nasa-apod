<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod\Emitter;

use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitterContract;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;

class MediaEmitter extends EmitterContract
{
    public function supports(): string
    {
        return Media::class;
    }

    protected function run(string $externalId, EmitContextInterface $context): ?DatasetEntityContract
    {
        /** @var NasaApodClient $api */
        $api = $context->getContainer()->get(NasaApodClient::class);
        /** @var MediaPacker $packer */
        $packer = $context->getContainer()->get(MediaPacker::class);
        $day = \date_create_from_format('Y-m-d', $externalId);

        return $packer->pack($api->getImageOfTheDay($day));
    }
}
