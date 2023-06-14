<?php


namespace App\Services\Lazada;


class LazadaService
{
    public function check_char_exist_str($str, $char)
    {
        if (strpos($str, $char) !== false) {
            return true;
        }
        return false;
    }

    public function matchObjectIdFromLazadaLink($link, $type = 'follow')
    {
        if (strpos($link, "s.lazada")) {
            $link = str_replace("http://", "https://", $link);
            if (strpos($link, "s.lazada")) {
                $link = $this->getRedirectUrl($link);
                $re = '/https:\/\/s.lazada.vn\/(.*)/m';
                preg_match($re, $link, $matches, PREG_OFFSET_CAPTURE, 0);
                if (isset($matches[1][0])) return $matches[1][0];
            }
        }
        if ($type == 'follow') {
            $re = '/https:\/\/www.lazada.vn\/shop\/(.*)/m';
            $str = $this->reconstructUrl($link);
            preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
            if (!isset($matches[0][1])) {
                return [
                    'error' => 'Link phải có dạng https://www.lazada.vn/shop/{username_shop}...',
                    'status' => 400
                ];
            }
            if ($this->check_char_exist_str($matches[0][1], "http")) {
                return [
                    'error' => 'Link phải có dạng https://www.lazada.vn/shop/{username_shop}...',
                    'status' => 400
                ];
            }
            return str_replace('/', '', $matches[0][1]);
        }

        if ($type == 'like') {
            $re = '/https:\/\/www.lazada.vn\/products\/(.*)/m';
            $str = $this->reconstructUrl($link);
            preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
            if (!isset($matches[0][1])) {
                return [
                    'error' => 'Link phải có dạng https://www.lazada.vn/products/{alias_san_pham}...',
                    'status' => 400
                ];
            }
            return str_replace('.html', '', $matches[0][1]);
        }
        return [
            'error' => 'Link không đúng định dạng',
            'status' => 400
        ];
    }


    private function reconstructUrl($url)
    {
        try {
            $url_parts = parse_url($url);
            if (isset($url_parts['scheme'])) {
                return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
            }
            return $url;
        } catch (\Exception $exception) {
            return $url;
        }
    }


    /**
     * get_redirect_url()
     * Gets the address that the provided URL redirects to,
     * or FALSE if there's no redirect.
     *
     * @param string $url
     * @return string
     */
    protected function getRedirectUrl($url)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseHeaders = curl_exec($ch);
        curl_close($ch);
        if (preg_match_all("/^location: (.*?)$/im", $responseHeaders, $results))
            return $results[1][0];
        return $url;
    }
}
