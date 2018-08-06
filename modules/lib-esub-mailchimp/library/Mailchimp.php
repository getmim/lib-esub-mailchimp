<?php
/**
 * Mailchimp
 * @package lib-esub-mailchimp
 * @version 0.0.1
 */

namespace LibEsubMailchimp\Library;

use \DrewM\MailChimp\MailChimp as DMailChimp;

class Mailchimp implements \LibEsub\Iface\Handler
{

    private $connector;
    private $list;
    private $error;

    public function __construct(){
        $config = \Mim::$app->config->libEsubMailchimp;
        $key    = $config->apikey;
        $list   = $config->list;

        $this->connector = new DMailChimp($key);
        $this->list = $list;
    }

    private function buildUser($user): object{
        $fullname = trim($user['merge_fields']['FNAME'] . ' ' . $user['merge_fields']['LNAME']);

        return (object)[
            'id'    => $user['id'],
            'email' => $user['email_address'],
            'name'  => (object)[
                'full'  => $fullname,
                'first' => $user['merge_fields']['FNAME'],
                'last'  => $user['merge_fields']['LNAME']
            ],
            'created' => date('Y-m-d H:i:s', strtotime($user['timestamp_opt']))
        ];
    }

    private function validateResponse($res): bool{
        if(!$res){
            $this->error = 'Unable to connect to mailchimp api';
            return false;
        }

        if(
            isset($res['type']) &&
            isset($res['status']) &&
            isset($res['detail'])
        ){
            $this->error = 'Mailchimp: ' . $res['detail'];
            return false;
        }

        return true;
    }

    function getLists(): array {
        $lists = $this->connector->get('lists');
        $result = [];
        if(!$this->validateResponse($lists))
            return $result;
        foreach($lists['lists'] as $list){
            $result[] = (object)[
                'id' => $list['id'],
                'name' => $list['name'],
                'members' => (int)$list['stats']['member_count']
            ];
        }

        return $result;
    }

    function setList(string $list): void {
        $this->list = $list;
    }

    function get(int $rpp=12, int $page=1): object {
        $result = (object)[
            'total' => 0,
            'emails' => []
        ];

        $uri = '/lists/' . $this->list . '/members';

        $offset = $rpp * ($page-1);
        $res = $this->connector->get($uri, [
            'count' => $rpp,
            'offset' => $offset,
            'status' => 'subscribed'
        ]);

        if(!$this->validateResponse($res))
            return $result;
        $result->total = $res['total_items'];

        foreach($res['members'] as $mem)
            $result->emails[] = $this->buildUser($mem);

        return $result;
    }

    function addMember(string $email, string $fname=null, string $lname=null): ?object {
        $uri = '/lists/' . $this->list . '/members';
        $res = $this->connector->post($uri, [
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $fname,
                'LNAME' => $lname
            ]
        ]);

        if(!$this->validateResponse($res))
            return null;

        return $this->buildUser($res);
    }

    function getMember(string $email): ?object {
        $hash = $this->connector->subscriberHash($email);

        $uri = '/lists/' . $this->list . '/members/' . $hash;
        $res = $this->connector->get($uri);

        if(!$this->validateResponse($res))
            return null;
        return $this->buildUser($res);
    }

    function lastError(): ?string{
        return $this->error;
    }

    function removeMember(string $email): bool {
        $hash = $this->connector->subscriberHash($email);

        $uri = '/lists/' . $this->list . '/members/' . $hash;
        $res = $this->connector->delete($uri);

        if(!$this->validateResponse($res))
            return false;
        if(is_bool($res))
            return $res;
        return false;
    }
}