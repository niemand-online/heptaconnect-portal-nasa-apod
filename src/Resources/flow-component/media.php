<?php
declare(strict_types=1);

use Heptacom\HeptaConnect\Dataset\Base\Date;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\Builder\FlowComponent;
use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\ConfigurationContract;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Portal;
use NiemandOnline\HeptaConnect\Portal\NasaApod\Support\NasaApodClient;
use Psr\Log\LoggerInterface;

FlowComponent::explorer(Media::class)
    ->run(static function(NasaApodClient $api, MediaPacker $packer, LoggerInterface $logger, ConfigurationContract $config): iterable {
        $now = new Date();
        $oneDayInterval = new \DateInterval('P1D');
        $counter = null;

        if ($config->get(Portal::CONFIG_PREVIEW)) {
            $counter = (int) $config->get(Portal::CONFIG_PREVIEW_LIMIT);
        }

        while ($now->format('Y-m-d') >= '1995-06-16') {
            try {
                $apodEntry = $api->getImageOfTheDay($now);

                if ($apodEntry === null) {
                    $logger->info('End reached. (Might be due to API limit)');
                    break;
                }

                yield $packer->pack($apodEntry);

                if ($counter !== null && --$counter === 0) {
                    break;
                }
            } catch (\RuntimeException $exception) {
            } catch (\Throwable $exception) {
                $logger->warning('End reached due to: ' . $exception->getMessage(), [
                    'exception' => $exception,
                ]);
                break;
            }

            $now = $now->sub($oneDayInterval);
        }
    });

FlowComponent::emitter(Media::class)
    ->run(static fn (string $id, NasaApodClient $api, MediaPacker $packer): ?Media => $packer->pack($api->getImageOfTheDay(\date_create_from_format('Y-m-d', $id))));
