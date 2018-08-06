<?php
/**
 * Mailchimp
 * @package lib-esub-mailchimp
 * @version 0.0.1
 */

namespace LibEsubMailchimp\Library;

use \DrewM\MailChimp\MailChimp as DMailChimp;

class Mailchimp
{

    private static $connector;

    private static $list;

    private static function prepareConnector(): void{
        if(self::$connector)
            return;

        $config = \Mim::$app->config->libEsubMailchimp;
        $key = $config->apikey;
        $list = $config->list;

        self::$connector = new DMailChimp($key);
        self::$list = $list;
    }

    static function getLists(): array {
        self::prepareConnector();

        $lists = self::$connector->get('lists');
        $result = [];
        foreach($lists['lists'] as $list){
            $result[] = (object)[
                'id' => $list['id'],
                'name' => $list['name'],
                'members' => (int)$list['stats']['member_count']
            ];
        }

        return $result;
    }

    static function setList(string $list): void {
        self::$list = $list;
        self::prepareConnector();
    }

    static function get(int $rpp=12, int $page=1): array {
        self::prepareConnector();
        $uri = '/lists/' . self::$list . '/members';

        $offset = $rpp * ($page-1);
        $res = self::$connector->get($uri, [
            'count' => $rpp,
            'offset' => $offset,
            'status' => 'subscribed'
        ]);

        if(!$res || !isset($res['members']))
            return [];

        $result = [];
        foreach($res['members'] as $mem){
            $result[] = (object)[
                'id'    => $mem['id'],
                'email' => $mem['email_address'],
                'fname' => $mem['merge_fields']['FNAME'],
                'lname' => $mem['merge_fields']['LNAME'],
                'created' => date('Y-m-d H:i:s', strtotime($mem['timestamp_opt']))
            ];
        }

        return $result;
    }

    static function addMember(string $email, string $fname=null, string $lname=null): ?object {
        self::prepareConnector();

        $uri = '/lists/' . self::$list . '/members';
        $res = self::$connector->post($uri, [
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $fname,
                'LNAME' => $lname
            ]
        ]);

        if(!$res || $res['status'] === 400)
            return null;

        return (object)[
            'id'    => $res['id'],
            'email' => $res['email_address'],
            'fname' => $res['merge_fields']['FNAME'],
            'lname' => $res['merge_fields']['LNAME'],
            'created' => date('Y-m-d H:i:s', strtotime($res['timestamp_opt']))
        ];
    }

    static function getMember(string $email): ?object {
        self::prepareConnector();
        $hash = self::$connector->subscriberHash($email);

        $uri = '/lists/' . self::$list . '/members/' . $hash;
        $res = self::$connector->get($uri);

        if(!$res || $res['status'] == 404)
            return null;

        return (object)[
            'id'    => $res['id'],
            'email' => $res['email_address'],
            'fname' => $res['merge_fields']['FNAME'],
            'lname' => $res['merge_fields']['LNAME'],
            'created' => date('Y-m-d H:i:s', strtotime($res['timestamp_opt']))
        ];
    }

    static function removeMember(string $email): bool {
        self::prepareConnector();

        $hash = self::$connector->subscriberHash($email);

        $uri = '/lists/' . self::$list . '/members/' . $hash;
        $res = self::$connector->delete($uri);

        if(is_bool($res))
            return $res;
        return false;
    }
}