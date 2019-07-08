<?php


namespace MService\Payment\Shared\Utils;

use MService\Payment\Shared\SharedModels\Log;

class HttpClient
{
    public static function HTTPPost($url, string $payload)
    {
        $logger = (new Log())->getLogger();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=UTF-8"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $result = curl_exec($ch);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $logger->info('[HTTP Post to MoMoServer] Endpoint: ' . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
            . ', RequestBody: ' . $payload);
        $logger->info('[HTTP Response from MoMoServer] HttpStatusCode: ' . $statusCode
            . ', ResponseBody: ' . $result);

        curl_close($ch);

        return new HttpResponse($statusCode, $result);
    }
}
