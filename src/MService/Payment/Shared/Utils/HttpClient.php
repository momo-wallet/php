<?php


namespace MService\Payment\Shared\Utils;

class HttpClient
{
    public static function HTTPPost($url, $uri, string $payload)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=UTF-8"));
        curl_setopt($ch, CURLOPT_URL, $url . $uri);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $result = curl_exec($ch);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        echo 'sendToMoMoServer::Endpoint::', curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), "\n";
        echo 'sendToMoMoServer::RequestBody::', $payload, "\n";
        echo 'sendToMoMoServer::HttpStatusCode::', $statusCode, "\n";
        echo 'sendToMoMoServer::ResponseBody::', $result, "\n";
        curl_close($ch);

        return new HttpResponse($statusCode, $result);
    }
}