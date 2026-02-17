<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CustomerService;
use App\Services\SettingService;
use Mockery;

class CustomerServiceTest extends TestCase
{
    protected $customerService;
    protected $settingServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settingServiceMock = Mockery::mock(SettingService::class);
        $this->customerService = new CustomerService($this->settingServiceMock);
    }

    public function test_calculate_points_earned_standard_rate()
    {
        // Rate: 10000:1 (Rp 10k = 1 point)
        $this->settingServiceMock->shouldReceive('getBool')
            ->with('customer.loyalty_enabled', true)
            ->andReturn(true);

        $this->settingServiceMock->shouldReceive('get')
            ->with('customer.points_earn_rate', '10000:1')
            ->andReturn('10000:1');

        $this->settingServiceMock->shouldReceive('getInt')
            ->with('customer.points_min_transaction', 0)
            ->andReturn(0);

        // Rp 50.000 -> 5 points
        $points = $this->customerService->calculatePointsEarned(50000);
        $this->assertEquals(5, $points);

        // Rp 55.000 -> 5 points (floor)
        $points = $this->customerService->calculatePointsEarned(55000);
        $this->assertEquals(5, $points);
    }

    public function test_calculate_points_below_min_transaction()
    {
        $this->settingServiceMock->shouldReceive('getBool')
            ->with('customer.loyalty_enabled', true)
            ->andReturn(true);

        $this->settingServiceMock->shouldReceive('get')
            ->with('customer.points_earn_rate', '10000:1')
            ->andReturn('10000:1');

        $this->settingServiceMock->shouldReceive('getInt')
            ->with('customer.points_min_transaction', 0)
            ->andReturn(20000); // Min Rp 20k

        // Rp 10.000 -> 0 points
        $points = $this->customerService->calculatePointsEarned(10000);
        $this->assertEquals(0, $points);
    }

    public function test_calculate_points_loyalty_disabled()
    {
        $this->settingServiceMock->shouldReceive('getBool')
            ->with('customer.loyalty_enabled', true)
            ->andReturn(false);

        $points = $this->customerService->calculatePointsEarned(50000);
        $this->assertEquals(0, $points);
    }

    public function test_convert_points_to_discount()
    {
        // Rate: 100:10000 (100 points = Rp 10k)
        $this->settingServiceMock->shouldReceive('get')
            ->with('customer.points_redeem_rate', '100:10000')
            ->andReturn('100:10000');

        // 200 points -> Rp 20.000
        $discount = $this->customerService->convertPointsToDiscount(200);
        $this->assertEquals(20000, $discount);

        // 150 points -> Rp 10.000 (floor to nearest 100 block)
        $discount = $this->customerService->convertPointsToDiscount(150);
        $this->assertEquals(10000, $discount);
    }
}
