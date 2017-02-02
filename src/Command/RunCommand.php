<?php

namespace iTrack\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PidHelper\PidHelper;
use FindMyiPhone\Client;
use Minerva\Writer\PdoWriter;
use RuntimeException;
use DateTime;

class RunCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run iTrack process')
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
        $pidHelper = new PidHelper('/tmp/', 'itrack.pid');
        if (!$pidHelper->lock()) {
            exit("Already running\n");
        }

        $config = $this->loadConfig($input);
        $pdo = $this->getPdo($config['pdo']);
        $writer = new PdoWriter($pdo, 'location');
        $output->writeLn("Connecting to iCloud");
        $client = new Client($config['username'], $config['password']);
        $output->writeLn("Listing devices");
        if (!isset($config['devices'])) {
            throw new RuntimeException("Devices not yet listed");
        }
        $client->getDevices();
        while (1) {
            foreach ($client->devices as $device) {
                if (in_array($device->ID, $config['devices'])) {
                    $output->writeLn(
                        "Fetching location: " . $device->name . ' (' . $device->API['deviceDisplayName'] . ')'
                    );
                    // Locate the device
                    $location = $client->locate($device->ID);
                    $label = $this->resolveLabel($config, $location->latitude, $location->longitude);
                    $output->writeLn(" * Location: " . $location->latitude . ", " . $location->longitude . ") = $label");
                    
                    
                    $row = [];
                    $row['device_id'] = $device->ID;
                    $row['device_name'] = $device->name;
                    $row['device_display_name'] = $device->API['deviceDisplayName'];
                    $row['stamp'] = time();
                    $row['latitude'] = $location->latitude;
                    $row['longitude'] = $location->longitude;
                    $row['label'] = $label;

                    $writer->insert(
                        $row
                    );
                }
            }
            $output->writeLn("Sleeping for " . $config['interval'] . ' seconds');
            sleep($config['interval']);
        }
        $pidHelper->unlock();
        exit("Done!\n");
    }
}
