<?php
/**
 * This file is part of the FreeDSx SNMP package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\FreeDSx\Snmp\Request;

use FreeDSx\Asn1\Asn1;
use FreeDSx\Asn1\Type\IncompleteType;
use FreeDSx\Snmp\Message\Pdu;
use FreeDSx\Snmp\Oid;
use FreeDSx\Snmp\OidList;
use FreeDSx\Snmp\OidValues;
use FreeDSx\Snmp\Protocol\SnmpEncoder;
use FreeDSx\Snmp\Request\RequestInterface;
use FreeDSx\Snmp\Request\TrapV2Request;
use FreeDSx\Snmp\Value\OidValue;
use FreeDSx\Snmp\Value\TimeTicksValue;
use PhpSpec\ObjectBehavior;

class TrapV2RequestSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(OidValues::timeticks(1), OidValues::oid('1.2.3'), new OidList(Oid::fromInteger('1.2',1)));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TrapV2Request::class);
    }

    function it_should_implement_the_request_interface()
    {
        $this->shouldImplement(RequestInterface::class);
    }

    function it_should_be_an_instance_of_a_PDU()
    {
        $this->shouldBeAnInstanceOf(Pdu::class);
    }

    function it_should_get_the_oid_list()
    {
        $this->getOids()->shouldBeLike(new OidList(Oid::fromInteger('1.2', 1)));
    }

    function it_should_set_the_oid_list()
    {
        $this->setOids(new OidList());
        $this->getOids()->shouldBeLike(new OidList());
    }

    function it_should_get_the_pdu_tag()
    {
        $this->getPduTag()->shouldBeEqualTo(7);
    }

    function it_should_have_an_ASN1_representation()
    {
        $this->toAsn1()->shouldBeLike(
            Asn1::context(7, Asn1::sequence(
                Asn1::integer(0),
                Asn1::integer(0),
                Asn1::integer(0),
                Asn1::sequenceOf(
                    Asn1::sequence(
                        Asn1::oid('1.3.6.1.2.1.1.3.0'),
                        OidValues::timeticks(1)->toAsn1()
                    ),
                    Asn1::sequence(
                        Asn1::oid('1.3.6.1.6.3.1.1.4.1.0'),
                        OidValues::oid('1.2.3')->toAsn1()
                    ),
                    Asn1::sequence(
                        Asn1::oid('1.2'),
                        OidValues::integer(1)->toAsn1()
                    )
                )
            ))
        );
    }

    function it_should_be_constructed_from_an_ASN1_representation()
    {
        $pdu = Asn1::sequence(
            Asn1::integer(0),
            Asn1::integer(0),
            Asn1::integer(0),
            Asn1::sequenceOf(
                Asn1::sequence(
                    Asn1::oid('1.3.6.1.2.1.1.3.0'),
                    OidValues::timeticks(1)->toAsn1()
                ),
                Asn1::sequence(
                    Asn1::oid('1.3.6.1.6.3.1.1.4.1.0'),
                    OidValues::oid('1.2.3')->toAsn1()
                ),
                Asn1::sequence(
                    Asn1::oid('1.2'),
                    OidValues::integer(1)->toAsn1()
                )
            )
        );

        $encoder = new SnmpEncoder();
        $pduEncoded = '';
        foreach ($pdu as $element) {
            $pduEncoded .= $encoder->encode($element);
        }

        $pdu = new IncompleteType($pduEncoded);
        $pdu = Asn1::context(7, $pdu)->setIsConstructed(true);

        $this::fromAsn1($pdu)->shouldBeLike(new TrapV2Request(new TimeTicksValue(1), new OidValue('1.2.3'), new OidList(Oid::fromInteger('1.2', 1))));
    }
}
