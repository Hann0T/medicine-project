<?php

namespace App\Services;

use App\Contracts\Scraper;
use App\Jobs\StoreMedicine;
use Illuminate\Support\Facades\Http;
use Spekulatius\PHPScraper\PHPScraper;

class InkafarmaScraper implements Scraper
{
    private string $storeName = 'inkafarma';

    private string $storeDomain = 'https://inkafarma.pe';

    private string $url = 'https://5doa19p9r7.execute-api.us-east-1.amazonaws.com/PROD/products/';

    public function scrape(): array
    {
        $maxLength = 6;
        $id = 0;

        while ($id < 999999) {
            $id += 1;
            $id = (string) $id;
            $times = $maxLength - strlen($id);
            $id = str_repeat("0", $times) . $id;
            $response = Http::get($this->url . $id);
            $data = $response->json();
            if ($data) {
                StoreMedicine::dispatch([
                    'id' => $data[0]['id'],
                    'name' => $data[0]['name'],
                    'long_description' => $data[0]['longDescription'],
                    'description' => $data[0]['shortDescription'],
                    'brand' => $data[0]['brand'],
                    'composition' => isset($data[0]['details'])
                        ? array_filter($data[0]['details'], fn ($detail) => $detail['key'] == 'composition')[0]['content']
                        : null,
                    'store_name' => $this->storeName,
                    'image_url' => isset($data[0]['imageList'])
                        ? $data[0]['imageList'][0]['url']
                        : null,
                    'url' => $this->storeDomain . '/product/' . $data[0]['slug'] . '/' . $data[0]['id']
                ], [

                    [
                        'name' => $data[0]['presentation'],
                        'regular_price' => $data[0]['price'],
                        'price' => $data[0]['priceAllPaymentMethod'],
                        'offer_price' => $data[0]['priceWithpaymentMethod'],
                    ],
                    isset($data[0]['fractionalMode']) ?
                        [
                            'name' => $data[0]['fractionatedForm'],
                            'price' => $data[0]['fractionatedPrice'],
                        ]
                        : null
                ]);
            }
        }
        return [];
    }
}
