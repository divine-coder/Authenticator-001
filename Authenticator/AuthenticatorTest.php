<?php

/*Loading once*/
require_once __DIR__ . '/Authenticator.php';

class GoogleAuthenticatorTest extends PHPUnit_Framework_TestCase
{
    /* @var $googleAuthenticator Authenticator */
    protected $googleAuthenticator;

    protected function setUp()
    {
        $this->googleAuthenticator = new Authenticator();
    }

    public function codeProvider()
    {
        // Secret, time, code
        return array(
            array(
                'SECRET',
                '0',
                '200470'
            ) ,
            array(
                'SECRET',
                '1385909245',
                '780018'
            ) ,
            array(
                'SECRET',
                '1378934578',
                '705013'
            ) ,
        );
    }

    public function testItCanBeInstantiated()
    {
        $ga = new Authenticator();

        $this->assertInstanceOf('Authenticator', $ga);
    }

    public function testCreateSecretDefaultsToSixteenCharacters()
    {
        $ga = $this->googleAuthenticator;
        $secret = $ga->createSecret();

        $this->assertEquals(strlen($secret) , 16);
    }

    public function testCreateSecretLengthCanBeSpecified()
    {
        $ga = $this->googleAuthenticator;

        for ($secretLength = 16;$secretLength < 100;++$secretLength)
        {
            $secret = $ga->createSecret($secretLength);

            $this->assertEquals(strlen($secret) , $secretLength);
        }
    }

    /**
     * @dataProvider codeProvider
     */
    public function testGetCodeReturnsCorrectValues($secret, $timeSlice, $code)
    {
        $generatedCode = $this
            ->googleAuthenticator
            ->getCode($secret, $timeSlice);

        $this->assertEquals($code, $generatedCode);
    }

    public function testGetQRCodeGoogleUrlReturnsCorrectUrl()
    {
        $secret = 'SECRET';
        $name = 'Test';
        $url = $this
            ->googleAuthenticator
            ->getQRCodeGoogleUrl($name, $secret);
        $urlParts = parse_url($url);

        parse_str($urlParts['query'], $queryStringArray);

        $this->assertEquals($urlParts['scheme'], 'https');
        $this->assertEquals($urlParts['host'], 'api.qrserver.com');
        $this->assertEquals($urlParts['path'], '/v1/create-qr-code/');

        $expectedChl = 'otpauth://totp/' . $name . '?secret=' . $secret;

        $this->assertEquals($queryStringArray['data'], $expectedChl);
    }

    public function testVerifyCode()
    {
        $secret = 'SECRET';
        $code = $this
            ->googleAuthenticator
            ->getCode($secret);
        $result = $this
            ->googleAuthenticator
            ->verifyCode($secret, $code);

        $this->assertEquals(true, $result);

        $code = 'INVALIDCODE';
        $result = $this
            ->googleAuthenticator
            ->verifyCode($secret, $code);

        $this->assertEquals(false, $result);
    }

    public function testVerifyCodeWithLeadingZero()
    {
        $secret = 'SECRET';
        $code = $this
            ->googleAuthenticator
            ->getCode($secret);
        $result = $this
            ->googleAuthenticator
            ->verifyCode($secret, $code);
        $this->assertEquals(true, $result);

        $code = '0' . $code;
        $result = $this
            ->googleAuthenticator
            ->verifyCode($secret, $code);
        $this->assertEquals(false, $result);
    }

    public function testSetCodeLength()
    {
        $result = $this
            ->googleAuthenticator
            ->setCodeLength(6);

        $this->assertInstanceOf('Authenticator', $result);
    }
}

