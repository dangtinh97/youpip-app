<?php

namespace App\Helper;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class WhatsCallerHelper
{
    const REGEX_PHONE_VN = '/(84|0[2|3|5|7|8|9])+([0-9]{8,10})/';

    /**
     * @param string $mobile
     *
     * @return string|null
     */
    public static function findPhone(string $mobile): ?string
    {
        $baseUrl = env('URL_WHATSCALLME');
        try {
            $headers = [
                'gmt' => '420',
                'lang' => 'vi',
                'os-name' => 'ANDROID'
            ];

            $response = Http::withHeaders($headers)->get("{$baseUrl}/api/fake-login?key=03121997");
            $token = Arr::get($response->json(), 'data.token');
            $headers['Authorization'] = 'Bearer '.$token;
            $find = Http::withHeaders($headers)
                ->get("{$baseUrl}/api/search-a-phone-number?phoneNumber={$mobile}")->json();

            return Arr::get($find, 'data.list.0.fullname');
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $mobile
     *
     * @return string|null
     */
    public static function phoneVn(string $mobile): ?string
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) < 9) {
            return null;
        }
        if (strlen($mobile) === 9) {
            $mobile = "0".$mobile;
        }

        $mobile = (string)preg_replace(["/^84/", "/^(0|84)+16/"], ["0", "03"], $mobile);
        preg_match(self::REGEX_PHONE_VN, $mobile, $matches);
        if (count($matches) === 3) {
            return $matches[0];
        }

        return null;
    }
}
