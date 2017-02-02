<?php

namespace iTrack\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FindMyiPhone\Client;
use RuntimeException;
use DateTime;

class LsCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('ls')
            ->setDescription('List apple devices')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Config file (config.yml by default)'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->loadConfig($input);
        $output->writeLn("Connecting to iCloud");
        $client = new Client($config['username'], $config['password']);
        $output->writeLn("Listing devices");
        $client->getDevices();
        foreach ($client->devices as $device) {
            $output->writeLn(" * Device: " . $device->name . ' (' . $device->API['deviceDisplayName'] . ') ID: ' . $device->ID);
            $output->writeLn("   Battery: " . $device->batteryLevel . '% (' . $device->batteryStatus . ')');
        }
        exit("Done!\n");
    }
}
