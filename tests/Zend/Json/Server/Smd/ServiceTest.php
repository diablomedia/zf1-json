<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Json_Server
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Test class for Zend_Json_Server_Smd_Service
 *
 * @category   Zend
 * @package    Zend_Json_Server
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Json
 * @group      Zend_Json_Server
 */
class Zend_Json_Server_Smd_ServiceTest extends PHPUnit\Framework\TestCase
{
    protected $service;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->service = new Zend_Json_Server_Smd_Service('foo');
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown(): void
    {
    }

    public function testConstructorShouldThrowExceptionWhenNoNameSet()
    {
        try {
            $service = new Zend_Json_Server_Smd_Service(null);
            $this->fail('Should throw exception when no name set');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('requires a name', $e->getMessage());
        }

        try {
            $service = new Zend_Json_Server_Smd_Service(array());
            $this->fail('Should throw exception when no name set');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('requires a name', $e->getMessage());
        }
    }

    public function testSettingNameShouldThrowExceptionWhenContainingInvalidFormat()
    {
        try {
            $this->service->setName('0ab-?');
            $this->fail('Invalid name should throw exception');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('Invalid name', $e->getMessage());
        }
        try {
            $this->service->setName('ab-?');
            $this->fail('Invalid name should throw exception');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('Invalid name', $e->getMessage());
        }
    }

    public function testNameAccessorsShouldWorkWithNormalInput()
    {
        $this->assertEquals('foo', $this->service->getName());
        $this->service->setName('bar');
        $this->assertEquals('bar', $this->service->getName());
    }

    public function testTransportShouldDefaultToPost()
    {
        $this->assertEquals('POST', $this->service->getTransport());
    }

    public function testTransportShouldBeLimitedToPost()
    {
        try {
            $this->service->setTransport('GET');
            $this->fail('Invalid transport should throw exception');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('Invalid transport', $e->getMessage());
        }
        try {
            $this->service->setTransport('REST');
            $this->fail('Invalid transport should throw exception');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('Invalid transport', $e->getMessage());
        }
    }

    public function testTransportAccessorsShouldWorkUnderNormalInput()
    {
        $this->service->setTransport('POST');
        $this->assertEquals('POST', $this->service->getTransport());
    }

    public function testTargetShouldBeNullInitially()
    {
        $this->assertNull($this->service->getTarget());
    }

    public function testTargetAccessorsShouldWorkUnderNormalInput()
    {
        $this->testTargetShouldBeNullInitially();
        $this->service->setTarget('foo');
        $this->assertEquals('foo', $this->service->getTarget());
    }

    public function testTargetAccessorsShouldNormalizeToString()
    {
        $this->testTargetShouldBeNullInitially();
        $this->service->setTarget(123);
        $value = $this->service->getTarget();
        $this->assertIsString($value);
        $this->assertEquals((string) 123, $value);
    }

    public function testEnvelopeShouldBeJsonRpc1CompliantByDefault()
    {
        $this->assertEquals(Zend_Json_Server_Smd::ENV_JSONRPC_1, $this->service->getEnvelope());
    }

    public function testEnvelopeShouldOnlyComplyWithJsonRpc1And2()
    {
        $this->testEnvelopeShouldBeJsonRpc1CompliantByDefault();
        $this->service->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2);
        $this->assertEquals(Zend_Json_Server_Smd::ENV_JSONRPC_2, $this->service->getEnvelope());
        $this->service->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_1);
        $this->assertEquals(Zend_Json_Server_Smd::ENV_JSONRPC_1, $this->service->getEnvelope());
        try {
            $this->service->setEnvelope('JSON-P');
            $this->fail('Should not be able to set non-JSON-RPC spec envelopes');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('Invalid envelope', $e->getMessage());
        }
    }

    public function testShouldHaveNoParamsByDefault()
    {
        $params = $this->service->getParams();
        $this->assertEmpty($params);
    }

    public function testShouldBeAbleToAddParamsByTypeOnly()
    {
        $this->service->addParam('integer');
        $params = $this->service->getParams();
        $this->assertCount(1, $params);
        $param = array_shift($params);
        $this->assertEquals('integer', $param['type']);
    }

    public function testParamsShouldAcceptArrayOfTypes()
    {
        $type = array('integer', 'string');
        $this->service->addParam($type);
        $params = $this->service->getParams();
        $param  = array_shift($params);
        $test   = $param['type'];
        $this->assertIsArray($test);
        $this->assertEquals($type, $test);
    }

    public function testInvalidParamTypeShouldThrowException()
    {
        try {
            $this->service->addParam(new stdClass);
            $this->fail('Invalid param type should throw exception');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('Invalid param type', $e->getMessage());
        }
    }

    public function testShouldBeAbleToOrderParams()
    {
        $this->service->addParam('integer', array(), 4)
                      ->addParam('string')
                      ->addParam('boolean', array(), 3);
        $params = $this->service->getParams();

        $this->assertCount(3, $params);

        $param = array_shift($params);
        $this->assertEquals('string', $param['type'], var_export($params, 1));
        $param = array_shift($params);
        $this->assertEquals('boolean', $param['type'], var_export($params, 1));
        $param = array_shift($params);
        $this->assertEquals('integer', $param['type'], var_export($params, 1));
    }

    public function testShouldBeAbleToAddArbitraryParamOptions()
    {
        $this->service->addParam(
            'integer',
            array(
                'name'        => 'foo',
                'optional'    => false,
                'default'     => 1,
                'description' => 'Foo parameter',
            )
        );
        $params = $this->service->getParams();
        $param  = array_shift($params);
        $this->assertEquals('foo', $param['name']);
        $this->assertFalse($param['optional']);
        $this->assertEquals(1, $param['default']);
        $this->assertEquals('Foo parameter', $param['description']);
    }

    public function testShouldBeAbleToAddMultipleParamsAtOnce()
    {
        $this->service->addParams(array(
            array('type' => 'integer', 'order' => 4),
            array('type' => 'string', 'name' => 'foo'),
            array('type' => 'boolean', 'order' => 3),
        ));
        $params = $this->service->getParams();

        $this->assertCount(3, $params);
        $param = array_shift($params);
        $this->assertEquals('string', $param['type']);
        $this->assertEquals('foo', $param['name']);

        $param = array_shift($params);
        $this->assertEquals('boolean', $param['type']);

        $param = array_shift($params);
        $this->assertEquals('integer', $param['type']);
    }

    public function testSetparamsShouldOverwriteExistingParams()
    {
        $this->testShouldBeAbleToAddMultipleParamsAtOnce();
        $params = $this->service->getParams();
        $this->assertCount(3, $params);

        $this->service->setParams(array(
            array('type' => 'string'),
            array('type' => 'integer'),
        ));
        $test = $this->service->getParams();
        $this->assertNotEquals($params, $test);
        $this->assertCount(2, $test);
    }

    public function testReturnShouldBeNullByDefault()
    {
        $this->assertNull($this->service->getReturn());
    }

    public function testReturnAccessorsShouldWorkWithNormalInput()
    {
        $this->testReturnShouldBeNullByDefault();
        $this->service->setReturn('integer');
        $this->assertEquals('integer', $this->service->getReturn());
    }

    public function testReturnAccessorsShouldAllowArrayOfTypes()
    {
        $this->testReturnShouldBeNullByDefault();
        $type = array('integer', 'string');
        $this->service->setReturn($type);
        $this->assertEquals($type, $this->service->getReturn());
    }

    public function testInvalidReturnTypeShouldThrowException()
    {
        try {
            $this->service->setReturn(new stdClass);
            $this->fail('Invalid return type should throw exception');
        } catch (Zend_Json_Server_Exception $e) {
            $this->assertStringContainsString('Invalid param type', $e->getMessage());
        }
    }

    public function testToArrayShouldCreateSmdCompatibleHash()
    {
        $this->setupSmdValidationObject();
        $smd = $this->service->toArray();
        $this->validateSmdArray($smd);
    }

    public function testTojsonShouldEmitJson()
    {
        $this->setupSmdValidationObject();
        $json = $this->service->toJson();
        $smd  = Zend_Json::decode($json);

        $this->assertArrayHasKey('foo', $smd);
        $this->assertIsArray($smd['foo']);

        $this->validateSmdArray($smd['foo']);
    }

    public function setupSmdValidationObject()
    {
        $this->service->setName('foo')
                      ->setTransport('POST')
                      ->setTarget('/foo')
                      ->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2)
                      ->addParam('boolean')
                      ->addParam('array')
                      ->addParam('object')
                      ->setReturn('boolean');
    }

    public function validateSmdArray(array $smd)
    {
        $this->assertArrayHasKey('transport', $smd);
        $this->assertEquals('POST', $smd['transport']);

        $this->assertArrayHasKey('envelope', $smd);
        $this->assertEquals(Zend_Json_Server_Smd::ENV_JSONRPC_2, $smd['envelope']);

        $this->assertArrayHasKey('parameters', $smd);
        $params = $smd['parameters'];
        $this->assertCount(3, $params);
        $param = array_shift($params);
        $this->assertEquals('boolean', $param['type']);
        $param = array_shift($params);
        $this->assertEquals('array', $param['type']);
        $param = array_shift($params);
        $this->assertEquals('object', $param['type']);

        $this->assertArrayHasKey('returns', $smd);
        $this->assertEquals('boolean', $smd['returns']);
    }
}
