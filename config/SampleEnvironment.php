<?php

use Dotenv\Dotenv;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\MoMoException;

include_once '../vendor/autoload.php';

$dotenv = Dotenv::create(__DIR__ . "/..");
$dotenv->load();

class SampleEnvironment
{
    public static function selectEnv($target = "dev")
    {
        switch ($target) {
            case "dev":
                $devInfo = new PartnerInfo($_ENV['DEV_ACCESS_KEY'], $_ENV['DEV_PARTNER_CODE'], $_ENV['DEV_SECRET_KEY']);
                $dev = new Environment($_ENV['DEV_MOMO_ENDPOINT'], $devInfo, $_ENV['DEV']);
                return $dev;

            case "prod":
                $productionInfo = new PartnerInfo($_ENV['PROD_ACCESS_KEY'], $_ENV['PROD_PARTNER_CODE'], $_ENV['PROD_SECRET_KEY']);
                $production = new Environment($_ENV['PROD_MOMO_ENDPOINT'], $productionInfo, $_ENV['PROD']);
                return $production;

            default:
                throw new MoMoException("MoMo doesnt provide other environment: dev and prod");
        }
    }
}

var_dump(SampleEnvironment::selectEnv('dev'));