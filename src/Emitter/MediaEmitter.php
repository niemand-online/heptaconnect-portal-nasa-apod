<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod\Emitter;

use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitterContract;
use Heptacom\HeptaConnect\Portal\Base\Mapping\Contract\MappingInterface;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;

class MediaEmitter extends EmitterContract
{
    public function supports(): string
    {
        return Media::class;
    }

    protected function run(MappingInterface $mapping, EmitContextInterface $context): ?DatasetEntityContract
    {
        /** @var NasaApodClient $api */
        $api = $context->getContainer()->get(NasaApodClient::class);
        /** @var MediaPacker $packer */
        $packer = $context->getContainer()->get(MediaPacker::class);
        $day = \date_create_from_format('Y-m-d', $mapping->getExternalId());

        return $packer->pack($api->getImageOfTheDay($day, (string) $context->getConfig()['api_key']));
    }
}
