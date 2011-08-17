<?php
require_once 'PHPUnit/Framework.php';
require_once 'Net/Monitor/Alert/SMS.php';

class Net_Monitor_Alert_SMSTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->alert = new Net_Monitor_Alert_SMSMock();
    }

    public function testAlert() {
        $this->alert->mock = $this->getMock('Net_SMS_Mock_Driver');
        $this->alert->mock->expects($this->once())
                            ->method('send')
                            ->with(array('from' => 'Net_Monitor', 
                                         'to' => array('example.com'),
                                         'id' => 1,
                                         'text' => 'sad.server.com(HTTP)>Sad server message ' . "\r\n"));

        $options = array('state_file' => 'Net_Monitor_TestSuite',
                         'SMS_default' => array( 'SMS_provider' => 'clickatell_http',
		                                        'username' => 'pique',
		                                        'password' => 'robert',
		                                        'api_id' => 'x.y.z' ));

        $result_array = array('sad.server.com' => 
                                array('HTTP' => 
                                    array("Sad server message", 0)));


        $this->alert->alert(array('example.com'), $result_array, $options);
    }

    public function testShouldNotBufferShortSMS() {
        $out = "Short text";
        $max = 160;

        $SMS_message = '';
        $SMS_array = array();
        $this->alert->bufferSMS($out, $max, $SMS_message, $SMS_array);

        $this->assertSame("Short text", $SMS_message);
        $this->assertSame(array(), $SMS_array);
    }

    public function testShouldBufferSMSForLongMessages() {
        $out = "Really long text";
        $max = 11;

        $SMS_message = '';
        $SMS_array = array();
        $this->alert->bufferSMS($out, $max, $SMS_message, $SMS_array);

        $this->assertSame("", $SMS_message);
        $this->assertSame(array("Really long"), $SMS_array);
    }

    public function testShouldBufferCurrentSMSForShortExtraMessages() {
        $out = "Really long text";
        $max = 20;

        $SMS_message = 'I am existing!';
        $SMS_array = array();
        $this->alert->bufferSMS($out, $max, $SMS_message, $SMS_array);

        $this->assertSame("Really long text", $SMS_message);
        $this->assertSame(array("I am existing!"), $SMS_array);
    }

    public function testShouldBuildToSendList1() {
        list($toSend, $accPar) = $this->alert->buildToSendList(array());

        $this->assertSame(array(), $toSend);
        $this->assertSame(array(), $accPar);
    }

    public function testShouldBuildToSendList2() {
        list($toSend, $accPar) = $this->alert->buildToSendList(array(
            array('phone_number' => 'bob', 'username' => 'maxwell', 'SMS_provider' => 'example')
        ));

        $this->assertSame(array('example' =>
                                 array('maxwell' => 
                                    array('bob')
                                 )
                            ), $toSend);

        $this->assertSame(array('example' =>
                                 array('maxwell' => 
                                    array('user' => 'maxwell')
                                 )
                            ), $accPar);
    }


}

class Net_Monitor_Alert_SMSMock extends Net_Monitor_Alert_SMS {
    public $mock;

    public function factory() {
        return $this->mock;
    }
    public function bufferSMS($out, $max, &$SMS_message, &$SMS_array) {
        return parent::bufferSMS($out, $max, $SMS_message, $SMS_array);
    }
    public function buildToSendList($items) {
        return parent::buildToSendList($items);
    }
}
class Net_SMS_Mock_Driver {
    public function send() {}
}
