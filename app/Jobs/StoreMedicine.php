<?php

namespace App\Jobs;

use App\Models\Medicine;
use App\Models\Presentation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreMedicine implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $medicine,
        public array $presentations
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $medicine = Medicine::updateOrCreate(
            [
                'code' => $this->medicine['id'],
            ],
            [
                'name' => $this->medicine['name'],
                'image_url' => $this->medicine['image_url'],
                'description' => $this->medicine['description'],
                'long_description' => $this->medicine['long_description'],
                'composition' => $this->medicine['composition'],
                'store_name' => $this->medicine['store_name'],
                'url' => $this->medicine['url'],
            ]
        );

        array_map(function ($presentation) use ($medicine) {
            $medicine->presentations()->updateOrCreate(
                ['name' => $presentation['name']],
                [
                    'regular_price' => isset($presentation['regular_price'])
                        ? floatval($presentation['regular_price'])
                        : null,
                    'price' => isset($presentation['price'])
                        ? floatval($presentation['price'])
                        : null,
                    'offer_price' => isset($presentation['offer_price'])
                        ? floatval($presentation['offer_price'])
                        : null,
                ]
            );
        }, $this->presentations);
    }
}
