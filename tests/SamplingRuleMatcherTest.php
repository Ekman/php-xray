<?php
namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;

class SamplingRuleMatcherTest extends TestCase
{
    /** @var SamplingRuleMatcher */
    private $samplingRuleMatcher;
    
    protected function setUp()
    {
        parent::setUp();
        $this->samplingRuleMatcher = new SamplingRuleMatcher();
    }
    
    /** @dataProvider provideMatch */
    public function testMatch($trace, $samplingRule, $expected)
    {
        $this->assertEquals($expected, $this->samplingRuleMatcher->match($trace, $samplingRule));
    }
    
    public function provideMatch()
    {
        return [
            [
                (new Trace())
                    ->setUrl("https://example.com/path")
                    ->setMethod("GET")
                    ->setName("application"),
                [
                    "HTTPMethod" => "GET",
                    "Host" => "example.com",
                    "URLPath" => "/path",
                    "ServiceName" => "app*",
                    "ServiceType" => "*"
                ],
                true
            ]            
        ];
    }
    
    /** @dataProvider provideMatchFirst */
    public function testMatchFirst($trace, $samplingRules, $expected)
    {
        $this->assertEquals($expected, $this->samplingRuleMatcher->matchFirst($trace, $samplingRules));
    }
    
    public function provideMatchFirst()
    {
        return [
            [
                (new Trace())
                    ->setUrl("https://example.com/path")
                    ->setMethod("GET"),
                [
                    [
                        "Priority" => 1000,
                        "HTTPMethod" => "GET",
                        "Host" => "example.com",
                        "URLPath" => "/path",
                        "RuleName" => "Default",
                        "ServiceName" => "*",
                        "ServiceType" => "*"
                    ],
                    [
                        "Priority" => 1,
                        "HTTPMethod" => "GET",
                        "Host" => "*",
                        "URLPath" => "/any/path",
                        "RuleName" => "Not matching",
                        "ServiceName" => "*",
                        "ServiceType" => "*"
                    ],
                    [
                        "Priority" => 5,
                        "HTTPMethod" => "GET",
                        "Host" => "*",
                        "URLPath" => "/path",
                        "RuleName" => "Important",
                        "ServiceName" => "*",
                        "ServiceType" => "*"
                    ]
                ],
                [
                    "Priority" => 5,
                    "HTTPMethod" => "GET",
                    "Host" => "*",
                    "URLPath" => "/path",
                    "RuleName" => "Important",
                    "ServiceName" => "*",
                    "ServiceType" => "*"
                ]
            ]
        ];
    }
}

