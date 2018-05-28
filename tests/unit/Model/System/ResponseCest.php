<?php
use TinyAppBase\Model\System\Response;
use Codeception\Example;

class ResponseCest
{
    public $response;

    private function callNonPublic($object, string $method, array $params)
    {
        return (function () use ($object, $method, $params) {
            return call_user_func_array([$object, $method], $params);
        })->bindTo($object, $object)();
    }

    private function callNonPublicByRefetence($object, string $method, &$param)
    {
        (function () use ($object, $method, &$param) {
            $object->$method($param);
        })->bindTo($object, $object)();
    }

    public function _before()
    {
        $this->response = new Response(
            '/templates/home.php',
            [
                'templateText' => '<p>Some text</p>',
                'templateLinks' => [
                    'link1' => 'someLink1',
                    'link2' => 'someLink2',
                    'link3' => 'someLink3'
                ]
        
            ],
            ['templateText' => 'html'],
            ['formParam' => '789'],
            ['someCookie' => 654]
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
        $result = $this->callNonPublicByRefetence($this->response, 'fileEscapeValue', $value);
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
        $result = $this->callNonPublicByRefetence($this->response, 'urlEscapeValue', $value);
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
        $result = $this->callNonPublicByRefetence($this->response, 'htmlEscapeValue', $value);
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
        $result = $this->callNonPublicByRefetence($this->response, 'sanitizeValue', $value);
        $I->assertEquals($example[1], $value);
    }

    private function sanitizeValueProvider()
    {
        return [
            ['alert(function.call())', 'alertfunctioncall'],
            ['<img src="some.dangerous.site" />', 'imgsrcsomedangeroussite'],
        ];
    }

    public function getVariablesTest()
    {
        $this->response;
    }

}
