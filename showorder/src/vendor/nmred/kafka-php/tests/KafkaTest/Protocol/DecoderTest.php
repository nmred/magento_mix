<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +---------------------------------------------------------------------------
// | SWAN [ $_SWANBR_SLOGAN_$ ]
// +---------------------------------------------------------------------------
// | Copyright $_SWANBR_COPYRIGHT_$
// +---------------------------------------------------------------------------
// | Version  $_SWANBR_VERSION_$
// +---------------------------------------------------------------------------
// | Licensed ( $_SWANBR_LICENSED_URL_$ )
// +---------------------------------------------------------------------------
// | $_SWANBR_WEB_DOMAIN_$
// +---------------------------------------------------------------------------

namespace KafkaTest\Protocol;

use \Kafka\Protocol\Decoder;

/**
+------------------------------------------------------------------------------
* Kafka protocol since Kafka v0.8
+------------------------------------------------------------------------------
*
* @package
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$
+------------------------------------------------------------------------------
*/

class DecoderTest extends \PHPUnit_Framework_TestCase
{
    // {{{ consts
    // }}}
    // {{{ members

    /**
     * stream
     *
     * @var mixed
     * @access protected
     */
    protected $stream = null;

    // }}}
    // {{{ functions
    // {{{ public function setUp()

    /**
     * setUp
     *
     * @access public
     * @return void
     */
    public function setUp()
    {
        $this->stream = \Kafka\Socket::createFromStream(fopen('php://temp', 'w+b'));
    }

    // }}}
    // {{{ public function setData()

    /**
     * getData
     *
     * @access public
     * @return void
     */
    public function setData($data)
    {
        $len = $this->stream->write($data, true);
        $this->stream->rewind();
        return $len;
    }

    // }}}
    // {{{ public function testFetchRequest()

    /**
     * testFetchRequest
     *
     * @access public
     * @return void
     */
    public function testFetchRequest()
    {
        $this->setData(Decoder::Khex2bin('0000007200000000000000010004746573740000000100000000000000000000000000630000004e000000000000006100000020d4091dc7000000000000000000123332333231603160606060606060606060600000000000000062000000166338ddac000000000000000000086d65737361676532'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        $actual  = $decoder->fetchResponse();
        $this->assertInstanceOf('\Kafka\Protocol\Fetch\Topic', $actual);

        $this->assertEquals(1, count($actual));
    }

    // }}}
    //{{{ public function testProduceResponse()

    /**
     * testProduceResponse
     *
     * @access public
     * @return void
     */
    public function testProduceResponse()
    {
        $this->setData(Decoder::Khex2bin('00000047000000000000000200057465737436000000020000000200000000000000000034000000050000000000000000002800047465737400000001000000000000000000000000005f'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        $actual  = $decoder->produceResponse();

        $expect = array(
            'test6' => array(
                2 => array(
                    'errCode' => 0,
                    'offset'  => 52,
                ),
                5 => array(
                    'errCode' => 0,
                    'offset'  => 40,
                ),
            ),
            'test' => array(
                0 => array(
                    'errCode' => 0,
                    'offset'  => 95,
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    // }}}
    //{{{ public function testProduceResponseNotData()

    /**
     * testProduceResponseNotData
     *
     * @access public
     * @return void
     */
    public function testProduceResponseNotData()
    {
        $this->setData(Decoder::Khex2bin('00000000'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        try {
            $actual  = $decoder->produceResponse();
        } catch (\Kafka\Exception\Protocol $e) {
            $this->assertSame('produce response invalid.', $e->getMessage());
        }
    }

    // }}}
    //{{{ public function testMetadataResponse()

    /**
     * testMetadataResponse
     *
     * @access public
     * @return void
     */
    public function testMetadataResponse()
    {
        $this->setData(Decoder::Khex2bin('0000007100000000000000030000000000086861646f6f703131000023840000000100086861646f6f703132000023840000000200086861646f6f70313300002384000000010000000574657374310000000100000000000000000002000000030000000000000001000000020000000100000002'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        $actual  = $decoder->metadataResponse();

        $expect = array(
            'brokers' => array(
                array(
                    'host' => 'hadoop11',
                    'port' => 9092,
                ),
                array(
                    'host' => 'hadoop12',
                    'port' => 9092,
                ),
                array(
                    'host' => 'hadoop13',
                    'port' => 9092,
                ),
            ),
            'topics' => array(
                'test1' => array(
                    'errCode' => 0,
                    'partitions'  => array(
                        0 => array(
                            'errCode'  => 0,
                            'leader'   => 2,
                            'replicas' => array(0, 1, 2),
                            'isr'      => array(2),
                        ),
                    ),
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    // }}}
    //{{{ public function testMetadataResponseNotData()

    /**
     * testMetadataResponseNotData
     *
     * @access public
     * @return void
     */
    public function testMetadataResponseNotData()
    {
        $this->setData(Decoder::Khex2bin('00000000'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        try {
            $actual  = $decoder->metadataResponse();
        } catch (\Kafka\Exception\Protocol $e) {
            $this->assertSame('metaData response invalid.', $e->getMessage());
        }
    }

    // }}}
    //{{{ public function testOffsetResponse()

    /**
     * testOffsetResponse
     *
     * @access public
     * @return void
     */
    public function testOffsetResponse()
    {
        $this->setData(Decoder::Khex2bin('00000024000000000000000100047465737400000001000000000000000000010000000000000063'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        $actual  = $decoder->offsetResponse();

        $expect = array(
            'test' => array(
                0 => array(
                    'errCode' => 0,
                    'offset' => array(99),
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    // }}}
    //{{{ public function testOffsetResponseNotData()

    /**
     * testOffsetResponseNotData
     *
     * @access public
     * @return void
     */
    public function testOffsetResponseNotData()
    {
        $this->setData(Decoder::Khex2bin('00000000'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        try {
            $actual  = $decoder->offsetResponse();
        } catch (\Kafka\Exception\Protocol $e) {
            $this->assertSame('offset response invalid.', $e->getMessage());
        }
    }

    // }}}
    //{{{ public function testCommitOffsetResponse()

    /**
     * testCommitOffsetResponse
     *
     * @access public
     * @return void
     */
    public function testCommitOffsetResponse()
    {
        $this->setData(Decoder::Khex2bin('0000001900000000000000010005746573743600000001000000020000'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        $actual  = $decoder->commitOffsetResponse();

        $expect = array(
            'test6' => array(
                2 => array(
                    'errCode' => 0,
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    // }}}
    //{{{ public function testCommitOffsetResponseNotData()

    /**
     * testCommitOffsetResponseNotData
     *
     * @access public
     * @return void
     */
    public function testCommitOffsetResponseNotData()
    {
        $this->setData(Decoder::Khex2bin('00000000'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        try {
            $actual  = $decoder->commitOffsetResponse();
        } catch (\Kafka\Exception\Protocol $e) {
            $this->assertSame('commit offset response invalid.', $e->getMessage());
        }
    }

    // }}}
    //{{{ public function testFetchOffsetResponse()

    /**
     * testFetchOffsetResponse
     *
     * @access public
     * @return void
     */
    public function testFetchOffsetResponse()
    {
        $this->setData(Decoder::Khex2bin('000000230000000000000001000574657374360000000100000002000000000000000200000000'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        $actual  = $decoder->fetchOffsetResponse();

        $expect = array(
            'test6' => array(
                2 => array(
                    'offset'   => 2,
                    'metadata' => '',
                    'errCode'  => 0,
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    // }}}
    //{{{ public function testFetchOffsetResponseNotData()

    /**
     * testFetchOffsetResponseNotData
     *
     * @access public
     * @return void
     */
    public function testFetchOffsetResponseNotData()
    {
        $this->setData(Decoder::Khex2bin('00000000'));
        $decoder = new \Kafka\Protocol\Decoder($this->stream);
        try {
            $actual  = $decoder->fetchOffsetResponse();
        } catch (\Kafka\Exception\Protocol $e) {
            $this->assertSame('fetch offset response invalid.', $e->getMessage());
        }
    }

    // }}}
    // {{{ public function testGetError()

    /**
     * testGetError
     *
     * @access public
     * @return void
     */
    public function testGetError()
    {
        $this->assertEquals('Unknown error', Decoder::getError(19));
    }

    // }}}
    // }}}
}
