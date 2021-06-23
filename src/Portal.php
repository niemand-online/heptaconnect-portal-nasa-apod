<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod;

use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalContract;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Portal extends PortalContract
{
    public function getConfigurationTemplate(): OptionsResolver
    {
        return parent::getConfigurationTemplate()
            ->setRequired(['api_key'])
            ->setAllowedTypes('api_key', 'string')
            ->setDefault('api_key', 'DEMO_KEY');
    }
}
