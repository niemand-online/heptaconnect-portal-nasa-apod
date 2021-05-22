<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod;

use Heptacom\HeptaConnect\Core\Storage\NormalizationRegistry;
use Heptacom\HeptaConnect\Portal\Base\Emission\EmitterCollection;
use Heptacom\HeptaConnect\Portal\Base\Exploration\ExplorerCollection;
use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalContract;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;
use Psr\Container\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Portal extends PortalContract
{
    public function getExplorers(): ExplorerCollection
    {
        return new ExplorerCollection([
            new Explorer\MediaExplorer(),
        ]);
    }

    public function getEmitters(): EmitterCollection
    {
        return new EmitterCollection([
            new Emitter\MediaEmitter(),
        ]);
    }

    public function getServices(): array
    {
        return [
            NasaApodClient::class => static fn (ContainerInterface $ci): NasaApodClient => new NasaApodClient(
                Psr18ClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findUriFactory(),
            ),
            MediaPacker::class => static fn (ContainerInterface $ci): MediaPacker => new MediaPacker(
                $ci->get(NormalizationRegistry::class),
                $ci->get(NasaApodClient::class),
            ),
        ];
    }

    public function getConfigurationTemplate(): OptionsResolver
    {
        return parent::getConfigurationTemplate()
            ->setRequired(['api_key'])
            ->setAllowedTypes('api_key', 'string')
            ->setDefault('api_key', 'DEMO_KEY');
    }
}
