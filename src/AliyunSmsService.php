<?php

namespace KagaDorapeko\Laravel\Aliyun\Sms;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AliyunSmsService
{
    protected array $config;

    protected PendingRequest $apiClient;

    protected string $apiUrl = 'https://dysmsapi.aliyuncs.com';

    public function __construct()
    {
        $this->refreshConfig();

        $this->apiClient = Http::retry(1)->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
    }

    public function refreshConfig()
    {
        $this->config = config('aliyun-sms');
    }

    public function send(string $phone, string $templateCode, array $templateParams = []): bool
    {
        $queryStr = $this->getQueryStr([
            'template_params' => json_encode($templateParams),
            'access_key_id' => $this->config['access_key_id'],
            'template_code' => $templateCode,
            'phone' => $phone,
        ]);

        $sign = $this->getSign($queryStr);

        $response = $this->apiClient->post("$this->apiUrl/?Signature=$sign&$queryStr");

        if ($response->successful() and $response->json('Code') === 'OK') {
            return true;
        }

        return false;
    }

    protected function getSign(string $queryStr): string
    {
        $signContent = 'POST&%2F&' . rawurlencode($queryStr);

        return urlencode(base64_encode(hash_hmac(
            'sha1', $signContent, "{$this->config['access_key_secret']}&", true
        )));
    }

    protected function getQueryStr(array $params): string
    {
        $query = [
            'AccessKeyId' => $this->config['access_key_id'],
            'Action' => 'SendSms',
            'Format' => 'json',
            'PhoneNumbers' => $params['phone'],
            'RegionId' => 'cn-shenzhen',
            'SignName' => 'Lovebook',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => md5(Str::uuid()),
            'SignatureVersion' => '1.0',
            'TemplateCode' => $params['template_code'],
            'TemplateParam' => $params['template_params'],
            'Timestamp' => now()->toIso8601ZuluString(),
            'Version' => '2017-05-25',
        ];

        ksort($query);

        return http_build_query($query);
    }
}
