<?php

namespace Tests\Unit;

use App\Services\FinancialReportingService;
use PHPUnit\Framework\TestCase;

class FinancialReportingAccountingTest extends TestCase
{
    public function test_debit_and_credit_normal_account_rules_are_consistent(): void
    {
        $service = new FinancialReportingService();

        $this->assertTrue($service->isDebitNormal('1000', 'asset'));
        $this->assertTrue($service->isDebitNormal('5100', 'expense'));
        $this->assertTrue($service->isDebitNormal('4150', 'revenue'));
        $this->assertTrue($service->isDebitNormal('4990', 'contra revenue'));
        $this->assertFalse($service->isDebitNormal('2000', 'liability'));
        $this->assertFalse($service->isDebitNormal('3000', 'equity'));
    }
}
