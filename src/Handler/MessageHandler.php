<?php

namespace Karhal\Web3ConnectBundle\Handler;

use Karhal\Web3ConnectBundle\Model\Message;

class MessageHandler
{
    const REGEX = '/^(?<domain>([^?#]*)) wants you to sign in with your Ethereum account:\R(?<address>0x[a-zA-Z0-9]{40})\R\R((?<statement>([^?#]*))\R)?\RURI: (?<uri>((([^:\/?#]+):)?(\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?))\RVersion: (?<version>1)\RChain ID: (?<chainId>[0-9]+)\RNonce: (?<nonce>[a-zA-Z0-9]{8,})\RIssued At: (?<issuedAt>([0-9]+)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(([Zz])|([\+|\-]([01][0-9]|2[0-3]):[0-5][0-9])))(\RExpiration Time: (?<expirationTime>([0-9]+)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(([Zz])|([\+|\-]([01][0-9]|2[0-3]):[0-5][0-9]))))?(\RNot Before: (?<notBefore>([0-9]+)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(([Zz])|([\+|\-]([01][0-9]|2[0-3]):[0-5][0-9]))))?(\RRequest ID: (?<requestId>[-._~!$&\'()*+,;=:@%a-zA-Z0-9]*))?(\RResources:(?<resources>(\R- ((([^:\/?#]+):)?(\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?)?)+))?/m';

    public static function parseMessage(string $message)
    {
        preg_match_all(self::REGEX, $message, $matches, PREG_SET_ORDER, 0);

        $message = new Message();
        $message->setStatement($matches[0]['statement']);
        $message->setAddress($matches[0]['address']);
        $message->setDomain($matches[0]['domain']);
        $message->setUri($matches[0]['uri']);
        $message->setVersion($matches[0]['version']);
        $message->setChainId($matches[0]['chainId']);
        $message->setNonce($matches[0]['nonce']);

        if(array_key_exists('issuedAt', $matches[0])) {
            $message->setIssuedAt(new \DateTimeImmutable($matches[0]['issuedAt']));
        }
        if(array_key_exists('notBefore', $matches[0])) {
            $message->setNotBefore(new \DateTimeImmutable($matches[0]['notBefore']));
        }
        if(array_key_exists('expirationTime', $matches[0])) {
            $message->setExpirationTime(new \DateTimeImmutable($matches[0]['expirationTime']));
        }
        if(array_key_exists('requestId', $matches[0])) {
            $message->setRequestId($matches[0]['requestId']);
        }
        if(array_key_exists('resources', $matches[0])) {
            $resources = explode("\n- ", $matches[0]['resources']);
            array_shift($resources);
            $message->setResources($resources);
        }

        return $message;
    }
}
