<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod\Explorer;

use Heptacom\HeptaConnect\Dataset\Base\Date;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\Exploration\Contract\ExploreContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Exploration\Contract\ExplorerContract;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Packer\MediaPacker;

class MediaExplorer extends ExplorerContract
{
    public function supports(): string
    {
        return Media::class;
    }

    protected function run(ExploreContextInterface $context): iterable
    {
        /** @var NasaApodClient $api */
        $api = $context->getContainer()->get(NasaApodClient::class);
        /** @var MediaPacker $packer */
        $packer = $context->getContainer()->get(MediaPacker::class);
        $now = new Date();
        $oneDayInterval = new \DateInterval('P1D');
        $config = $context->getConfig();

        while ($now->format('Y-m-d') >= '1995-06-16') {
            try {
                yield $packer->pack($api->getImageOfTheDay($now, $config['api_key']));
            } catch (\RuntimeException $exception) {
            }

            $now = $now->sub($oneDayInterval);
        }
    }
}
