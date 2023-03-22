<?php

namespace Tests\Feature;

use App\Jobs\StoreMedicine;
use App\Models\Medicine;
use App\Models\Presentation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreMedicineJobTest extends TestCase
{
    use RefreshDatabase;

    protected array $medicineData = [
        'id' => '012345',
        'name' => 'medicine 1',
        'long_description' => 'long description for medicine 1',
        'description' => 'description',
        'brand' => 'medicine brand',
        'composition' => 'composition of medicine 1',
        'store_name' => 'store 1',
        'image_url' => 'https://medicine.com/images/medicine1.jpg',
        'url' => 'https://medicine.com/prod/012345'
    ];

    protected array $presentationsData = [
        [
            'name' => 'bottle',
            'regular_price' => '12.90',
            'price' => '10.80',
            'offer_price' => '0',
        ],
        [
            'name' => 'unit',
            'price' => '2.80',
        ],
    ];

    /** @test */
    public function the_job_should_store_the_medicine(): void
    {
        $job = new StoreMedicine($this->medicineData, $this->presentationsData);

        $this->assertEquals(0, Medicine::count());
        $job->handle();

        $medicine = Medicine::first();
        $this->assertEquals(1, Medicine::count());
        $this->assertEquals($this->medicineData['name'], $medicine->name);
        $this->assertEquals($this->medicineData['id'], $medicine->code);
    }

    /** @test */
    public function the_job_should_store_the_presentations(): void
    {
        $job = new StoreMedicine($this->medicineData, $this->presentationsData);

        $this->assertEquals(0, Presentation::count());
        $job->handle();

        $this->assertEquals(2, Presentation::count());

        $presentation = Presentation::first();
        $this->assertEquals($this->presentationsData[0]['name'], $presentation->name);
        $this->assertEquals($this->presentationsData[0]['regular_price'], $presentation->regular_price);
        $this->assertEquals($this->presentationsData[0]['price'], $presentation->price);
        $this->assertEquals($this->presentationsData[0]['offer_price'], $presentation->offer_price);

        $presentation = Presentation::get()->last();
        $this->assertEquals($this->presentationsData[1]['name'], $presentation->name);
        $this->assertEquals($this->presentationsData[1]['price'], $presentation->price);
    }
}
