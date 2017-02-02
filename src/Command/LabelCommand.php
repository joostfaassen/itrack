<?php

namespace iTrack\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Minerva\Writer\PdoWriter;
use RuntimeException;
use DateTime;
use PDO;

class LabelCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('label')
            ->setDescription('Label locations')
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
        $pdo = $this->getPdo($config['pdo']);
        $writer = new PdoWriter($pdo, 'location');
        $stmt = $pdo->prepare(
            "SELECT * FROM location"
        );
        $res = $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $id = $row['id'];
            $stamp = $row['stamp'];
            $latitude = $row['latitude'];
            $longitude = $row['longitude'];
            $label = $this->resolveLabel($config, $latitude, $longitude);

            echo "#$id: " . date('d/M/Y H:i', $stamp) . ": $latitude, $longitude = $label\n";
            $writer->update(['id'=>$id], ['label'=>$label]);
        }
        exit("Done!\n");
    }
}
