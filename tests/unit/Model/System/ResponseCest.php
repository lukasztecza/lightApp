<?php
use LightApp\Model\System\Response;
use Codeception\Example;

class ResponseCest
{
    public $response;

    public function _before()
    {
        $this->response = new Response(
            '/templates/home.php',
            [
                'templateText' => '<p>Some text</p>',
                'templateVars' => [
                    'var1' => 'sanitize $% me',
                    'var2' => 'leave @# raw'
                ]
        
            ],
            [
                'templateText' => 'html',
                'templateVars.var2' => 'raw'
            ],
            ['someHeader' => '654'],
            [
                [
                    'name' => 'cookie1',
                    'value' => 321
                ],
                [
                    'name' => 'cookie2',
                    'value' => 123
                ]
            ]
        );
    }

    public function _after()
    {
    }

    /**
     * @dataProvider fileEscapeValueProvider
     */
    public function fileEscapeValueTest(UnitTester $I, Example $example)
    {
        $value = $example[0];
        $result = $I->callNonPublic($this->response, 'fileEscapeValue', [&$value]);
        $I->assertEquals($example[1], $value);
    }

    private function fileEscapeValueProvider()
    {
        return [
            ['/directory/path.php', '/directory/path.php'],
            ['/fileonly.php', '/fileonly.php'],
            ['/../cofig.php', '/cofig.php']
        ];
    }

    /**
     * @dataProvider urlEscapeValueProvider
     */
    public function urlEscapeValueTest(UnitTester $I, Example $example)
    {
        $value = $example[0];
        $result = $I->callNonPublic($this->response, 'urlEscapeValue', [&$value]);
        $I->assertEquals($example[1], $value);
    }

    private function urlEscapeValueProvider()
    {
        return [
            ['http://www.somesite.com?one=1&two=2&multi[]=some&multi=another', 'http%3A%2F%2Fwww.somesite.com%3Fone%3D1%26two%3D2%26multi%5B%5D%3Dsome%26multi%3Danother']
        ];
    }

    /**
     * @dataProvider htmlEscapeValueProvider
     */
    public function htmlEscapeValueTest(UnitTester $I, Example $example)
    {
        $value = $example[0];
        $result = $I->callNonPublic($this->response, 'htmlEscapeValue', [&$value]);
        $I->assertEquals($example[1], $value);
    }

    private function htmlEscapeValueProvider()
    {
        return [
            ['some text', 'some text'],
            ['<p>allowed tag</p>', '<p>allowed tag</p>'],
            ['<a href="some_url">not allowed tag</a>', '&lt;a href=&quot;some_url&quot;&gt;not allowed tag&lt;/a&gt;']
        ];
    }

    /**
     * @dataProvider sanitizeValueProvider
     */
    public function sanitizeValueTest(UnitTester $I, Example $example)
    {
        $value = $example[0];
        $result = $I->callNonPublic($this->response, 'sanitizeValue', [&$value]);
        $I->assertEquals($example[1], $value);
    }

    private function sanitizeValueProvider()
    {
        return [
            ['alert(function.call())', 'alertfunctioncall'],
            ['<img src="some.dangerous.site" />', 'imgsrcsomedangeroussite'],
        ];
    }

    public function getFileTest(UnitTester $I)
    {
        $I->assertEquals($this->response->getFile(), '/templates/home.php');
    }

    public function getVariablesTest(UnitTester $I)
    {
        $variables = $this->response->getVariables(['templateText', 'templateVars.var2']);
        $I->assertEquals($variables['templateText'], '<p>Some text</p>');
        $I->assertEquals($variables['templateVars']['var1'], 'sanitizeme');
        $I->assertEquals($variables['templateVars']['var2'], 'leave @# raw');
    }

    public function getHeadersTest(UnitTester $I)
    {
        $I->assertEquals($this->response->getHeaders(), ['someHeader' => '654']);
    }

    public function getCookiesTest(UnitTester $I)
    {
        $I->assertEquals($this->response->getCookies(), [
            [
                'name' => 'cookie1',
                'value' => 321,
                'expire' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true
            ],
            [
                'name' => 'cookie2',
                'value' => 123,
                'expire' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true
            ]
        ]);
    }
}
