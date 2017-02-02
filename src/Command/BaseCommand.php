<?php

namespace iTrack\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use AnthonyMartin\GeoLocation\GeoLocation as GeoLocation;
use RuntimeException;
use PDO;

abstract class BaseCommand extends Command
{
    protected function loadConfig(InputInterface $input)
    {
        $configFilename = $input->getOption('config');
        if (!$configFilename) {
            $configFilename = 'config.yml';
        }
        if (!file_exists($configFilename)) {
            throw new RuntimeException("Config file not found: " . $configFilename);
        }
        $configYml = file_get_contents($configFilename);
        $config = Yaml::parse($configYml);
        return $config;
    }
    
    public function resolveLabel($config, $latitude, $longitude)
    {
        $loc = GeoLocation::fromDegrees($latitude, $longitude);
        foreach ($config['locations'] as $key => $details) {
            $loc2 = GeoLocation::fromDegrees($details['latitude'], $details['longitude']);
            $distance = round($loc->distanceTo($loc2, 'kilometers'), 3);
            /*
            echo "  Distance: " . $key . '/' .
                $distance . " kilometers \n";
            */
            if ($distance < $details['radius']) {
                return $key;
            }
        }
        return null;
    }
    
    public function getPdo($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $user = parse_url($url, PHP_URL_USER);
        $pass = parse_url($url, PHP_URL_PASS);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $dbname = parse_url($url, PHP_URL_PATH);
        if (!$port) {
            $port = 3306;
        }

        $dsn = sprintf(
            '%s:dbname=%s;host=%s;port=%d',
            $scheme,
            substr($dbname, 1),
            $host,
            $port
        );
        //echo $dsn;exit();

        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
